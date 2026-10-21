<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($request->filled('start_date')) {
            $query->whereDate('transacted_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transacted_at', '<=', $request->end_date);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 10);
        $purchases = $query->latest('transacted_at')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar transaksi pengadaan berhasil diambil.',
            'data' => $purchases,
        ], 200);
    }

    /**
     * Store a newly created purchase transaction.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        $purchase = DB::transaction(function () use ($validated, $request) {
            $date = now()->format('Ymd');
            $countToday = Purchase::whereDate('created_at', now()->toDateString())->count() + 1;
            $number = 'PO-'.$date.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);

            while (Purchase::where('number', $number)->exists()) {
                $countToday++;
                $number = 'PO-'.$date.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // Recalculate totals server-side
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['unit_cost'];
            }

            $purchase = Purchase::create([
                'number' => $number,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'status' => 'completed',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'transacted_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                // Increment physical stock
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            return $purchase;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi pengadaan berhasil dicatat.',
            'data' => $purchase->load(['supplier', 'user', 'items.product']),
        ], 201);
    }

    /**
     * Display the specified purchase details.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'cancelledBy', 'items.product'])->find($id);

        if (! $purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi pengadaan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi pengadaan berhasil diambil.',
            'data' => $purchase,
        ], 200);
    }

    /**
     * Cancel an existing purchase transaction and reverse stock.
     */
    public function cancel(Request $request, string $id)
    {
        $purchase = Purchase::with('items.product')->find($id);

        if (! $purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi pengadaan tidak ditemukan.',
            ], 404);
        }

        if ($purchase->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini tidak dapat dibatalkan.',
            ], 422);
        }

        $result = DB::transaction(function () use ($purchase, $request) {
            // Verify stock availability for reversal
            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (! $product || $product->stock < $item->quantity) {
                    return [
                        'success' => false,
                        'message' => "Stok produk {$product?->name} tidak mencukupi untuk pembatalan transaksi.",
                    ];
                }
            }

            // Decrement reversed stock
            foreach ($purchase->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            $purchase->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
                'cancel_reason' => $request->input('cancel_reason', 'Dibatalkan oleh admin'),
            ]);

            return [
                'success' => true,
                'purchase' => $purchase->fresh()->load(['supplier', 'user', 'cancelledBy', 'items.product']),
            ];
        });

        if (! $result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi pengadaan berhasil dibatalkan.',
            'data' => $result['purchase'],
        ], 200);
    }
}
