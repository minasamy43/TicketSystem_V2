@extends('layouts.app')

@section('title', 'Users Management')
@section('breadcrumb', 'Users')
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #d4af53;
            --gold-light: #d4af53;
            --gold-dark: #d4af53;
            --bg-light: #f8f9fa;
            --card-bg: rgba(255, 255, 255, 0.9);
            --border-soft: rgba(0, 0, 0, 0.05);
            --text-dark: #1a1a1a;
            --text-muted: #6c757d;
        }

        .premium-container {
            font-family: 'Outfit', sans-serif;
        }

        .premium-container {
            padding: 2.5rem 1rem;
        }

        .premium-card {
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        .page-title {
            font-weight: 700;
            font-size: 2.2rem;
            background: linear-gradient(135deg, var(--text-dark) 0%, var(--gold-primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.3rem;
        }

        .btn-gold-action {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.6rem;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-gold-action:hover {
            background: var(--gold-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 83, 0.3);
        }

        .table-premium {
            border-collapse: separate;
            border-spacing: 0 8px;
            width: 100%;
        }

        .table-premium thead th {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
        }

        .table-premium tbody tr {
            background: #fff;
            border-radius: 12px;
            transition: all 0.2s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.01);
        }

        .table-premium tbody td {
            padding: 1.2rem 1.5rem;
            border: none;
            vertical-align: middle;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            font-weight: 500;
        }

        .table-premium tbody tr td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .table-premium tbody tr td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .user-badge {
            font-weight: 700;
            font-size: 0.7rem;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
        }

        .dropdown-menu-premium {
            border-radius: 14px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 0.6rem;
            z-index: 1050;
        }

        /* Fix for dropdown clipping/z-index in table rows */
        .table-premium tbody tr {
            position: relative;
        }

        .table-premium tbody tr:hover,
        .table-premium tbody tr:focus-within {
            z-index: 50;
        }

        .dropdown-item-premium {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .dropdown-item-premium:hover {
            background: var(--bg-light);
        }

        .modal-content-premium {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .form-control-premium {
            background: var(--bg-light);
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
        }

        .form-control-premium:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 10px rgba(212, 175, 83, 0.1);
            background: #fff;
        }

        /* Mobile Responsiveness Rules */
        @media (max-width: 991px) {
            .premium-container {
                padding: 1.5rem 0.5rem;
            }

            .premium-card {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.6rem;
                text-align: center;
            }

            .text-muted.lead {
                text-align: center;
                font-size: 0.9rem !important;
            }

            .btn-gold-action {
                margin-top: 5px;
            }

            .table-premium thead {
                display: none;
            }

            .table-premium,
            .table-premium tbody,
            .table-premium tr,
            .table-premium td {
                display: block;
                width: 100%;
            }

            .table-premium tbody tr {
                margin-bottom: 1.2rem;
                padding: 0.5rem;
                border: 1px solid var(--border-soft);
                background: #fff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
                border-radius: 16px !important;
                display: flex;
                flex-direction: column;
            }

            .table-premium tbody td {
                padding: 0.8rem 1rem !important;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
            }

            .table-premium tbody td[data-label="ID"] {
                order: 1;
            }

            .table-premium tbody td[data-label="User"] {
                order: 2;
            }

            .table-premium tbody td[data-label="Role"] {
                order: 3;
            }

            .table-premium tbody td[data-label="Member Since"] {
                order: 4;
            }

            .table-premium tbody td::before {
                content: attr(data-label) " : ";
                font-weight: 700;
                font-size: 0.75rem;
                color: var(--text-muted);
                margin-right: 15px;
                text-align: left;
            }

            .table-premium tbody td:last-child {
                order: -1;
                padding-bottom: 0.5rem !important;
                margin-bottom: 0.5rem;
                border-bottom: 1px solid rgba(0, 0, 0, 0.04);
                justify-content: flex-end;
            }

            .table-premium tbody td:last-child::before {
                display: none;
            }

            .table-premium tbody td.text-end {
                text-align: right !important;
            }

            .table-responsive {
                overflow: visible !important;
            }
        }
    </style>
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

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4"
                style="border-radius: 12px; background: #e7f5ed; color: #1a5131;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

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

    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

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