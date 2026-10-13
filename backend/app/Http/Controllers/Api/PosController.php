<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Mengambil daftar menu siap jual untuk kasir (dikelompokkan per kategori)
     */
    public function getMenu(): JsonResponse
    {
        $categories = Category::where('is_sellable', true)->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('is_sellable', true))
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Proses Checkout Kasir (Umum, VIP, Biro Yarwe)
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'customer_type' => 'required|in:umum,vip,yarwe',
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'cash_received' => 'nullable|numeric|min:0',
            'total_plates' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $currentShift = Shift::getCurrentShift();

        try {
            $sale = DB::transaction(function () use ($request, $user, $currentShift) {
                $customerType = $request->customer_type;
                $totalPlates = (int) ($request->total_plates ?? 0);

                $originalTotal = 0;
                $finalTotal = 0;
                $totalDiscount = 0;
                $yarweTotal = 0;

                // 1. Validasi & Hitung Item
                $itemsData = [];
                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    // Validasi stok
                    if ($product->is_stockable && $product->stock < $item['qty']) {
                        throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi (Tersisa: {$product->stock}).");
                    }

                    $qty = (int) $item['qty'];
                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $qty;
                    $itemDiscount = 0;

                    // Logika Pelanggan VIP: Gratis 100%
                    if ($customerType === 'vip') {
                        $itemDiscount = $subtotal;
                        $finalItemPrice = 0;
                    } else {
                        $finalItemPrice = $subtotal;
                    }

                // Hitung total piring dari keranjang jika tidak dikirim spesifik
                $calculatedPlates = 0;
                foreach ($itemsData as $row) {
                    if ($row['is_plate_based']) {
                        $calculatedPlates += $row['qty'];
                    }
                }

                $totalPlates = ($totalPlates > 0) ? $totalPlates : $calculatedPlates;
                $yarweTotal = ($customerType === 'yarwe') ? ($totalPlates * 2000) : 0;


                // Total akhir yang harus dibayar
                $grandTotal = ($customerType === 'vip') ? 0 : ($finalTotal + $yarweTotal);
                $cashReceived = (float) ($request->cash_received ?? $grandTotal);
                $cashChange = max(0, $cashReceived - $grandTotal);

                // 2. Buat Record Penjualan (Sale Header)
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $user->id,
                    'shift_id' => $currentShift ? $currentShift->id : 1,
                    'customer_type' => $customerType,
                    'sale_date' => now(),
                    'original_total' => $originalTotal,
                    'total_discount' => $totalDiscount,
                    'yarwe_total' => $yarweTotal,
                    'total_plates' => $totalPlates,
                    'final_total' => $grandTotal,
                    'payment_method' => $request->payment_method,
                    'cash_received' => $cashReceived,
                    'cash_change' => $cashChange,
                    'status' => 'completed',
                    'notes' => $request->notes,
                ]);

                // 3. Simpan Rincian Item & Potong Stok
                foreach ($itemsData as $row) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $row['product']->id,
                        'product_name' => $row['product']->name,
                        'qty' => $row['qty'],
                        'unit_price' => $row['unit_price'],
                        'original_price' => $row['original_price'],
                        'discount_percent' => ($customerType === 'vip') ? 100 : 0,
                        'discount_amount' => $row['discount_amount'],
                        'subtotal' => $row['subtotal'],
                        'final_price' => $row['final_price'],
                        'is_plate_based' => $row['is_plate_based'],
                        'plate_count' => $row['plate_count'],
                        'yarwe_fee_applied' => $row['yarwe_fee_applied'],
                    ]);

                    // Potong stok jika produk bertipe stockable
                    if ($row['product']->is_stockable) {
                        $stockBefore = $row['product']->stock;
                        $stockAfter = $stockBefore - $row['qty'];

                        $row['product']->update(['stock' => $stockAfter]);

                        InventoryMovement::create([
                            'product_id' => $row['product']->id,
                            'user_id' => $user->id,
                            'movement_type' => 'out',
                            'qty' => $row['qty'],
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'notes' => "Penjualan #{$sale->invoice_number} ({$customerType})",
                        ]);
                    }
                }

                return $sale;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi kasir berhasil diproses!',
                'data' => $sale->load(['details', 'user', 'shift']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ambil data invoice untuk struk kasir
     */
    public function getInvoice(int $id): JsonResponse
    {
        $sale = Sale::with(['details.product', 'user', 'shift'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sale
        ]);
    }

    /**
     * Riwayat Transaksi Penjualan Kasir
     */
    public function getHistory(Request $request): JsonResponse
    {
        $query = Sale::with(['user', 'shift', 'details'])->orderBy('sale_date', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('sale_date', $request->date);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        $sales = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }
}
