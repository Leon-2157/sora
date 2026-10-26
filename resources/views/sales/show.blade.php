@extends('layouts.app')

@section('title', 'Receipt ' . $sale->sale_number)
@section('page-title', 'Sales Receipt')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-3);">
    <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm">
      ← Back to Sales
    </a>
    <a href="{{ route('sales.pos') }}" class="btn btn-primary btn-sm">
      POS Terminal
    </a>
  </div>

  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
    <div>
      <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: 6px;">
        <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;" class="num">{{ $sale->sale_number }}</h1>
        @if($sale->status === 'completed')
          <span class="badge badge-success"><span class="badge-dot"></span>Completed</span>
        @elseif($sale->status === 'pending')
          <span class="badge badge-warning"><span class="badge-dot"></span>Pending</span>
        @else
          <span class="badge badge-danger"><span class="badge-dot"></span>Cancelled</span>
        @endif
      </div>
      <p class="text-muted" style="font-size: 0.875rem;">
        Sale recorded on {{ $sale->sold_at ? $sale->sold_at->format('l, d F Y \a\t H:i:s') : '—' }}
      </p>
    </div>

    <div style="display: flex; gap: var(--space-2);">
      <button type="button" class="btn btn-secondary" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>Print Receipt</span>
      </button>

      @if($sale->status === 'pending' && auth()->user()->isAdmin())
        <form action="{{ route('sales.complete', $sale) }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-strong" onclick="return confirm('Complete this sale and deduct stock?')">
            Complete Sale
          </button>
        </form>

        <form action="{{ route('sales.cancel', $sale) }}" method="POST" style="display:inline;" onsubmit="return confirm('Cancel this sale?');">
          @csrf
          <button type="submit" class="btn btn-danger-outline">Cancel</button>
        </form>
      @endif
    </div>
  </div>
</div>

@if($sale->status === 'cancelled')
  <div class="alert alert-danger" role="alert" style="margin-bottom: var(--space-6);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="15" y1="9" x2="9" y2="15"></line>
      <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <div>
      <strong>This sales order has been cancelled.</strong>
    </div>
  </div>
@endif

<!-- Sale Info Meta Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-5); margin-bottom: var(--space-6);">
  <div class="card" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Served By (Cashier)</div>
      <div style="font-size: 1.125rem; font-weight: 700;">{{ $sale->user->name ?? '—' }}</div>
      <div class="text-sm text-muted" style="margin-top: 4px;">Email: {{ $sale->user->email ?? '—' }}</div>
    </div>
  </div>

  <div class="card" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Transaction Status</div>
      <div style="font-size: 1.125rem; font-weight: 700; text-transform: capitalize;">{{ $sale->status }}</div>
      <div class="text-sm text-muted" style="margin-top: 4px;">Payment Method: Cash / Instant</div>
    </div>
  </div>

  <div class="card stat-navy" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Grand Total</div>
      <div class="stat-value num" style="font-size: 1.875rem;">
        Rp {{ number_format($sale->total, 0, ',', '.') }}
      </div>
      <div class="stat-subtext">{{ $sale->items->count() }} items purchased</div>
    </div>
  </div>
</div>

<!-- Receipt Table Card -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Purchased Items</h2>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>SKU</th>
          <th class="text-right">Quantity</th>
          <th class="text-right">Unit Price</th>
          <th class="text-right">Line Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sale->items as $index => $item)
          <tr>
            <td class="text-muted">{{ $index + 1 }}</td>
            <td>
              @if($item->product)
                <a href="{{ route('products.show', $item->product) }}" class="font-semibold" style="color: var(--brand-navy);">
                  {{ $item->product->name }}
                </a>
              @else
                <span class="font-semibold text-muted">[Product Deleted]</span>
              @endif
            </td>
            <td class="num text-muted">{{ $item->product->sku ?? '—' }}</td>
            <td class="num text-right font-semibold">{{ $item->quantity }}</td>
            <td class="num text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="num text-right font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background: var(--bg-surface-subtle); border-top: 2px solid var(--border-default);">
          <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1rem; padding: var(--space-4);">
            Total Payment:
          </td>
          <td class="num text-right font-bold" style="font-size: 1.25rem; color: var(--brand-navy); padding: var(--space-4);">
            Rp {{ number_format($sale->total, 0, ',', '.') }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
