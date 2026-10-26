<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function pos(): View
    {
        $products = Product::with('category')->orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('sales.pos', compact('products', 'categories'));
    }

    public function index(Request $request): View
    {
        $query = Sale::with(['user', 'items.product']);

        if ($search = $request->input('search')) {
            $query->where('sale_number', 'LIKE', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();

        return view('sales.index', compact('sales', 'search', 'status'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name', 'asc')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request, SaleService $saleService): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,completed'],
            'sold_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = $saleService->createSale($request->all(), $request->user());

            // If coming from POS, redirect with success and open receipt or back to POS
            if ($request->boolean('is_pos')) {
                return redirect()->route('sales.show', $sale)
                    ->with('success', "Sale recorded successfully with number {$sale->sale_number}.");
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', "Sale recorded successfully with number {$sale->sale_number}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function complete(Sale $sale, SaleService $saleService): RedirectResponse
    {
        try {
            $saleService->completeSale($sale);
            return back()->with('success', "Sale {$sale->sale_number} marked as completed and inventory stock updated.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Sale $sale, SaleService $saleService): RedirectResponse
    {
        try {
            $saleService->cancelSale($sale);
            return back()->with('success', "Sale {$sale->sale_number} has been cancelled.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
