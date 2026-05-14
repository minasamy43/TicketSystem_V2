@extends('layouts.app')
@section('title', 'Customer Hub')
@section('breadcrumb', 'Dashboard')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard-new.css') }}">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #fcfcfd;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        {{-- Hero Section --}}
        <div class="hub-hero">
            <h1 class="hero-title">Hello, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h1>
            <p class="hero-subtitle">Welcome to your support portal. Submit tickets, track their status, and get help.</p>
            <form method="GET" action="{{ route('user.dashboard') }}" id="filterForm" style="display:none;">
                <input type="hidden" name="subject">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="date" value="{{ request('date') }}">
            </form>
        </div>


        {{-- Quick Actions --}}
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <a href="{{ route('knowledge.base') }}" class="action-card">
                    <div class="action-icon-wrapper" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 class="action-title">Knowledge Base</h3>
                    <p class="action-desc">Search for instant answers in our documentation and frequently asked questions.
                    </p>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('user.tickets.create') }}" class="action-card">
                    <div class="action-icon-wrapper">
                        <i class="fa-solid fa-plus-circle"></i>
                    </div>
                    <h3 class="action-title">Create Ticket</h3>
                    <p class="action-desc">Need assistance? Open a new support ticket and our team will help you shortly.
                    </p>
                </a>
            </div>

        </div>

        {{-- Ticket Management Section --}}
        <div class="section-title">
            <span>Recent Tickets</span>

            <div class="d-flex gap-2 ms-auto align-items-center">
                <select name="status" class="form-select form-select-sm border-0 shadow-sm"
                    style="border-radius: 8px; width: auto;"
                    onchange="const f = document.getElementById('filterForm'); f.status.value = this.value; f.submit();">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in progress" {{ request('status') == 'in progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-light border shadow-sm"
                    style="border-radius: 8px;" title="Reset Filters">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </div>

        <div class="modern-ticket-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Ticket Details</th>
                            <th>Current Status</th>
                            <th>Last Activity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr data-ticket-id="{{ $ticket->id }}" style="cursor: pointer;">
                                <td>
                                    <div class="ticket-subject">{{ $ticket->subject }}</div>
                                    <div class="ticket-meta">
                                        <span class="me-2">#{{ $ticket->id }}</span>
                                        <span>Submitted: {{ $ticket->created_at->format('M d, Y') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = str_replace(' ', '-', $ticket->status);
                                        $statusEmoji = $ticket->status == 'open' ? '🎟️' : ($ticket->status == 'in progress' ? '👍🏻' : '✅️');
                                    @endphp
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statusEmoji }} {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #333; font-size: 0.9rem;">
                                        {{ $ticket->updated_at->diffForHumans() }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #aaa;">
                                        {{ $ticket->updated_at->format('g:i A') }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @php $msgCount = $ticket->unread_replies_count ?? 0; @endphp
                                        <a href="javascript:void(0)" onclick="openAdminChat({{ $ticket->id }})"
                                            class="chat-btn-modern" title="Open Chat">
                                            <svg viewBox="0 0 256 256" width="24" height="24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <defs>
                                                    <linearGradient id="messenger-grad" x1="0" y1="1" x2="1" y2="0">
                                                        <stop offset="0%" stop-color="#00C6FF" />
                                                        <stop offset="50%" stop-color="#0078FF" />
                                                        <stop offset="100%" stop-color="#A033FF" />
                                                    </linearGradient>
                                                </defs>
                                                <path fill="url(#messenger-grad)"
                                                    d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" />
                                            </svg>
                                            @if($msgCount > 0)
                                                <span id="unread-count-{{ $ticket->id }}"
                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm"
                                                    style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">
                                                    {{ $msgCount > 99 ? '99+' : $msgCount }}
                                                </span>
                                            @endif
                                        </a>
                                        <button type="button" class="chat-btn-modern" title="Delete Ticket"
                                            style="background: rgba(220,53,69,0.08); border-color: rgba(220,53,69,0.15); color: #dc3545;"
                                            onclick="confirmDelete({{ $ticket->id }}, '{{ addslashes($ticket->subject) }}')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;">📭</div>
                                    @if(request('date') || request('status') || request('subject'))
                                        <h4 class="fw-bold">No tickets found</h4>
                                        <p class="text-muted">No tickets match your current filters. Try adjusting your search.</p>
                                        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary px-4 py-2 mt-2"
                                            style="border-radius: 10px; font-weight: 600;">
                                            <i class="fa-solid fa-rotate-right me-2"></i> Clear Filters
                                        </a>
                                    @else
                                        <h4 class="fw-bold">No tickets yet</h4>
                                        <p class="text-muted">You haven't submitted any tickets yet. Let us know how we can help!</p>
                                        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary px-4 py-2 mt-2"
                                            style="border-radius: 10px; font-weight: 600;">
                                            <i class="fa-solid fa-plus me-2"></i> Open Your First Ticket
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($tickets->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <p class="text-muted small mb-0">
                    Showing <strong>{{ $tickets->firstItem() }}</strong> to <strong>{{ $tickets->lastItem() }}</strong> of
                    <strong>{{ $tickets->total() }}</strong> tickets
                </p>
                <div>
                    {{ $tickets->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:70px; height:70px; background: rgba(220,53,69,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 1.5rem;">
                        <i class="fa-solid fa-trash-can" style="font-size: 1.8rem; color: #dc3545;"></i>
                    </div>
                    <h4 style="font-weight: 800; color: #1a1a1a; margin-bottom: 0.5rem;">Delete Ticket?</h4>
                    <p class="text-muted" id="deleteModalSubject" style="font-size: 0.95rem; margin-bottom: 2rem;"></p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-light px-4 py-2" style="border-radius: 12px; font-weight: 600;"
                            data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger px-4 py-2"
                                style="border-radius: 12px; font-weight: 600;">
                                <i class="fa-solid fa-trash-can me-2"></i>Yes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials._chat')

    <script>
        const DASHBOARD_CONFIG = {
            highestTicketId: {{ $tickets->first()->id ?? 0 }},
            newDataUrl: '{{ route("user.dashboard.new-data") }}'
        };

        function confirmDelete(ticketId, subject) {
            document.getElementById('deleteModalSubject').textContent = 'This will permanently delete ticket: "' + subject + '"';
            document.getElementById('deleteForm').action = `/user/tickets/${ticketId}`;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Make entire row clickable to show ticket
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('tr[data-ticket-id]');
            rows.forEach(row => {
                row.addEventListener('click', function (e) {
                    if (!e.target.closest('a') && !e.target.closest('button')) {
                        const ticketId = this.getAttribute('data-ticket-id');
                        window.location.href = `{{ url('user/tickets') }}/${ticketId}`;
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('js/user-portal.js') }}"></script>
@endsection