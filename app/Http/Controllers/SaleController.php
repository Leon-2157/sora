<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales transactions with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'items.product']);

        if ($request->filled('start_date')) {
            $query->whereDate('transacted_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transacted_at', '<=', $request->end_date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 10);
        $sales = $query->latest('transacted_at')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar transaksi penjualan berhasil diambil.',
            'data' => $sales,
        ], 200);
    }

    /**
     * Store a newly created sale transaction.
     */
    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated, $request) {
            // Lock and verify stock for all products first
            $products = [];
            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (! $product || $product->stock < $item['quantity']) {
                    return [
                        'success' => false,
                        'message' => "Stok produk {$product?->name} tidak mencukupi.",
                    ];
                }

                $unitPrice = (float) $product->selling_price;
                $subtotal = $unitPrice * $item['quantity'];
                $total += $subtotal;

                $products[] = [
                    'model' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $date = now()->format('Ymd');
            $countToday = Sale::whereDate('created_at', now()->toDateString())->count() + 1;
            $number = 'INV-'.$date.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);

            while (Sale::where('number', $number)->exists()) {
                $countToday++;
                $number = 'INV-'.$date.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            $sale = Sale::create([
                'number' => $number,
                'user_id' => $request->user()->id,
                'status' => 'completed',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'transacted_at' => now(),
            ]);

            foreach ($products as $p) {
                $sale->items()->create([
                    'product_id' => $p['model']->id,
                    'quantity' => $p['quantity'],
                    'unit_price' => $p['unit_price'],
                    'subtotal' => $p['subtotal'],
                ]);

                // Decrement stock physically
                $p['model']->decrement('stock', $p['quantity']);
            }

            return [
                'success' => true,
                'sale' => $sale,
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
            'message' => 'Transaksi penjualan berhasil dicatat.',
            'data' => $result['sale']->load(['user', 'items.product']),
        ], 201);
    }

    /**
     * Display the specified sale details.
     */
    public function show(string $id)
    {
        $sale = Sale::with(['user', 'cancelledBy', 'items.product'])->find($id);

        if (! $sale) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi penjualan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi penjualan berhasil diambil.',
            'data' => $sale,
        ], 200);
    }

    /**
     * Cancel a sale transaction and restore physical stock.
     */
    public function cancel(Request $request, string $id)
    {
        $sale = Sale::with('items.product')->find($id);

        if (! $sale) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi penjualan tidak ditemukan.',
            ], 404);
        }

        if ($sale->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini tidak dapat dibatalkan.',
            ], 422);
        }

        DB::transaction(function () use ($sale, $request) {
            // Restore product stock
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            $sale->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
                'cancel_reason' => $request->input('cancel_reason', 'Dibatalkan oleh admin'),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi penjualan berhasil dibatalkan.',
            'data' => $sale->fresh()->load(['user', 'cancelledBy', 'items.product']),
        ], 200);
    }
}
