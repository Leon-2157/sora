@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Terminal')

@section('content')
<!-- POS Hero Banner -->
<div style="background: linear-gradient(135deg, var(--brand-navy) 0%, #1c234b 100%); color: #FFFFFF; border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-6); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="color: #FFFFFF; font-size: 1.75rem; font-weight: 800; margin-bottom: 6px;">Cashier & Sales Terminal</h1>
    <p style="color: var(--nav-text-muted); font-size: 0.9375rem;">Fast point of sale checkout with instant inventory verification</p>
  </div>
  <a href="{{ route('sales.pos') }}" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
      <rect x="2" y="3" width="20" height="14" rx="2"></rect>
      <line x1="8" y1="21" x2="16" y2="21"></line>
      <line x1="12" y1="17" x2="12" y2="21"></line>
    </svg>
    <span>Launch POS Terminal</span>
  </a>
</div>

<!-- My Daily Performance -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">My Sales Today</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
          <path d="M12 6v2m0 8v2"></path>
        </svg>
      </div>
    </div>
    <div class="stat-value num">Rp {{ number_format($mySalesToday, 0, ',', '.') }}</div>
    <div class="stat-subtext">Completed sales recorded by you</div>
  </div>

  <div class="stat-card stat-warning">
    <div class="stat-header">
      <span class="stat-label">Transactions Today</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
          <polyline points="17 6 23 6 23 12"></polyline>
        </svg>
      </div>
    </div>
    <div class="stat-value num">{{ $myTxCountToday }}</div>
    <div class="stat-subtext">Receipts processed today</div>
  </div>
</div>

<!-- Recent Sales by Staff & Low Stock Panel -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: var(--space-6);">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">My Recent Sales</h2>
      <a href="{{ route('sales.pos') }}" class="btn btn-primary btn-sm">+ New Sale</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Receipt #</th>
            <th>Time</th>
            <th class="text-right">Total</th>
            <th>Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentSales as $sale)
            <tr>
              <td class="font-semibold num">{{ $sale->sale_number }}</td>
              <td>{{ $sale->sold_at ? $sale->sold_at->format('H:i:s') : '-' }}</td>
              <td class="num text-right font-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
              <td>
                <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                  {{ $sale->status }}
                </span>
              </td>
              <td class="text-right">
                <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">Receipt</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted" style="padding: var(--space-8);">
                No sales processed yet today.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Low Stock Notification</h2>
      <a href="{{ route('products.low-stock') }}" class="btn btn-outline btn-sm">Check Stock</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th class="text-right">Stock</th>
            <th>Notice</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStockProducts as $prod)
            <tr>
              <td>
                <div class="font-semibold">{{ $prod->name }}</div>
                <div class="text-sm text-muted">{{ $prod->sku }}</div>
              </td>
              <td class="num text-right font-bold" style="{{ $prod->stock <= 0 ? 'color: var(--action-danger);' : '' }}">
                {{ $prod->stock }}
              </td>
              <td>
                @if($prod->stock <= 0)
                  <span class="badge badge-danger">Out of Stock</span>
                @else
                  <span class="badge badge-warning">Low (Min: {{ $prod->minimum_stock }})</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted" style="padding: var(--space-8);">
                Inventory levels are in good condition.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
