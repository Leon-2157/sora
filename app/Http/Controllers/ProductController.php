<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function lowStock(Request $request): View
    {
        $query = Product::with('category')->whereRaw('stock <= minimum_stock');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('stock', 'asc')->paginate(15)->withQueryString();

        return view('products.low-stock', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        $validated['stock'] = 0;
        $validated['purchase_price'] = 0.00;

        $product = Product::create($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $recentPurchases = $product->purchaseItems()
            ->with('purchase.supplier')
            ->latest()
            ->limit(5)
            ->get();

        $recentSales = $product->saleItems()
            ->with('sale.user')
            ->latest()
            ->limit(5)
            ->get();

        return view('products.show', compact('product', 'recentPurchases', 'recentSales'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $hasPurchases = $product->purchaseItems()->exists();
        $hasSales = $product->saleItems()->exists();

        if ($hasPurchases || $hasSales) {
            return back()->with('error', 'This product cannot be deleted because it is recorded in past transactions.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
