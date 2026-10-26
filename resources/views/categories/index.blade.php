@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Product Categories')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">Categories</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Organize product catalog classifications</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addCategoryModal')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
      <line x1="12" y1="5" x2="12" y2="19"></line>
      <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
    <span>Add Category</span>
  </button>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('categories.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search category by name..." 
      value="{{ $search }}"
    >
    <button type="submit" class="btn btn-secondary">Apply</button>
    @if($search)
      <a href="{{ route('categories.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Table Card -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Category Name</th>
          <th class="text-right">Products Count</th>
          <th class="text-right" style="width: 160px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($categories as $category)
          <tr>
            <td>
              <div class="font-semibold">{{ $category->name }}</div>
            </td>
            <td class="num text-right font-semibold">
              <span class="badge badge-neutral">{{ $category->products_count }} products</span>
            </td>
            <td class="text-right">
              <div style="display: flex; justify-content: flex-end; gap: var(--space-2);">
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}')"
                >
                  Edit
                </button>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete category \'{{ $category->name }}\'?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center text-muted" style="padding: var(--space-12);">
              No categories found. Click "Add Category" to create one.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($categories->hasPages())
    <div class="card-footer">
      {{ $categories->links() }}
    </div>
  @endif
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form action="{{ route('categories.store') }}" method="POST" data-prevent-double-submit>
      @csrf
      <div class="card-header">
        <h3 class="card-title">Add New Category</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('addCategoryModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="new_category_name" class="form-label">Category Name <span class="required">*</span></label>
          <input type="text" id="new_category_name" name="name" class="form-control" required placeholder="e.g. Office Stationery">
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('addCategoryModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form id="editCategoryForm" method="POST" data-prevent-double-submit>
      @csrf
      @method('PUT')
      <div class="card-header">
        <h3 class="card-title">Edit Category</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('editCategoryModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="edit_category_name" class="form-label">Category Name <span class="required">*</span></label>
          <input type="text" id="edit_category_name" name="name" class="form-control" required>
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('editCategoryModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Category</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEditCategoryModal(id, name) {
  const form = document.getElementById('editCategoryForm');
  form.action = `/categories/${id}`;
  document.getElementById('edit_category_name').value = name;
  openModal('editCategoryModal');
}
</script>
@endpush
