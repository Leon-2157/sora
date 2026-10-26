@extends('layouts.app')

@section('title', 'Create Purchase Order')
@section('page-title', 'New Purchase')

@section('content')
<div style="margin-bottom: var(--space-6);">
  <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: var(--space-3);">
    ← Back to Purchases
  </a>
  <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Record Purchase Order</h1>
  <p class="text-muted" style="font-size: 0.875rem;">Procure inventory from suppliers. Completed status increments stock immediately.</p>
</div>

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" data-prevent-double-submit>
  @csrf

  <div class="transaction-layout">
    <!-- Left Column: Form Details & Dynamic Line Items -->
    <div>
      <!-- Order Header Card -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Order Details</h2>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label for="supplier_id" class="form-label">Supplier <span class="required">*</span></label>
              <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                <option value="">Select a Supplier</option>
                @foreach($suppliers as $sup)
                  <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                    {{ $sup->name }}
                  </option>
                @endforeach
              </select>
              @error('supplier_id')
                <div class="form-error">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="purchased_at" class="form-label">Order Date & Time <span class="required">*</span></label>
              <input 
                type="datetime-local" 
                id="purchased_at" 
                name="purchased_at" 
                class="form-control @error('purchased_at') is-invalid @enderror" 
                value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}" 
                required
              >
              @error('purchased_at')
                <div class="form-error">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="status" class="form-label">Initial Status <span class="required">*</span></label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Stock unchanged until completed)</option>
                <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>Completed (Increases stock immediately)</option>
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
          <h2 class="card-title">Order Items</h2>
          <button type="button" id="addRowBtn" class="btn btn-secondary btn-sm">
            + Add Another Item
          </button>
        </div>
        <div class="card-body">
          <div id="lineItemsList">
            <!-- Line Item Row 0 -->
            <div class="line-item-row" data-index="0">
              <div>
                <label class="form-label" style="font-size:0.75rem;">Product <span class="required">*</span></label>
                <select name="items[0][product_id]" class="form-select product-select" required>
                  <option value="">Select Product...</option>
                  @foreach($products as $prod)
                    <option value="{{ $prod->id }}" data-cost="{{ $prod->purchase_price }}">
                      {{ $prod->name }} ({{ $prod->sku }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="form-label" style="font-size:0.75rem;">Qty <span class="required">*</span></label>
                <input type="number" name="items[0][quantity]" class="form-control num qty-input" value="1" min="1" step="1" required>
              </div>

              <div>
                <label class="form-label" style="font-size:0.75rem;">Unit Cost (Rp) <span class="required">*</span></label>
                <input type="number" name="items[0][unit_cost]" class="form-control num cost-input" value="0" min="0" step="500" required>
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

    <!-- Right Column: Sticky Totals Summary Card -->
    <div class="card totals-sticky-card">
      <div class="card-header">
        <h2 class="card-title">Summary</h2>
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
            * Official total is verified and calculated on the server upon submission.
          </p>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: var(--space-6);">
          Save Purchase
        </button>
      </div>
    </div>
  </div>
</form>

<!-- Row Template for JS Cloning -->
<template id="rowTemplate">
  <div class="line-item-row" data-index="__INDEX__">
    <div>
      <label class="form-label" style="font-size:0.75rem;">Product <span class="required">*</span></label>
      <select name="items[__INDEX__][product_id]" class="form-select product-select" required>
        <option value="">Select Product...</option>
        @foreach($products as $prod)
          <option value="{{ $prod->id }}" data-cost="{{ $prod->purchase_price }}">
            {{ $prod->name }} ({{ $prod->sku }})
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="form-label" style="font-size:0.75rem;">Qty <span class="required">*</span></label>
      <input type="number" name="items[__INDEX__][quantity]" class="form-control num qty-input" value="1" min="1" step="1" required>
    </div>

    <div>
      <label class="form-label" style="font-size:0.75rem;">Unit Cost (Rp) <span class="required">*</span></label>
      <input type="number" name="items[__INDEX__][unit_cost]" class="form-control num cost-input" value="0" min="0" step="500" required>
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
      const costInput = row.querySelector('.cost-input');
      const subtotalEl = row.querySelector('.subtotal-val');

      const qty = parseInt(qtyInput.value, 10) || 0;
      const cost = parseFloat(costInput.value) || 0;
      const subtotal = qty * cost;

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
    const costInput = row.querySelector('.cost-input');
    const removeBtn = row.querySelector('.remove-row-btn');

    productSelect.addEventListener('change', () => {
      const selected = productSelect.options[productSelect.selectedIndex];
      if (selected && selected.dataset.cost) {
        costInput.value = selected.dataset.cost;
      }
      recalculate();
    });

    qtyInput.addEventListener('input', recalculate);
    costInput.addEventListener('input', recalculate);

    removeBtn.addEventListener('click', () => {
      if (container.querySelectorAll('.line-item-row').length <= 1) {
        showToast('At least one item row is required in the purchase order.', 'warning');
        return;
      }
      row.remove();
      recalculate();
    });
  }

  // Bind initial row
  const firstRow = container.querySelector('.line-item-row');
  if (firstRow) bindRowEvents(firstRow);

  // Add row
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
