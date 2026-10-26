@extends('layouts.app')

@section('title', 'Sales Transactions')
@section('page-title', 'Sales Transactions')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Sales Transactions</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Customer sales receipts and transaction history</p>
  </div>
  <div style="display: flex; gap: var(--space-2);">
    <a href="{{ route('sales.pos') }}" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
        <line x1="8" y1="21" x2="16" y2="21"></line>
        <line x1="12" y1="17" x2="12" y2="21"></line>
      </svg>
      <span>POS Quick Sale</span>
    </a>
    <a href="{{ route('sales.create') }}" class="btn btn-outline">
      <span>Standard Form</span>
    </a>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('sales.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search by sale number (e.g. SAL-)..." 
      value="{{ $search }}"
    >
    <select name="status" class="form-select filter-select">
      <option value="">All Statuses</option>
      <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <button type="submit" class="btn btn-secondary">Apply Filters</button>
    @if($search || $status)
      <a href="{{ route('sales.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Sales Table Card -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Sale Receipt #</th>
          <th>Date & Time</th>
          <th>Cashier / User</th>
          <th class="text-right">Total Amount</th>
          <th>Status</th>
          <th class="text-right" style="width: 120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sales as $sale)
          <tr>
            <td>
              <a href="{{ route('sales.show', $sale) }}" class="font-semibold num" style="color: var(--brand-navy);">
                {{ $sale->sale_number }}
              </a>
            </td>
            <td>{{ $sale->sold_at ? $sale->sold_at->format('d M Y H:i:s') : '—' }}</td>
            <td>{{ $sale->user->name ?? '—' }}</td>
            <td class="num text-right font-bold">
              Rp {{ number_format($sale->total, 0, ',', '.') }}
            </td>
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
              <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">Receipt</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted" style="padding: var(--space-12);">
              No sales transactions found matching your criteria.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($sales->hasPages())
    <div class="card-footer">
      {{ $sales->links() }}
    </div>
  @endif
</div>
@endsection
