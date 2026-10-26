@extends('layouts.app')

@section('title', 'Record Sale')
@section('page-title', 'New Sale Form')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: var(--space-3);">
    ← Back to Sales
  </a>
  <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Record Sale Order</h1>
  <p class="text-muted" style="font-size: 0.875rem;">Create a sales order. Completed status immediately deducts items from inventory.</p>
</div>

<form action="{{ route('sales.store') }}" method="POST" id="saleForm" data-prevent-double-submit>
  @csrf

  <div class="transaction-layout">
    <!-- Left Column: Details & Items -->
    <div>
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Transaction Header</h2>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label for="sold_at" class="form-label">Sale Date & Time <span class="required">*</span></label>
              <input 
                type="datetime-local" 
                id="sold_at" 
                name="sold_at" 
                class="form-control @error('sold_at') is-invalid @enderror" 
                value="{{ old('sold_at', now()->format('Y-m-d\TH:i')) }}" 
                required
              >
              @error('sold_at')
                <div class="form-error">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="status" class="form-label">Status <span class="required">*</span></label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>Completed (Immediate stock deduction)</option>
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Reserve only, stock deducted upon completion)</option>
              </select>
              @error('status')
                <div class="form-error">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items Card -->
      <div class="card">
        <div class="card-header" style="justify-content: space-between;">
          <h2 class="card-title">Items Sold</h2>
          <button type="button" id="addRowBtn" class="btn btn-secondary btn-sm">
            + Add Another Item
          </button>
        </div>
        <div class="card-body">
          <div id="lineItemsList">
            <!-- Row 0 -->
            <div class="line-item-row" data-index="0">
              <div>
                <label class="form-label" style="font-size:0.75rem;">Product <span class="required">*</span></label>
                <select name="items[0][product_id]" class="form-select product-select" required>
                  <option value="">Select Product...</option>
                  @foreach($products as $prod)
                    <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price }}" data-stock="{{ $prod->stock }}">
                      {{ $prod->name }} (Available: {{ $prod->stock }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="form-label" style="font-size:0.75rem;">Qty <span class="required">*</span></label>
                <input type="number" name="items[0][quantity]" class="form-control num qty-input" value="1" min="1" step="1" required>
              </div>

              <div>
                <label class="form-label" style="font-size:0.75rem;">Unit Price (Rp)</label>
                <input type="number" name="items[0][unit_price]" class="form-control num price-input" value="0" min="0" step="500" required>
              </div>

              <div>
                <label class="form-label" style="font-size:0.75rem;">Subtotal</label>
                <div class="subtotal-val num font-bold" style="padding: 9px 0;">Rp 0</div>
              </div>

              <div style="padding-top: 18px; text-align: center;">
                <button type="button" class="btn btn-outline btn-sm remove-row-btn" style="color: var(--action-danger); padding: 6px 10px;" title="Remove row">✕</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Summary Card -->
    <div class="card totals-sticky-card">
      <div class="card-header">
        <h2 class="card-title">Order Summary</h2>
      </div>
      <div class="card-body">
        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-3); font-size: 0.9375rem;">
          <span class="text-muted">Total Rows:</span>
          <span id="summaryRowCount" class="num font-semibold">1</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-3); font-size: 0.9375rem;">
          <span class="text-muted">Total Units:</span>
          <span id="summaryTotalUnits" class="num font-semibold">1</span>
        </div>

        <div style="margin-top: var(--space-5); padding-top: var(--space-4); border-top: 1px dashed var(--border-default);">
          <div style="font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 4px;">
            Estimated Grand Total
          </div>
          <div id="summaryGrandTotal" class="num font-bold" style="font-size: 1.75rem; color: var(--brand-navy); font-family: var(--font-ui);">
            Rp 0
          </div>
          <p class="text-muted" style="font-size: 0.75rem; margin-top: 4px;">
            * Official total is calculated and stock checked on the server upon checkout.
          </p>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: var(--space-6);">
          Complete Sale
        </button>
      </div>
    </div>
  </div>
</form>

