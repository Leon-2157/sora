@extends('layouts.app')

@section('title', '403 — Access Denied')
@section('page-title', 'Unauthorized')

@section('content')
<div style="max-width: 560px; margin: var(--space-12) auto; text-align: center;">
  <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: var(--radius-pill); background-color: var(--status-danger-bg); color: var(--action-danger); margin-bottom: var(--space-4);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:36px;height:36px;">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
    </svg>
  </div>

  <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: var(--space-2);">403 — Access Denied</h1>
  <p class="text-muted" style="font-size: 1rem; margin-bottom: var(--space-6);">
    {{ $exception->getMessage() ?: 'You do not have administrative permission to access this section.' }}
  </p>

  <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
    ← Return to Dashboard
  </a>
</div>
@endsection
