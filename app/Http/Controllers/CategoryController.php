<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Menampilkan daftar kategori dengan fitur pencarian & paginasi
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Fitur Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Paginasi server side
        $perPage = (int) $request->input('per_page', 10);
        $categories = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => $categories,
        ], 200);
    }

    // Menyimpan kategori baru (Admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    // Menampilkan detail satu kategori beserta daftar produknya
    public function show(string $id)
    {
        $category = Category::with('products')->find($id);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $category,
        ], 200);
    }

    // Memperbarui kategori (Admin)
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category,
        ], 200);
    }

    // Menghapus kategori dengan proteksi integritas data (Admin)
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        // Proteksi: Jangan hapus jika masih memiliki produk aktif
        if ($category->products()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak dapat dihapus karena masih memuat produk terkait.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus.',
        ], 200);
    }
}
