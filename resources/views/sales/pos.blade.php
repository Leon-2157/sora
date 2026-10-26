@extends('layouts.app')

@section('title', 'POS Quick Sale')
@section('page-title', 'Point of Sale')

@section('content')
<div class="pos-layout">
  <!-- Left Side: Product Catalog & Search -->
  <div class="pos-catalog">
    <!-- Search Bar & Category Filters -->
    <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
      <div class="pos-search-wrapper" style="flex: 1; min-width: 240px;">
        <svg class="pos-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input 
          type="text" 
          id="posSearchInput" 
          class="form-control pos-search-input" 
          placeholder="Search products by name or SKU (Press / to focus)..." 
          autocomplete="off"
        >
      </div>

      <select id="posCategoryFilter" class="form-select" style="width: auto; min-width: 170px;">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>

    <!-- Product Grid -->
    <div id="posGrid" class="pos-grid">
      @forelse($products as $prod)
        <button 
          type="button" 
          class="pos-card" 
          data-id="{{ $prod->id }}"
          data-sku="{{ $prod->sku }}"
          data-name="{{ $prod->name }}"
          data-price="{{ $prod->selling_price }}"
          data-stock="{{ $prod->stock }}"
          data-category="{{ $prod->category_id }}"
          {{ $prod->stock <= 0 ? 'disabled' : '' }}
        >
          <div class="pos-card-sku">{{ $prod->sku }}</div>
          <div class="pos-card-name">{{ $prod->name }}</div>
          <div class="pos-card-footer">
            <div class="pos-card-price num">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</div>
            <div>
              @if($prod->stock <= 0)
                <span class="badge badge-danger">Out</span>
              @elseif($prod->stock <= $prod->minimum_stock)
                <span class="badge badge-warning num">{{ $prod->stock }} left</span>
              @else
                <span class="badge badge-neutral num">{{ $prod->stock }} in stock</span>
              @endif
            </div>
          </div>
        </button>
      @empty
        <div class="empty-state" style="grid-column: 1 / -1;">
          <div class="empty-state-title">No products found</div>
          <p class="empty-state-desc">There are no products available in the inventory catalogue.</p>
        </div>
      @endforelse
    </div>
  </div>

  <!-- Right Side: Interactive POS Cart Panel -->
  <div class="pos-cart-panel">
    <div class="pos-cart-header">
      <h2 style="font-size: 1.125rem; font-weight: 700; margin: 0;">Current Order</h2>
      <button type="button" id="clearCartBtn" class="btn btn-outline btn-sm" style="color: var(--action-danger);">
        Clear Cart
      </button>
    </div>

    <!-- Cart Items Scroll List -->
    <div id="cartItemsContainer" class="pos-cart-items">
      <div id="cartEmptyState" class="empty-state" style="padding: var(--space-8) var(--space-4);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <div class="empty-state-title" style="font-size: 1rem;">Cart is empty</div>
        <p class="empty-state-desc" style="font-size: 0.8125rem;">Click on items in the catalog to add them to this order.</p>
      </div>
    </div>

    <!-- Cart Summary & Checkout Form -->
    <form id="posCheckoutForm" action="{{ route('sales.store') }}" method="POST" class="pos-cart-footer" data-prevent-double-submit>
      @csrf
      <input type="hidden" name="status" value="completed">
      <input type="hidden" name="is_pos" value="1">
      <input type="hidden" name="sold_at" value="{{ now()->format('Y-m-d H:i:s') }}">
      <div id="hiddenCartInputs"></div>

      <div class="pos-summary-row">
        <span>Total Items:</span>
        <span id="cartTotalItems" class="num font-semibold">0</span>
      </div>

      <div class="pos-total-row">
        <span>Grand Total</span>
        <div id="cartGrandTotal" class="pos-total-amount num">Rp 0</div>
      </div>

      <button type="submit" id="posSubmitBtn" class="btn btn-strong btn-lg" style="width: 100%; margin-top: var(--space-4);" disabled>
        Complete & Pay
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const cart = {};
  const searchInput = document.getElementById('posSearchInput');
  const categoryFilter = document.getElementById('posCategoryFilter');
  const posGrid = document.getElementById('posGrid');
  const cartContainer = document.getElementById('cartItemsContainer');
  const cartEmptyState = document.getElementById('cartEmptyState');
  const cartTotalItemsEl = document.getElementById('cartTotalItems');
  const cartGrandTotalEl = document.getElementById('cartGrandTotal');
  const hiddenInputsContainer = document.getElementById('hiddenCartInputs');
  const submitBtn = document.getElementById('posSubmitBtn');
  const clearCartBtn = document.getElementById('clearCartBtn');

  // Shortcut key "/" to focus search
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput) {
      e.preventDefault();
      searchInput.focus();
    }
  });

  // Filter Catalog
  function filterCatalog() {
    const query = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;

    const cards = posGrid.querySelectorAll('.pos-card');
    cards.forEach((card) => {
      const name = card.dataset.name.toLowerCase();
      const sku = card.dataset.sku.toLowerCase();
      const cat = card.dataset.category;

      const matchesSearch = name.includes(query) || sku.includes(query);
      const matchesCat = !category || cat === category;

      card.style.display = (matchesSearch && matchesCat) ? 'flex' : 'none';
    });
  }

  searchInput.addEventListener('input', filterCatalog);
  categoryFilter.addEventListener('change', filterCatalog);

  // Click Product Card
  posGrid.addEventListener('click', (e) => {
    const card = e.target.closest('.pos-card');
    if (!card || card.disabled) return;

    const id = card.dataset.id;
    const name = card.dataset.name;
    const price = parseFloat(card.dataset.price);
    const stock = parseInt(card.dataset.stock, 10);

    addToCart(id, name, price, stock);
  });

  function addToCart(id, name, price, stock) {
    if (cart[id]) {
      if (cart[id].qty >= stock) {
        showToast(`Stock limit reached for "${name}". Maximum available: ${stock}.`, 'danger');
        return;
      }
      cart[id].qty++;
    } else {
      if (stock < 1) {
        showToast(`"${name}" is currently out of stock.`, 'danger');
        return;
      }
      cart[id] = { id, name, price, stock, qty: 1 };
    }
    renderCart();
  }

  function updateQty(id, newQty) {
    if (!cart[id]) return;

    if (newQty <= 0) {
      delete cart[id];
    } else if (newQty > cart[id].stock) {
      showToast(`Only ${cart[id].stock} units available in stock.`, 'danger');
      cart[id].qty = cart[id].stock;
    } else {
      cart[id].qty = newQty;
    }
    renderCart();
  }

  function removeFromCart(id) {
    delete cart[id];
    renderCart();
  }

  clearCartBtn.addEventListener('click', () => {
    for (const key in cart) delete cart[key];
    renderCart();
  });

  function renderCart() {
    const items = Object.values(cart);
    hiddenInputsContainer.innerHTML = '';

    if (items.length === 0) {
      cartContainer.innerHTML = '';
      cartContainer.appendChild(cartEmptyState);
      cartEmptyState.style.display = 'block';
      cartTotalItemsEl.textContent = '0';
      cartGrandTotalEl.textContent = 'Rp 0';
      submitBtn.disabled = true;
      return;
    }

    cartEmptyState.style.display = 'none';
    cartContainer.innerHTML = '';

    let totalItems = 0;
    let grandTotal = 0;

    items.forEach((item, index) => {
      const lineSubtotal = item.qty * item.price;
      totalItems += item.qty;
      grandTotal += lineSubtotal;

      // Hidden inputs for form submit
      hiddenInputsContainer.innerHTML += `
        <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
        <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
        <input type="hidden" name="items[${index}][unit_price]" value="${item.price}">
      `;

      // Visual cart row
      const row = document.createElement('div');
      row.className = 'cart-item-row';
      row.innerHTML = `
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-price num">${formatRupiah(item.price)}</div>
        </div>
        <div class="cart-qty-ctrl">
          <button type="button" class="qty-btn" onclick="window.posUpdateQty('${item.id}', ${item.qty - 1})">−</button>
          <span class="qty-val num">${item.qty}</span>
          <button type="button" class="qty-btn" onclick="window.posUpdateQty('${item.id}', ${item.qty + 1})">+</button>
        </div>
        <div class="cart-item-subtotal num">${formatRupiah(lineSubtotal)}</div>
        <button type="button" class="btn btn-outline btn-sm" style="color:var(--action-danger); padding:2px 6px;" onclick="window.posRemove('${item.id}')">✕</button>
      `;
      cartContainer.appendChild(row);
    });

    cartTotalItemsEl.textContent = totalItems;
    cartGrandTotalEl.textContent = formatRupiah(grandTotal);
    submitBtn.disabled = false;
  }

  window.posUpdateQty = updateQty;
  window.posRemove = removeFromCart;
});
</script>
@endpush
