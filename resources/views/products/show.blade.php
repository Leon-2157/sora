@extends('layouts.app')

@section('title', $product->name . ' — Details')
@section('page-title', 'Product Details')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: var(--space-3);">
    ← Back to Products
  </a>
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
    <div>
      <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: 6px;">
        <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;">{{ $product->name }}</h1>
        @if($product->stock <= 0)
          <span class="badge badge-danger">Out of Stock</span>
        @elseif($product->stock <= $product->minimum_stock)
          <span class="badge badge-warning">Low Stock</span>
        @else
          <span class="badge badge-success">In Stock</span>
        @endif
      </div>
      <div class="text-muted" style="font-size: 0.875rem;">
        SKU: <strong class="num">{{ $product->sku }}</strong> &nbsp;|&nbsp; Category: <strong>{{ $product->category->name ?? 'Uncategorized' }}</strong>
      </div>
    </div>

    @if(auth()->user()->isAdmin())
      <div style="display: flex; gap: var(--space-2);">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
            <path d="M12 20h9"></path>
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
          </svg>
          <span>Edit Product</span>
        </a>
        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product permanently?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger-outline">Delete</button>
        </form>
      </div>
    @endif
  </div>
</div>

<!-- Product Metrics Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">Current Stock</span>
    </div>
    <div class="stat-value num {{ $product->stock <= 0 ? 'text-danger' : '' }}" style="{{ $product->stock <= 0 ? 'color: var(--action-danger);' : '' }}">
      {{ $product->stock }}
    </div>
    <div class="stat-subtext">Min. threshold: {{ $product->minimum_stock }} units</div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">Selling Price</span>
    </div>
    <div class="stat-value num">
      Rp {{ number_format($product->selling_price, 0, ',', '.') }}
    </div>
    <div class="stat-subtext">Active retail price</div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">Latest Unit Cost</span>
    </div>
    <div class="stat-value num text-muted">
      Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
    </div>
    <div class="stat-subtext">From last procurement order</div>
  </div>

  <div class="stat-card stat-navy">
    <div class="stat-header">
      <span class="stat-label">Unit Margin</span>
    </div>
    @php
      $margin = $product->selling_price - $product->purchase_price;
      $marginPct = $product->selling_price > 0 ? round(($margin / $product->selling_price) * 100, 1) : 0;
    @endphp
    <div class="stat-value num">
      Rp {{ number_format($margin, 0, ',', '.') }}
    </div>
    <div class="stat-subtext">{{ $marginPct }}% gross margin</div>
  </div>
</div>

<!-- Transaction History: Purchases & Sales -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: var(--space-6);">
  <!-- Recent Procurement -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Procurement History (Purchases)</h2>
      <a href="{{ route('purchases.create') }}" class="btn btn-outline btn-sm">+ New Purchase</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Supplier</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Cost</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentPurchases as $item)
            <tr>
              <td>
                <a href="{{ route('purchases.show', $item->purchase) }}" class="font-semibold num">
                  {{ $item->purchase->purchase_number }}
                </a>
              </td>
              <td>{{ $item->purchase->supplier->name ?? '—' }}</td>
              <td class="num text-right font-semibold">{{ $item->quantity }}</td>
              <td class="num text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: var(--space-8);">
                No purchase transactions recorded for this product.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Outflow / Sales -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Sales Outflow</h2>
      <a href="{{ route('sales.pos') }}" class="btn btn-outline btn-sm">Launch POS</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Receipt #</th>
            <th>Sold By</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Price</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentSales as $item)
            <tr>
              <td>
                <a href="{{ route('sales.show', $item->sale) }}" class="font-semibold num">
                  {{ $item->sale->sale_number }}
                </a>
              </td>
              <td>{{ $item->sale->user->name ?? '—' }}</td>
              <td class="num text-right font-semibold">{{ $item->quantity }}</td>
              <td class="num text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: var(--space-8);">
                No sales recorded for this product yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
