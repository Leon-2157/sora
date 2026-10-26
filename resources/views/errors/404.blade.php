@extends('layouts.app')

@section('title', '404 — Page Not Found')
@section('page-title', 'Not Found')

@section('content')
<div style="max-width: 560px; margin: var(--space-12) auto; text-align: center;">
  <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: var(--radius-pill); background-color: var(--bg-surface-muted); color: var(--text-secondary); margin-bottom: var(--space-4);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:36px;height:36px;">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="8" x2="12" y2="12"></line>
      <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
  </div>

  <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: var(--space-2);">404 — Page Not Found</h1>
  <p class="text-muted" style="font-size: 1rem; margin-bottom: var(--space-6);">
    The record or page you requested could not be located in the inventory system.
  </p>

  <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
    ← Return to Dashboard
  </a>
</div>
@endsection
