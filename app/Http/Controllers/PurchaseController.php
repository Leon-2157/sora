<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'user']);

        if ($search = $request->input('search')) {
            $query->where('purchase_number', 'LIKE', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'search', 'status', 'supplierId'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request, PurchaseService $purchaseService): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'status' => ['required', 'in:pending,completed'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $purchase = $purchaseService->createPurchase($request->all(), $request->user());

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Purchase recorded successfully with number {$purchase->purchase_number}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function complete(Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->completePurchase($purchase);
            return back()->with('success', "Purchase {$purchase->purchase_number} marked as completed and inventory stock updated.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Purchase $purchase, PurchaseService $purchaseService): RedirectResponse
    {
        try {
            $purchaseService->cancelPurchase($purchase);
            return back()->with('success', "Purchase {$purchase->purchase_number} has been cancelled.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
