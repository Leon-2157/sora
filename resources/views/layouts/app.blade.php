<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — Sora Inventory & Sales</title>

  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
  @stack('styles')
</head>
<body>
  @php
    $lowStockCount = \App\Models\Product::whereRaw('stock <= minimum_stock')->count();
    $currentUser = auth()->user();
  @endphp

  <div class="app-wrapper">
    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Navigation Sidebar -->
    <aside id="appSidebar" class="app-sidebar" aria-label="Main Navigation">
      <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="brand-logo">
          <span class="brand-mark">S</span>
          <span>SORA</span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">Main Menu</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="9" rx="1"></rect>
            <rect x="14" y="3" width="7" height="5" rx="1"></rect>
            <rect x="14" y="12" width="7" height="9" rx="1"></rect>
            <rect x="3" y="16" width="7" height="5" rx="1"></rect>
          </svg>
          <span>Dashboard</span>
        </a>

        @if($currentUser && $currentUser->isStaff())
        <a href="{{ route('sales.pos') }}" class="nav-link {{ request()->routeIs('sales.pos') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
          <span>POS Quick Sale</span>
        </a>
        @endif

        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') && !request()->routeIs('products.low-stock') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
          </svg>
          <span>Products</span>
        </a>

        <a href="{{ route('products.low-stock') }}" class="nav-link {{ request()->routeIs('products.low-stock') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <span>Low Stock</span>
          @if($lowStockCount > 0)
            <span class="nav-badge num">{{ $lowStockCount }}</span>
          @endif
        </a>

        <div class="nav-section-title">Transactions</div>
        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
          <span>Purchases</span>
        </a>

        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') || request()->routeIs('sales.create') || request()->routeIs('sales.show') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <span>Sales</span>
        </a>

        @if($currentUser && $currentUser->isAdmin())
        <div class="nav-section-title">Administration</div>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          <span>Categories</span>
        </a>

        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="3" width="15" height="13"></rect>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
            <circle cx="5.5" cy="18.5" r="2.5"></circle>
            <circle cx="18.5" cy="18.5" r="2.5"></circle>
          </svg>
          <span>Suppliers</span>
        </a>

        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
          <span>Users</span>
        </a>
        @endif
      </nav>

      <div class="sidebar-footer">
        <div class="user-mini-card">
          <div class="user-avatar">
            {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
          </div>
          <div class="user-details">
            <div class="user-name">{{ $currentUser->name ?? 'Guest' }}</div>
            <div class="user-role-badge">{{ $currentUser->role ?? 'staff' }}</div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Workspace Area -->
    <div class="app-main">
      <!-- Glass Topbar (Permitted Glass Element 1) -->
      <header class="app-topbar glass">
        <div class="topbar-left">
          <button type="button" id="mobileMenuBtn" class="mobile-menu-btn" aria-label="Toggle navigation menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
          <div class="breadcrumb-area">
            <span>Sora</span>
            <span>/</span>
            <span class="page-current">@yield('page-title', 'Dashboard')</span>
          </div>
        </div>

        <div class="topbar-right">
          <span class="badge {{ $currentUser && $currentUser->isAdmin() ? 'badge-warning' : 'badge-neutral' }}">
            {{ strtoupper($currentUser->role ?? 'STAFF') }}
          </span>

          <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Sign Out</span>
            </button>
          </form>
        </div>
      </header>

      <!-- Content Container -->
      <main class="content-body" id="mainContent">
        <!-- Flash Feedback Messages -->
        @if(session('success'))
          <div class="alert alert-success" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div style="flex:1;">{{ session('success') }}</div>
            <button type="button" class="alert-dismiss" data-dismiss="alert" aria-label="Close">✕</button>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div style="flex:1;">{{ session('error') }}</div>
            <button type="button" class="alert-dismiss" data-dismiss="alert" aria-label="Close">✕</button>
          </div>
        @endif

        @if(session('warning'))
          <div class="alert alert-warning" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0;">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div style="flex:1;">{{ session('warning') }}</div>
            <button type="button" class="alert-dismiss" data-dismiss="alert" aria-label="Close">✕</button>
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
