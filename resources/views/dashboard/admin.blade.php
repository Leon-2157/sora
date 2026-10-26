@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Business Overview</h1>
  <p class="text-muted" style="font-size: 0.875rem;">Real-time inventory and sales operations snapshot</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">Sales Today</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
          <path d="M12 6v2m0 8v2"></path>
        </svg>
      </div>
    </div>
    <div class="stat-value num">Rp {{ number_format($salesToday, 0, ',', '.') }}</div>
    <div class="stat-subtext">Completed sales transactions</div>
  </div>

  <div class="stat-card stat-navy">
    <div class="stat-header">
      <span class="stat-label">Sales This Month</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
      </div>
    </div>
    <div class="stat-value num">Rp {{ number_format($salesThisMonth, 0, ',', '.') }}</div>
    <div class="stat-subtext">{{ now()->format('F Y') }} total revenue</div>
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
    <div class="stat-value num">{{ $transactionsToday }}</div>
    <div class="stat-subtext">Purchases & sales combined</div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <span class="stat-label">Active Products</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
      </div>
    </div>
    <div class="stat-value num">{{ $totalProducts }}</div>
    <div class="stat-subtext">Catalogued items</div>
  </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-6);">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">7-Day Sales Trend</h2>
      <span class="badge badge-neutral">Daily Revenue</span>
    </div>
    <div class="card-body">
      <canvas id="weeklySalesChart" style="max-height: 260px; width: 100%;"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Monthly Revenue (6 Months)</h2>
      <span class="badge badge-neutral">Completed</span>
    </div>
    <div class="card-body">
      <canvas id="monthlySalesChart" style="max-height: 260px; width: 100%;"></canvas>
    </div>
  </div>
</div>

<!-- Split Grid: Top Selling & Low Stock Alerts -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-6);">
  <!-- Top 5 Products -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Top 5 Selling Products</h2>
      <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th class="text-right">Qty Sold</th>
            <th class="text-right">Total Revenue</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topProducts as $item)
            <tr>
              <td>
                <div class="font-semibold">{{ $item->product->name ?? 'Unknown Product' }}</div>
                <div class="text-sm text-muted">{{ $item->product->sku ?? '-' }}</div>
              </td>
              <td class="num text-right font-semibold">{{ $item->total_qty }}</td>
              <td class="num text-right font-bold">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted" style="padding: var(--space-8);">
                No sales recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Low Stock Warning Panel -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Low Stock Alert</h2>
      <a href="{{ route('products.low-stock') }}" class="btn btn-danger-outline btn-sm">
        View All ({{ $lowStockProducts->count() }})
      </a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Min Stock</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStockProducts as $prod)
            <tr>
              <td>
                <div class="font-semibold">{{ $prod->name }}</div>
                <div class="text-sm text-muted">{{ $prod->category->name ?? 'Uncategorized' }}</div>
              </td>
              <td class="num text-right font-bold {{ $prod->stock <= 0 ? 'text-danger' : '' }}" style="{{ $prod->stock <= 0 ? 'color: var(--action-danger);' : '' }}">
                {{ $prod->stock }}
              </td>
              <td class="num text-right text-muted">{{ $prod->minimum_stock }}</td>
              <td>
                @if($prod->stock <= 0)
                  <span class="badge badge-danger">Out of Stock</span>
                @else
                  <span class="badge badge-warning">Low Stock</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: var(--space-8);">
                All product stock levels are healthy.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Recent Transactions -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Recent Transactions</h2>
    <div style="display: flex; gap: var(--space-2);">
      <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm">Sales</a>
      <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm">Purchases</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Transaction #</th>
          <th>Type</th>
          <th>Date</th>
          <th>Recorder / Supplier</th>
          <th class="text-right">Total</th>
          <th>Status</th>
          <th class="text-right">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentSales as $sale)
          <tr>
            <td class="font-semibold num">{{ $sale->sale_number }}</td>
            <td><span class="badge badge-neutral">Sale</span></td>
            <td>{{ $sale->sold_at ? $sale->sold_at->format('d M Y H:i') : '-' }}</td>
            <td>{{ $sale->user->name ?? '-' }}</td>
            <td class="num text-right font-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            <td>
              @if($sale->status === 'completed')
                <span class="badge badge-success"><span class="badge-dot"></span>Completed</span>
              @elseif($sale->status === 'pending')
                <span class="badge badge-warning"><span class="badge-dot"></span>Pending</span>
              @else
                <span class="badge badge-danger"><span class="badge-dot"></span>Cancelled</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">Details</a>
            </td>
          </tr>
        @endforeach

        @foreach($recentPurchases as $purchase)
          <tr>
            <td class="font-semibold num">{{ $purchase->purchase_number }}</td>
            <td><span class="badge badge-neutral">Purchase</span></td>
            <td>{{ $purchase->purchased_at ? $purchase->purchased_at->format('d M Y H:i') : '-' }}</td>
            <td>{{ $purchase->supplier->name ?? '-' }}</td>
            <td class="num text-right font-bold">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
            <td>
              @if($purchase->status === 'completed')
                <span class="badge badge-success"><span class="badge-dot"></span>Completed</span>
              @elseif($purchase->status === 'pending')
                <span class="badge badge-warning"><span class="badge-dot"></span>Pending</span>
              @else
                <span class="badge badge-danger"><span class="badge-dot"></span>Cancelled</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary btn-sm">Details</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Weekly Sales Bar Chart
  const ctxWeekly = document.getElementById('weeklySalesChart');
  if (ctxWeekly) {
    new Chart(ctxWeekly, {
      type: 'bar',
      data: {
        labels: {!! json_encode($last7Days) !!},
        datasets: [{
          label: 'Revenue (Rp)',
          data: {!! json_encode($sales7Days) !!},
          backgroundColor: '#81A186',
          hoverBackgroundColor: '#739578',
          borderRadius: 6,
          maxBarThickness: 38
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => 'Revenue: ' + formatRupiah(ctx.raw)
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            beginAtZero: true,
            ticks: {
              callback: (val) => 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'M' : (val/1000).toFixed(0) + 'k')
            },
            grid: { color: '#E3E4D7' }
          }
        }
      }
    });
  }

  // Monthly Sales Line Chart
  const ctxMonthly = document.getElementById('monthlySalesChart');
  if (ctxMonthly) {
    new Chart(ctxMonthly, {
      type: 'line',
      data: {
        labels: {!! json_encode($monthlyLabels) !!},
        datasets: [{
          label: 'Monthly Sales',
          data: {!! json_encode($monthlySales) !!},
          borderColor: '#0C0F22',
          backgroundColor: 'rgba(129, 161, 134, 0.25)',
          fill: true,
          tension: 0.35,
          pointBackgroundColor: '#C4AC46',
          pointBorderColor: '#0C0F22',
          pointRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => 'Revenue: ' + formatRupiah(ctx.raw)
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            beginAtZero: true,
            ticks: {
              callback: (val) => 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'M' : (val/1000).toFixed(0) + 'k')
            },
            grid: { color: '#E3E4D7' }
          }
        }
      }
    });
  }
});
</script>
@endpush
