@extends('layouts.app')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Suppliers</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Manage procurement vendor contacts</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addSupplierModal')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
      <line x1="12" y1="5" x2="12" y2="19"></line>
      <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
    <span>Add Supplier</span>
  </button>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('suppliers.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search suppliers by name, phone, or email..." 
      value="{{ $search }}"
    >
    <button type="submit" class="btn btn-secondary">Apply</button>
    @if($search)
      <a href="{{ route('suppliers.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Suppliers Table Card -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Supplier Name</th>
          <th>Phone</th>
          <th>Email Address</th>
          <th class="text-right">Purchases</th>
          <th class="text-right" style="width: 160px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $supplier)
          <tr>
            <td>
              <div class="font-semibold">{{ $supplier->name }}</div>
            </td>
            <td>{{ $supplier->phone ?? '—' }}</td>
            <td>{{ $supplier->email ?? '—' }}</td>
            <td class="num text-right font-semibold">
              <span class="badge badge-neutral">{{ $supplier->purchases_count }} orders</span>
            </td>
            <td class="text-right">
              <div style="display: flex; justify-content: flex-end; gap: var(--space-2);">
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  onclick="openEditSupplierModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->email ?? '') }}')"
                >
                  Edit
                </button>
                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete supplier \'{{ $supplier->name }}\'?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted" style="padding: var(--space-12);">
              No suppliers found. Click "Add Supplier" to register one.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($suppliers->hasPages())
    <div class="card-footer">
      {{ $suppliers->links() }}
    </div>
  @endif
</div>

<!-- Add Supplier Modal -->
<div id="addSupplierModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form action="{{ route('suppliers.store') }}" method="POST" data-prevent-double-submit>
      @csrf
      <div class="card-header">
        <h3 class="card-title">Add Supplier</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('addSupplierModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="new_supplier_name" class="form-label">Company / Supplier Name <span class="required">*</span></label>
          <input type="text" id="new_supplier_name" name="name" class="form-control" required placeholder="e.g. PT Sumber Makmur">
        </div>
        <div class="form-group">
          <label for="new_supplier_phone" class="form-label">Phone Number</label>
          <input type="text" id="new_supplier_phone" name="phone" class="form-control" placeholder="e.g. 081234567890">
        </div>
        <div class="form-group">
          <label for="new_supplier_email" class="form-label">Email Address</label>
          <input type="email" id="new_supplier_email" name="email" class="form-control" placeholder="sales@supplier.com">
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('addSupplierModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Supplier</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Supplier Modal -->
<div id="editSupplierModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form id="editSupplierForm" method="POST" data-prevent-double-submit>
      @csrf
      @method('PUT')
      <div class="card-header">
        <h3 class="card-title">Edit Supplier</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('editSupplierModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="edit_supplier_name" class="form-label">Company / Supplier Name <span class="required">*</span></label>
          <input type="text" id="edit_supplier_name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_supplier_phone" class="form-label">Phone Number</label>
          <input type="text" id="edit_supplier_phone" name="phone" class="form-control">
        </div>
        <div class="form-group">
          <label for="edit_supplier_email" class="form-label">Email Address</label>
          <input type="email" id="edit_supplier_email" name="email" class="form-control">
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('editSupplierModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Supplier</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEditSupplierModal(id, name, phone, email) {
  const form = document.getElementById('editSupplierForm');
  form.action = `/suppliers/${id}`;
  document.getElementById('edit_supplier_name').value = name;
  document.getElementById('edit_supplier_phone').value = phone;
  document.getElementById('edit_supplier_email').value = email;
  openModal('editSupplierModal');
}
</script>
@endpush
