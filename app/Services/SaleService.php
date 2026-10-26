<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function generateSaleNumber(): string
    {
        $today = Carbon::now('Asia/Jakarta')->format('Ymd');
        $prefix = "SAL-{$today}-";

        $latest = Sale::where('sale_number', 'LIKE', "{$prefix}%")
            ->orderBy('sale_number', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->sale_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$prefix}{$nextSeq}";
    }

    public function createSale(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $saleNumber = $this->generateSaleNumber();
            $status = $data['status'] ?? 'completed';

            $total = 0.00;
            $itemsData = [];

            // Step 1: Pre-fetch and lock products for stock validation if completed
            foreach ($data['items'] as $item) {
                $qty = (int) $item['quantity'];
                if ($qty < 1) {
                    throw new Exception('Quantity must be at least 1.');
                }

                $product = Product::lockForUpdate()->find($item['product_id']);
                if (! $product) {
                    throw new Exception('Product not found.');
                }

                if ($status === 'completed' && $product->stock < $qty) {
                    throw new Exception(
                        "Stock for \"{$product->name}\" is insufficient. Available: {$product->stock} units, requested: {$qty}."
                    );
                }

                $unitPrice = isset($item['unit_price']) && is_numeric($item['unit_price'])
                    ? (float) $item['unit_price']
                    : (float) $product->selling_price;

                $subtotal = round($qty * $unitPrice, 2);
                $total += $subtotal;

                $itemsData[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Step 2: Create Sale Header
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $saleNumber,
                'total' => $total,
                'status' => $status,
                'sold_at' => $data['sold_at'] ?? Carbon::now('Asia/Jakarta'),
            ]);

            // Step 3: Create Sale Items & Decrement Stock if completed
            foreach ($itemsData as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                if ($status === 'completed') {
                    $item['product']->decrement('stock', $item['quantity']);
                }
            }

            return $sale;
        });
    }

    public function completeSale(Sale $sale): Sale
    {
        if ($sale->status === 'completed') {
            throw new Exception('This sale has already been completed.');
        }

        if ($sale->status === 'cancelled') {
            throw new Exception('A cancelled sale cannot be completed.');
        }

        return DB::transaction(function () use ($sale) {
            $sale->load('items.product');

            // Validate sufficient stock for all items
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (! $product) {
                    throw new Exception('A product in this sale no longer exists.');
                }

                if ($product->stock < $item->quantity) {
                    throw new Exception(
                        "Stock for \"{$product->name}\" is insufficient. Available: {$product->stock} units, requested: {$item->quantity}."
                    );
                }
            }

            // Decrement stock
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                $product->decrement('stock', $item->quantity);
            }

            $sale->update(['status' => 'completed']);
            return $sale;
        });
    }

    public function cancelSale(Sale $sale): Sale
    {
        if ($sale->status === 'completed') {
            throw new Exception('Completed sales cannot be cancelled.');
        }

        if ($sale->status === 'cancelled') {
            throw new Exception('This sale is already cancelled.');
        }

        $sale->update(['status' => 'cancelled']);
        return $sale;
    }
}
