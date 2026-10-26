@extends('layouts.app')

@section('title', 'Add New Product')
@section('page-title', 'Create Product')

@section('content')
<div style="max-width: 720px; margin: 0 auto;">
  <div style="margin-bottom: var(--space-6);">
    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: var(--space-3);">
      ← Back to Products
    </a>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">New Product Record</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Register a new item in the catalog. Initial stock is managed via Purchases.</p>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('products.store') }}" method="POST" data-prevent-double-submit>
        @csrf

        <div class="form-group">
          <label for="category_id" class="form-label">Category <span class="required">*</span></label>
          <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Select a Category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
          @error('category_id')
            <div class="form-error">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="sku" class="form-label">Product SKU (Stock Keeping Unit) <span class="required">*</span></label>
          <input 
            type="text" 
            id="sku" 
            name="sku" 
            class="form-control @error('sku') is-invalid @enderror" 
            value="{{ old('sku') }}" 
            placeholder="e.g. EL-MOU-001" 
            required
          >
          @error('sku')
            <div class="form-error">{{ $message }}</div>
          @else
            <div class="form-hint">Must be unique across all catalog products.</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Product Name <span class="required">*</span></label>
          <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name') }}" 
            placeholder="e.g. Ergonomic Wireless Mouse" 
            required
          >
          @error('name')
            <div class="form-error">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="selling_price" class="form-label">Selling Price (Rp) <span class="required">*</span></label>
            <input 
              type="number" 
              id="selling_price" 
              name="selling_price" 
              class="form-control num @error('selling_price') is-invalid @enderror" 
              value="{{ old('selling_price') }}" 
              min="0" 
              step="500" 
              placeholder="0" 
              required
            >
            @error('selling_price')
              <div class="form-error">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label for="minimum_stock" class="form-label">Minimum Stock Alert Threshold <span class="required">*</span></label>
            <input 
              type="number" 
              id="minimum_stock" 
              name="minimum_stock" 
              class="form-control num @error('minimum_stock') is-invalid @enderror" 
              value="{{ old('minimum_stock', 5) }}" 
              min="0" 
              step="1" 
              required
            >
            @error('minimum_stock')
              <div class="form-error">{{ $message }}</div>
            @else
              <div class="form-hint">Triggers warning alert when current stock ≤ this value.</div>
            @enderror
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6); padding-top: var(--space-5); border-top: 1px solid var(--border-light);">
          <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
