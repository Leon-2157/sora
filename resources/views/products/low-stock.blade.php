@extends('layouts.app')

@section('title', 'Low Stock Products')
@section('page-title', 'Low Stock Watchlist')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Low Stock Watchlist</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Items whose current stock is at or below the minimum safety threshold</p>
  </div>
  <a href="{{ route('purchases.create') }}" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
      <line x1="12" y1="5" x2="12" y2="19"></line>
      <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
    <span>Reorder via Purchase</span>
  </a>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('products.low-stock') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search low-stock products by name or SKU..." 
      value="{{ $search }}"
    >
    <button type="submit" class="btn btn-secondary">Search</button>
    @if($search)
      <a href="{{ route('products.low-stock') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Low Stock Table -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product Name</th>
          <th>Category</th>
          <th class="text-right">Current Stock</th>
          <th class="text-right">Safety Minimum</th>
          <th class="text-right">Stock Gap</th>
          <th>Status</th>
          <th class="text-right" style="width: 140px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          @php $gap = max(0, $product->minimum_stock - $product->stock); @endphp
          <tr>
            <td class="num font-semibold text-muted">{{ $product->sku }}</td>
            <td>
              <a href="{{ route('products.show', $product) }}" class="font-semibold" style="color: var(--brand-navy);">
                {{ $product->name }}
              </a>
            </td>
            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
            <td class="num text-right font-bold" style="{{ $product->stock <= 0 ? 'color: var(--action-danger);' : 'color: var(--status-warning-text);' }}">
              {{ $product->stock }}
            </td>
            <td class="num text-right text-muted">{{ $product->minimum_stock }}</td>
            <td class="num text-right font-bold" style="color: var(--action-danger);">
              -{{ $gap }} units
            </td>
            <td>
              @if($product->stock <= 0)
                <span class="badge badge-danger">Out of Stock</span>
              @else
                <span class="badge badge-warning">Low Stock</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('products.show', $product) }}" class="btn btn-secondary btn-sm">Inspect</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted" style="padding: var(--space-12);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon" style="color: var(--brand-sage);">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
              <div class="empty-state-title" style="color: var(--status-success-text);">All stock levels healthy</div>
              <p class="empty-state-desc">There are no products with stock below their minimum safety thresholds.</p>
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
