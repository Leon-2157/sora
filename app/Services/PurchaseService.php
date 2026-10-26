<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function generatePurchaseNumber(): string
    {
        $today = Carbon::now('Asia/Jakarta')->format('Ymd');
        $prefix = "PUR-{$today}-";

        $latest = Purchase::where('purchase_number', 'LIKE', "{$prefix}%")
            ->orderBy('purchase_number', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->purchase_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$prefix}{$nextSeq}";
    }

    public function createPurchase(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user) {
            $purchaseNumber = $this->generatePurchaseNumber();
            $status = $data['status'] ?? 'pending';

            $total = 0.00;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1) {
                    throw new Exception('Quantity must be at least 1.');
                }
                if ($unitCost < 0) {
                    throw new Exception('Unit cost cannot be negative.');
                }

                $subtotal = round($qty * $unitCost, 2);
                $total += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ];
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'purchase_number' => $purchaseNumber,
                'total' => $total,
                'status' => $status,
                'purchased_at' => $data['purchased_at'] ?? Carbon::now('Asia/Jakarta'),
            ]);

            foreach ($itemsData as $item) {
                $purchase->items()->create($item);

                // If status is completed upon creation, increase stock
                if ($status === 'completed') {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                        // Update purchase price to latest unit cost
                        $product->update(['purchase_price' => $item['unit_cost']]);
                    }
                }
            }

            return $purchase;
        });
    }

    public function completePurchase(Purchase $purchase): Purchase
    {
        if ($purchase->status === 'completed') {
            throw new Exception('This purchase has already been completed.');
        }

        if ($purchase->status === 'cancelled') {
            throw new Exception('A cancelled purchase cannot be completed.');
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                    $product->update(['purchase_price' => $item->unit_cost]);
                }
            }

            $purchase->update(['status' => 'completed']);
            return $purchase;
        });
    }

    public function cancelPurchase(Purchase $purchase): Purchase
    {
        if ($purchase->status === 'completed') {
            throw new Exception('Completed purchases cannot be cancelled.');
        }

        if ($purchase->status === 'cancelled') {
            throw new Exception('This purchase is already cancelled.');
        }

        $purchase->update(['status' => 'cancelled']);
        return $purchase;
    }
}
