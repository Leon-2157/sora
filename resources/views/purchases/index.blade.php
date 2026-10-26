@extends('layouts.app')

@section('title', 'Purchases')
@section('page-title', 'Procurement Orders')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Purchase Orders</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Procure inventory from registered suppliers</p>
  </div>
  <a href="{{ route('purchases.create') }}" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
      <line x1="12" y1="5" x2="12" y2="19"></line>
      <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
    <span>New Purchase Order</span>
  </a>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('purchases.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search by purchase number (e.g. PUR-)..." 
      value="{{ $search }}"
    >
    <select name="status" class="form-select filter-select">
      <option value="">All Statuses</option>
      <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <select name="supplier_id" class="form-select filter-select">
      <option value="">All Suppliers</option>
      @foreach($suppliers as $sup)
        <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
          {{ $sup->name }}
        </option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Apply Filters</button>
    @if($search || $status || $supplierId)
      <a href="{{ route('purchases.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Purchases Table Card -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Purchase #</th>
          <th>Date</th>
          <th>Supplier</th>
          <th>Recorded By</th>
          <th class="text-right">Grand Total</th>
          <th>Status</th>
          <th class="text-right" style="width: 120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchases as $purchase)
          <tr>
            <td>
              <a href="{{ route('purchases.show', $purchase) }}" class="font-semibold num" style="color: var(--brand-navy);">
                {{ $purchase->purchase_number }}
              </a>
            </td>
            <td>{{ $purchase->purchased_at ? $purchase->purchased_at->format('d M Y H:i') : '—' }}</td>
            <td>{{ $purchase->supplier->name ?? '—' }}</td>
            <td>{{ $purchase->user->name ?? '—' }}</td>
            <td class="num text-right font-bold">
              Rp {{ number_format($purchase->total, 0, ',', '.') }}
            </td>
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
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted" style="padding: var(--space-12);">
              No purchase orders found matching your filters.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($purchases->hasPages())
    <div class="card-footer">
      {{ $purchases->links() }}
    </div>
  @endif
</div>
@endsection
