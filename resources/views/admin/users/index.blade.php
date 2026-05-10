@extends('layouts.app')

@section('title', 'Users Management')
@section('breadcrumb', 'Users')
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin-users-index.css') }}">
@endpush

@section('content')
    <div class="premium-container container-fluid">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-end mb-4 gap-3">
            <div class="text-center text-md-start">
                <h1 class="page-title mb-1">Community Members</h1>
                <p class="text-muted lead mb-0" style="font-size: 1rem;">Managing roles and permissions for system users.
                </p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-gold-action shadow-none">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>



        <div class="premium-card">
            <div class="table-responsive" style="overflow: visible;">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Member Since</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr style="animation: slideUp {{ 0.3 + ($loop->index * 0.05) }}s ease forwards;">
                                <td data-label="ID">
                                    <span class="text-muted fw-bold">{{ $user->id }}</span>
                                </td>
                                <td data-label="User">
                                    <div class="d-flex align-items-center">

                                        <div class="text-end text-md-start">
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-muted small" style="font-size: 0.8rem;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role">
                                    @if ($user->role == 1)
                                        <span class="user-badge"
                                            style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">Technical</span>
                                    @else
                                        <span class="user-badge" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                            User</span>
                                    @endif
                                </td>
                                <td data-label="Member Since">
                                    <div class="text-muted small">{{ $user->created_at->format('M d, Y') }}</div>
                                </td>
                                <td data-label="Actions" class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-link text-dark p-2" type="button" id="userActions{{ $user->id }}"
                                            data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false"
                                            style="background: var(--bg-light); border-radius: 8px;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 dropdown-menu-premium"
                                            aria-labelledby="userActions{{ $user->id }}">
                                            <li>
                                                <button class="dropdown-item dropdown-item-premium py-2" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal" data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}"
                                                    data-user-role="{{ $user->role }}">
                                                    <i class="fas fa-edit me-2 text-primary"></i> Edit Details
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item dropdown-item-premium py-2" data-bs-toggle="modal"
                                                    data-bs-target="#changePasswordModal" data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}">
                                                    <i class="fas fa-key me-2 text-warning"></i> Reset Password
                                                </button>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="dropdown-item dropdown-item-premium py-2 text-danger"
                                                        onclick="return confirm('Securely delete this user record?')">
                                                        <i class="fas fa-trash-alt me-2"></i> Remove User
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>


    @include('admin.users._edit_modal')
    @include('admin.users._password_modal')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');
                var userEmail = button.getAttribute('data-user-email');
                var userRole = button.getAttribute('data-user-role');

                document.getElementById('editUserNameDisplay').textContent = userName;
                document.getElementById('edit_name').value = userName;
                document.getElementById('edit_email').value = userEmail;
                document.getElementById('edit_role').value = userRole;
                document.getElementById('editUserForm').action = '/admin/users/' + userId;
            });

            var changePasswordModal = document.getElementById('changePasswordModal');
            changePasswordModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');

                document.getElementById('userName').textContent = userName;
                document.getElementById('changePasswordForm').action = '/admin/users/' + userId +
                    '/update-password';
            });
        });
    </script>
@endpush