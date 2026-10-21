<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filters, search, and pagination.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $perPage = (int) $request->input('per_page', 10);
        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar produk berhasil diambil.',
            'data' => $products,
        ], 200);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['min_stock'] = $validated['min_stock'] ?? 0;

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk berhasil diambil.',
            'data' => $product,
        ], 200);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product->load('category'),
        ], 200);
    }

    /**
     * Soft delete the specified product.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ], 200);
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(string $id)
    {
        $product = Product::onlyTrashed()->find($id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk terhapus tidak ditemukan.',
            ], 404);
        }

        $product->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dipulihkan.',
            'data' => $product->load('category'),
        ], 200);
    }
}
