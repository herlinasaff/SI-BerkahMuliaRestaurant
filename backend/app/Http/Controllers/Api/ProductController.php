<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'supplier'])->orderBy('code');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->outOfStock();
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'total' => $products->count(),
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'price_per_plate' => 'nullable|numeric|min:0',
            'is_plate_based' => 'boolean',
            'is_prasmanan' => 'boolean',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan!',
            'data' => $product->load('category', 'supplier'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'code' => 'required|string|unique:products,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'price_per_plate' => 'nullable|numeric|min:0',
            'is_plate_based' => 'boolean',
            'is_prasmanan' => 'boolean',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui!',
            'data' => $product->load('category', 'supplier'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.'
        ]);
    }

    /**
     * Penyesuaian Stok Opname Manual (Barang Rusak / Selisih Fisik)
     */
    public function adjustStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'new_stock' => 'required|integer|min:0',
            'notes' => 'required|string',
        ]);

        $product = Product::findOrFail($id);
        $stockBefore = $product->stock;
        $stockAfter = (int) $request->new_stock;
        $diff = $stockAfter - $stockBefore;

        $product->update(['stock' => $stockAfter]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'movement_type' => 'adjustment',
            'qty' => abs($diff),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => 'opname',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil disesuaikan.',
            'data' => $product,
        ]);
    }

    public function getCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Category::all(),
        ]);
    }
}
