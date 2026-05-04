@extends('layouts.app')

@section('title', 'Tickets Management')
@section('breadcrumb', 'Tickets')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin-tickets-index.css') }}">
@endpush

@section('content')


    <div class="container mt-4">


        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body p-0">
                <form method="GET" action="{{ route('admin.tickets.index') }}" id="filterForm">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th width="80">Id</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>In Progress By</th>
                                    <th>Closed By</th>
                                    <th>Date</th>
                                    <th class="text-center" width="90">Action</th>
                                </tr>
                                <tr style="background: var(--primary-light);">
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="ticket_id" id="filter_ticket_id"
                                            class="inline-filter-input search-icon-input" placeholder="ID..."
                                            value="{{ request('ticket_id') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="user_name" id="filter_user_name"
                                            class="inline-filter-input search-icon-input" placeholder="User..."
                                            value="{{ request('user_name') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="subject" id="filter_subject"
                                            class="inline-filter-input search-icon-input" placeholder="Subject..."
                                            value="{{ request('subject') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <select name="status" class="inline-filter-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">All Status</option>
                                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open
                                            </option>
                                            <option value="in progress" {{ request('status') == 'in progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>
                                                Closed
                                            </option>
                                        </select>
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="inprogress_name" id="filter_inprogress_name"
                                            class="inline-filter-input search-icon-input" placeholder="In progress by..."
                                            value="{{ request('inprogress_name') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="closer_name" id="filter_closer_name"
                                            class="inline-filter-input search-icon-input" placeholder="Closed by..."
                                            value="{{ request('closer_name') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="date" name="date" class="inline-filter-input" value="{{ $date }}"
                                            onchange="document.getElementById('filterForm').submit()">
                                    </td>
                                    <td class="text-center" style="padding: 10px 15px;">
                                        <a href="{{ route('admin.tickets.index') }}" class="btn-clear-inline"
                                            title="Clear Filters">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="1 4 1 10 7 10"></polyline>
                                                <polyline points="23 20 23 14 17 14"></polyline>
                                                <path
                                                    d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15">
                                                </path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr data-ticket-id="{{ $ticket->id }}"
                                        class="{{ !$ticket->has_admin_read ? 'unread-row' : '' }}">
                                        <td style="font-weight: 600; color: #000;">
                                            #{{ $ticket->id }}
                                        </td>
                                        <td style="font-weight: 500;">
                                            {{ $ticket->user->name ?? 'N/A' }}
                                            @if(!$ticket->has_admin_read)
                                                <span class="new-badge rounded-pill ms-2"><span class="pulse-dot"></span> New</span>
                                            @endif
                                        </td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>
                                            @php
                                                $ticketOwnerId = $ticket->inprogress_by ?: $ticket->closed_by;
                                                $canReopen = !$ticketOwnerId || $ticketOwnerId == Auth::id();
                                            @endphp
                                            <select
                                                class="status-select-badge @if($ticket->status == 'open') status-open @elseif($ticket->status == 'in progress') status-progress @else status-closed @endif"
                                                onchange="updateStatusLive({{ $ticket->id }}, this.value, this)">
                                                <option value="open" @if($ticket->status == 'open') selected @endif
                                                    @if(!$canReopen) disabled
                                                        title="Only {{ $ticket->inprogressBy->name ?? $ticket->closer->name ?? 'the assigned admin' }} can reopen this ticket"
                                                    @endif>
                                                    Open 🎟️
                                                </option>
                                                <option value="in progress" @if($ticket->status == 'in progress') selected @endif>
                                                    In Progress 👍🏻
                                                </option>
                                                <option value="closed" @if($ticket->status == 'closed') selected @endif>
                                                    Closed ✅️
                                                </option>
                                            </select>
                                        </td>
                                        <td class="text-muted" id="inprogress-{{ $ticket->id }}">
                                            {{ $ticket->inprogressBy->name ?? '---' }}
                                        </td>
                                        <td class="text-muted" id="closer-{{ $ticket->id }}">
                                            {{ $ticket->closer->name ?? '---' }}
                                        </td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <span style="color:var(--primary-color); flex-shrink:0;">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <polyline points="12 6 12 12 16 14" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <div
                                                        style="font-weight:600; color:#333; font-size:0.88rem; line-height:1.2;">
                                                        {{ $ticket->created_at->format('g:i A') }}
                                                    </div>
                                                    <div style="font-size:0.72rem; color:#aaa; margin-top:2px;">
                                                        {{ $ticket->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $msgCount = $ticket->unread_replies_count ?? 0;
                                            @endphp
                                            <a href="javascript:void(0)" onclick="openAdminChat({{ $ticket->id }})"
                                                class="action-btn-premium position-relative" title="Chat">
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="empty-state-row">
                                        <td colspan="8" class="p-0">
                                            <div class="empty-state-container text-center w-100"
                                                style="padding: 4rem 1rem; background: #fff;">
                                                <div
                                                    style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                    <div
                                                        style="font-size: 3.5rem; margin-bottom: 0.5rem; opacity: 0.6; filter: grayscale(0.2);">
                                                        📭</div>
                                                    <h5
                                                        style="font-family: 'Playfair Display', serif; color: #111; font-weight: 600; margin-bottom: 0.3rem;">
                                                        All caught up!</h5>
                                                    <p style="color: #777; font-size: 0.95rem; margin-bottom: 0;">There are no
                                                        tickets found here to display.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                @if($tickets->hasPages())
                    <div class="px-4 pb-4">
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <small class="text-muted">
                                Showing {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }}
                                tickets
                            </small>
                            {{ $tickets->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin.partials._chat')

    <script>
        const ADMIN_TICKETS_CONFIG = {
            highestTicketId: {{ $tickets->first()->id ?? 0 }},
            newDataUrl: '{{ route("admin.tickets.new-data") }}'
        };
    </script>
    <script src="{{ asset('js/admin-tickets-index.js') }}"></script>
@endsection