@extends('layouts.app')

@section('title', 'Purchase ' . $purchase->purchase_number)
@section('page-title', 'Purchase Order Details')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: var(--space-3);">
    ← Back to Purchases
  </a>
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
    <div>
      <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: 6px;">
        <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;" class="num">{{ $purchase->purchase_number }}</h1>
        @if($purchase->status === 'completed')
          <span class="badge badge-success"><span class="badge-dot"></span>Completed</span>
        @elseif($purchase->status === 'pending')
          <span class="badge badge-warning"><span class="badge-dot"></span>Pending</span>
        @else
          <span class="badge badge-danger"><span class="badge-dot"></span>Cancelled</span>
        @endif
      </div>
      <p class="text-muted" style="font-size: 0.875rem;">
        Procurement order recorded on {{ $purchase->purchased_at ? $purchase->purchased_at->format('l, d F Y \a\t H:i') : '—' }}
      </p>
    </div>

    @if($purchase->status === 'pending' && auth()->user()->isAdmin())
      <div style="display: flex; gap: var(--space-2);">
        <form action="{{ route('purchases.complete', $purchase) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-strong" onclick="return confirm('Complete this purchase order? This will immediately add items to product stock.')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Complete Order</span>
          </button>
        </form>

        <button type="button" class="btn btn-danger-outline" onclick="openModal('cancelPurchaseModal')">
          Cancel Order
        </button>
      </div>
    @endif
  </div>
</div>

@if($purchase->status === 'cancelled')
  <div class="alert alert-danger" role="alert" style="margin-bottom: var(--space-6);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="15" y1="9" x2="9" y2="15"></line>
      <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <div>
      <strong>This purchase order has been cancelled.</strong> No inventory stock was affected by this transaction.
    </div>
  </div>
@endif

<!-- Order Information Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-5); margin-bottom: var(--space-6);">
  <div class="card" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Supplier Details</div>
      <div style="font-size: 1.125rem; font-weight: 700;">{{ $purchase->supplier->name ?? '—' }}</div>
      <div class="text-sm text-muted" style="margin-top: 4px;">Phone: {{ $purchase->supplier->phone ?? '—' }}</div>
      <div class="text-sm text-muted">Email: {{ $purchase->supplier->email ?? '—' }}</div>
    </div>
  </div>

  <div class="card" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Recorded By</div>
      <div style="font-size: 1.125rem; font-weight: 700;">{{ $purchase->user->name ?? '—' }}</div>
      <div class="text-sm text-muted" style="margin-top: 4px;">Email: {{ $purchase->user->email ?? '—' }}</div>
      <div class="text-sm text-muted">Role: {{ strtoupper($purchase->user->role ?? 'STAFF') }}</div>
    </div>
  </div>

  <div class="card stat-navy" style="margin-bottom: 0;">
    <div class="card-body">
      <div class="text-muted text-sm font-semibold" style="text-transform: uppercase; margin-bottom: 4px;">Total Order Value</div>
      <div class="stat-value num" style="font-size: 1.875rem;">
        Rp {{ number_format($purchase->total, 0, ',', '.') }}
      </div>
      <div class="stat-subtext">{{ $purchase->items->count() }} line items</div>
    </div>
  </div>
</div>

<!-- Ordered Items Table Card -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Procured Items</h2>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>SKU</th>
          <th class="text-right">Quantity</th>
          <th class="text-right">Unit Cost</th>
          <th class="text-right">Line Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase->items as $index => $item)
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
            <td class="num text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
            <td class="num text-right font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background: var(--bg-surface-subtle); border-top: 2px solid var(--border-default);">
          <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1rem; padding: var(--space-4);">
            Grand Total:
          </td>
          <td class="num text-right font-bold" style="font-size: 1.25rem; color: var(--brand-navy); padding: var(--space-4);">
            Rp {{ number_format($purchase->total, 0, ',', '.') }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Cancel Purchase Confirmation Modal -->
@if($purchase->status === 'pending')
<div id="cancelPurchaseModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form action="{{ route('purchases.cancel', $purchase) }}" method="POST">
      @csrf
      <div class="card-header">
        <h3 class="card-title" style="color: var(--action-danger);">Cancel Purchase Order?</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('cancelPurchaseModal')">✕</button>
      </div>
      <div class="card-body">
        <p style="margin-bottom: var(--space-4);">
          This purchase order (<strong>{{ $purchase->purchase_number }}</strong>) is still pending. Cancelling will mark it as cancelled without altering inventory stock.
        </p>
        <p class="text-muted text-sm">
          Are you sure you want to proceed with cancelling this transaction?
        </p>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('cancelPurchaseModal')">Back</button>
        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection
