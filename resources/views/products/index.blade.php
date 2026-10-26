@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Product Catalog')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Products</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Inventory stock, pricing, and item master records</p>
  </div>
  @if(auth()->user()->isAdmin())
    <a href="{{ route('products.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      <span>Add Product</span>
    </a>
  @endif
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('products.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search by product name or SKU..." 
      value="{{ $search }}"
    >
    <select name="category_id" class="form-select filter-select">
      <option value="">All Categories</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
          {{ $cat->name }}
        </option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Apply Filters</button>
    @if($search || $categoryId)
      <a href="{{ route('products.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Products Table -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product Name</th>
          <th>Category</th>
          <th class="text-right">Selling Price</th>
          <th class="text-right">Stock</th>
          <th>Stock Status</th>
          <th class="text-right" style="width: 140px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          <tr>
            <td class="num font-semibold text-muted">{{ $product->sku }}</td>
            <td>
              <a href="{{ route('products.show', $product) }}" class="font-semibold" style="color: var(--brand-navy);">
                {{ $product->name }}
              </a>
            </td>
            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
            <td class="num text-right font-semibold">
              Rp {{ number_format($product->selling_price, 0, ',', '.') }}
            </td>
            <td class="num text-right font-bold {{ $product->stock <= 0 ? 'text-danger' : '' }}" style="{{ $product->stock <= 0 ? 'color: var(--action-danger);' : '' }}">
              {{ $product->stock }}
            </td>
            <td>
              @if($product->stock <= 0)
                <span class="badge badge-danger"><span class="badge-dot"></span>Out of Stock</span>
              @elseif($product->stock <= $product->minimum_stock)
                <span class="badge badge-warning"><span class="badge-dot"></span>Low Stock</span>
              @else
                <span class="badge badge-success"><span class="badge-dot"></span>In Stock</span>
              @endif
            </td>
            <td class="text-right">
              <div style="display: flex; justify-content: flex-end; gap: var(--space-2);">
                <a href="{{ route('products.show', $product) }}" class="btn btn-secondary btn-sm">View</a>
                @if(auth()->user()->isAdmin())
                  <a href="{{ route('products.edit', $product) }}" class="btn btn-outline btn-sm">Edit</a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted" style="padding: var(--space-12);">
              No products found matching your filter criteria.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($products->hasPages())
    <div class="card-footer">
      {{ $products->links() }}
    </div>
  @endif
</div>
@endsection
