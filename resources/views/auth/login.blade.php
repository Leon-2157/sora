@extends('layouts.guest')

@section('title', 'Sign In — Sora')

@section('content')
<div class="login-card glass">
  <div style="text-align: center; margin-bottom: var(--space-6);">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: var(--brand-sage); border-radius: var(--radius-md); color: var(--brand-navy); font-weight: 800; font-size: 1.5rem; margin-bottom: var(--space-3); font-family: var(--font-ui);">
      S
    </div>
    <h1 style="font-size: 1.5rem; margin-bottom: 4px;">Sign in to Sora</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Inventory & Sales Management System</p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger" role="alert">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <div>{{ $errors->first() }}</div>
    </div>
  @endif

  <form action="{{ route('login.post') }}" method="POST" data-prevent-double-submit>
    @csrf

    <div class="form-group">
      <label for="email" class="form-label">Email Address <span class="required">*</span></label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        class="form-control @error('email') is-invalid @enderror" 
        value="{{ old('email') }}" 
        required 
        autofocus 
        placeholder="name@company.com"
      >
    </div>

    <div class="form-group">
      <label for="password" class="form-label">Password <span class="required">*</span></label>
      <input 
        type="password" 
        id="password" 
        name="password" 
        class="form-control @error('password') is-invalid @enderror" 
        required 
        placeholder="••••••••"
      >
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
      <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <span>Remember me</span>
      </label>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
      Sign In
    </button>
  </form>

  <div style="margin-top: var(--space-8); padding-top: var(--space-5); border-top: 1px solid var(--border-light); font-size: 0.8125rem;">
    <div style="font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Demo Accounts:</div>
    <div style="display: flex; flex-direction: column; gap: 4px; color: var(--text-muted);">
      <div>• <strong>Admin:</strong> <code>admin@sora.test</code> / <code>password</code></div>
      <div>• <strong>Staff:</strong> <code>staff@sora.test</code> / <code>password</code></div>
    </div>
  </div>
</div>
@endsection
