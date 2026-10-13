<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(): JsonResponse
    {
        $purchases = Purchase::with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $purchases]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method' => 'required|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchase = DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }

            $shippingCost = (float) ($request->shipping_cost ?? 0);
            $totalAmount = $subtotal + $shippingCost;

            $invoiceNumber = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $purchase = Purchase::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $request->supplier_id,
                'user_id' => $request->user()->id,
                'purchase_date' => now(),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'received',
                'notes' => $request->notes,
            ]);

            // Tambahkan stok produk & catat mutasi masuk
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemSubtotal = $item['qty'] * $item['unit_price'];

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $item['qty'];

                $product->update(['stock' => $stockAfter]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'movement_type' => 'in',
                    'qty' => $item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'notes' => "Pengadaan Supplier #{$purchase->invoice_number}",
                ]);
            }

            return $purchase;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengadaan belanja berhasil dicatat dan stok otomatis bertambah!',
            'data' => $purchase->load('supplier', 'details'),
        ], 201);
    }

    public function getSuppliers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Supplier::where('status', 'active')->get(),
        ]);
    }
}