<template id="rowTemplate">
  <div class="line-item-row" data-index="__INDEX__">
    <div>
      <label class="form-label" style="font-size:0.75rem;">Product <span class="required">*</span></label>
      <select name="items[__INDEX__][product_id]" class="form-select product-select" required>
        <option value="">Select Product...</option>
        @foreach($products as $prod)
          <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price }}" data-stock="{{ $prod->stock }}">
            {{ $prod->name }} (Available: {{ $prod->stock }})
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="form-label" style="font-size:0.75rem;">Qty <span class="required">*</span></label>
      <input type="number" name="items[__INDEX__][quantity]" class="form-control num qty-input" value="1" min="1" step="1" required>
    </div>

    <div>
      <label class="form-label" style="font-size:0.75rem;">Unit Price (Rp)</label>
      <input type="number" name="items[__INDEX__][unit_price]" class="form-control num price-input" value="0" min="0" step="500" required>
    </div>

    <div>
      <label class="form-label" style="font-size:0.75rem;">Subtotal</label>
      <div class="subtotal-val num font-bold" style="padding: 9px 0;">Rp 0</div>
    </div>

    <div style="padding-top: 18px; text-align: center;">
      <button type="button" class="btn btn-outline btn-sm remove-row-btn" style="color: var(--action-danger); padding: 6px 10px;" title="Remove row">✕</button>
    </div>
  </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('lineItemsList');
  const template = document.getElementById('rowTemplate');
  const addBtn = document.getElementById('addRowBtn');
  const summaryRowCountEl = document.getElementById('summaryRowCount');
  const summaryTotalUnitsEl = document.getElementById('summaryTotalUnits');
  const summaryGrandTotalEl = document.getElementById('summaryGrandTotal');
  let rowCount = 1;

  function recalculate() {
    let rows = container.querySelectorAll('.line-item-row');
    let totalUnits = 0;
    let grandTotal = 0;

    rows.forEach(row => {
      const qtyInput = row.querySelector('.qty-input');
      const priceInput = row.querySelector('.price-input');
      const subtotalEl = row.querySelector('.subtotal-val');

      const qty = parseInt(qtyInput.value, 10) || 0;
      const price = parseFloat(priceInput.value) || 0;
      const subtotal = qty * price;

      subtotalEl.textContent = formatRupiah(subtotal);
      totalUnits += qty;
      grandTotal += subtotal;
    });

    summaryRowCountEl.textContent = rows.length;
    summaryTotalUnitsEl.textContent = totalUnits;
    summaryGrandTotalEl.textContent = formatRupiah(grandTotal);
  }

  function bindRowEvents(row) {
    const productSelect = row.querySelector('.product-select');
    const qtyInput = row.querySelector('.qty-input');
    const priceInput = row.querySelector('.price-input');
    const removeBtn = row.querySelector('.remove-row-btn');

    productSelect.addEventListener('change', () => {
      const selected = productSelect.options[productSelect.selectedIndex];
      if (selected && selected.dataset.price) {
        priceInput.value = selected.dataset.price;
      }
      recalculate();
    });

    qtyInput.addEventListener('input', () => {
      const selected = productSelect.options[productSelect.selectedIndex];
      if (selected && selected.dataset.stock) {
        const available = parseInt(selected.dataset.stock, 10);
        const requested = parseInt(qtyInput.value, 10);
        if (requested > available) {
          showToast(`Warning: Requested quantity (${requested}) exceeds available stock (${available}).`, 'warning');
        }
      }
      recalculate();
    });

    priceInput.addEventListener('input', recalculate);

    removeBtn.addEventListener('click', () => {
      if (container.querySelectorAll('.line-item-row').length <= 1) {
        showToast('At least one item row is required in the sale order.', 'warning');
        return;
      }
      row.remove();
      recalculate();
    });
  }

  const firstRow = container.querySelector('.line-item-row');
  if (firstRow) bindRowEvents(firstRow);

  addBtn.addEventListener('click', () => {
    const html = template.innerHTML.replace(/__INDEX__/g, rowCount);
    const div = document.createElement('div');
    div.innerHTML = html.trim();
    const newRow = div.firstChild;

    container.appendChild(newRow);
    bindRowEvents(newRow);
    rowCount++;
    recalculate();
  });

  recalculate();
});
</script>
@endpush
