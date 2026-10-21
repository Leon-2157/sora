<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with search and pagination.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $suppliers = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar supplier berhasil diambil.',
            'data' => $suppliers,
        ], 200);
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier berhasil ditambahkan.',
            'data' => $supplier,
        ], 201);
    }

    /**
     * Display the specified supplier.
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail supplier berhasil diambil.',
            'data' => $supplier,
        ], 200);
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier berhasil diperbarui.',
            'data' => $supplier,
        ], 200);
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier tidak ditemukan.',
            ], 404);
        }

        // Prevent deletion if supplier has purchase transactions
        if ($supplier->purchases()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier tidak dapat dihapus karena memiliki riwayat transaksi.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier berhasil dihapus.',
        ], 200);
    }
}
