@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'System Users')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
  <div>
    <h1 style="font-size: 1.625rem; font-weight: 800; margin-bottom: 4px;">User Accounts</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Manage employee credentials and system access roles</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addUserModal')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="9" cy="7" r="4"></circle>
      <line x1="20" y1="8" x2="20" y2="14"></line>
      <line x1="23" y1="11" x2="17" y2="11"></line>
    </svg>
    <span>Add User</span>
  </button>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <form action="{{ route('users.index') }}" method="GET" style="display: flex; gap: var(--space-3); width: 100%; flex-wrap: wrap;">
    <input 
      type="text" 
      name="search" 
      class="form-control filter-input" 
      placeholder="Search users by name or email..." 
      value="{{ $search }}"
    >
    <select name="role" class="form-select filter-select">
      <option value="">All Roles</option>
      <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrator</option>
      <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Cashier / Staff</option>
    </select>
    <button type="submit" class="btn btn-secondary">Apply Filters</button>
    @if($search || $role)
      <a href="{{ route('users.index') }}" class="btn btn-outline">Reset</a>
    @endif
  </form>
</div>

<!-- Users Table -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>User</th>
          <th>Email Address</th>
          <th>Role</th>
          <th>Member Since</th>
          <th class="text-right" style="width: 160px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: var(--space-3);">
                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8125rem;">
                  {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                  <div class="font-semibold">{{ $user->name }}</div>
                  @if($user->id === auth()->id())
                    <span class="text-sm" style="color: var(--brand-sage); font-weight: 600;">(You)</span>
                  @endif
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>
              <span class="badge {{ $user->isAdmin() ? 'badge-warning' : 'badge-neutral' }}">
                {{ strtoupper($user->role) }}
              </span>
            </td>
            <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</td>
            <td class="text-right">
              <div style="display: flex; justify-content: flex-end; gap: var(--space-2);">
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')"
                >
                  Edit
                </button>
                @if($user->id !== auth()->id())
                  <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete user account \'{{ $user->name }}\'?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted" style="padding: var(--space-12);">
              No user accounts found matching your query.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($users->hasPages())
    <div class="card-footer">
      {{ $users->links() }}
    </div>
  @endif
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form action="{{ route('users.store') }}" method="POST" data-prevent-double-submit>
      @csrf
      <div class="card-header">
        <h3 class="card-title">Add User Account</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('addUserModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="new_user_name" class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" id="new_user_name" name="name" class="form-control" required placeholder="e.g. Budi Santoso">
        </div>
        <div class="form-group">
          <label for="new_user_email" class="form-label">Email Address <span class="required">*</span></label>
          <input type="email" id="new_user_email" name="email" class="form-control" required placeholder="user@company.com">
        </div>
        <div class="form-group">
          <label for="new_user_password" class="form-label">Password <span class="required">*</span></label>
          <input type="password" id="new_user_password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
        </div>
        <div class="form-group">
          <label for="new_user_role" class="form-label">Role Permission <span class="required">*</span></label>
          <select id="new_user_role" name="role" class="form-select" required>
            <option value="staff">Staff / Cashier (Standard Operations)</option>
            <option value="admin">Administrator (Full Access)</option>
          </select>
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Account</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-backdrop" style="display: none;">
  <div class="modal-dialog">
    <form id="editUserForm" method="POST" data-prevent-double-submit>
      @csrf
      @method('PUT')
      <div class="card-header">
        <h3 class="card-title">Edit User Account</h3>
        <button type="button" class="alert-dismiss" onclick="closeModal('editUserModal')">✕</button>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="edit_user_name" class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" id="edit_user_name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_user_email" class="form-label">Email Address <span class="required">*</span></label>
          <input type="email" id="edit_user_email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_user_password" class="form-label">New Password (leave empty to keep current)</label>
          <input type="password" id="edit_user_password" name="password" class="form-control" minlength="8" placeholder="••••••••">
        </div>
        <div class="form-group">
          <label for="edit_user_role" class="form-label">Role Permission <span class="required">*</span></label>
          <select id="edit_user_role" name="role" class="form-select" required>
            <option value="staff">Staff / Cashier</option>
            <option value="admin">Administrator</option>
          </select>
        </div>
      </div>
      <div class="card-footer" style="justify-content: flex-end; gap: var(--space-3);">
        <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update User</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEditUserModal(id, name, email, role) {
  const form = document.getElementById('editUserForm');
  form.action = `/users/${id}`;
  document.getElementById('edit_user_name').value = name;
  document.getElementById('edit_user_email').value = email;
  document.getElementById('edit_user_password').value = '';
  document.getElementById('edit_user_role').value = role;
  openModal('editUserModal');
}
</script>
@endpush
