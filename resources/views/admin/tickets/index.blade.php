@extends('layouts.app')

@section('title', 'Tickets Management')
@section('breadcrumb', 'Tickets')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
@endpush

@section('content')

    <style>
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .new-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25em 0.6em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.03em;
            vertical-align: middle;
        }

        .pulse-dot {
            width: 6px;
            height: 6px;
            background-color: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .unread-row {
            background: linear-gradient(90deg, rgba(220, 53, 69, 0.04) 0%, transparent 30%);
        }

        .unread-row td {
            font-weight: 600 !important;
        }

        .unread-row td:not(:first-child) {
            color: #111 !important;
        }

        .new-entry-flash {
            animation: flashRow 3s ease-out;
        }

        @keyframes flashRow {
            0% {
            background-color: var(--primary-light);
            }

            100% {
                background-color: transparent;
            }
        }

        /* Standard Table Overrides to match User View */
        .table-bordered {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e8eaed !important;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.02);
        }

        .table-bordered thead th {
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
            font-size: 0.88rem;
            padding: 10px 12px;
            border-bottom: 2px solid #e8eaed !important;
        }

        .table-bordered tbody td {
            padding: 10px 12px;
            vertical-align: middle;
            font-size: 0.9rem;
            border-color: #edebe8ff !important;
        }

        .table-bordered tbody tr:not(.empty-state-row):hover td {
            background: rgba(0, 0, 0, 0.02) !important;
        }

        .action-btn-premium {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #eee;
            color: #d4af53;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .action-btn-premium:hover {
            color: #fff;
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--primary-light);
        }

        /* Inline Filters */
        .inline-filter-select,
        .inline-filter-input {
            background: #fcfcfc;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            font-size: 0.75rem;
            padding: 4px 8px;
            color: #555;
            width: 100%;
            transition: all 0.2s ease;
            font-family: 'DM Sans', sans-serif;
        }

        .inline-filter-select:focus,
        .inline-filter-input:focus {
            border-color: #d4af53;
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .btn-clear-inline {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #eee;
            color: #666;
            transition: all 0.3s ease;
        }

        .btn-clear-inline:hover {
            background: #f8f9fa;
            color: #dc3545;
            border-color: #ffcccc;
        }

        /* Live Status Select Badge */
        .status-select-badge {
            border: none;
            border-radius: 10px;
            padding: 0.5rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            text-align: center;
            width: auto;
            min-width: 90px;
        }

        .status-select-badge:focus {
            box-shadow: 0 0 0 3px rgba(212, 175, 83, 0.2);
        }

        .status-select-badge option {
            background: #ffffff !important;
            color: #333333 !important;
        }

        .status-open {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
        }

        .status-progress {
            background: rgba(212, 175, 83, 0.15) !important;
            color: #d4af53 !important;
        }

        .status-closed {
            background: rgba(25, 135, 84, 0.1) !important;
            color: #198754 !important;
        }

        .search-icon-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 10px center;
            padding-left: 30px !important;
        }

        .search-icon-input::placeholder {
            color: transparent;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {

            .table-bordered thead th,
            .table-bordered tbody td {
                padding: 10px;
                font-size: 0.8rem;
            }

            .status-select-badge {
                min-width: 80px;
                padding: 0.4rem 0.6rem;
                font-size: 0.7rem;
            }

            .action-btn-premium {
                width: 32px;
                height: 32px;
            }
        }
    </style>

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
                                        <td style="font-weight: 600; color: #d4af53;">
                                            #{{ $ticket->id }}
                                        </td>
                                        <td style="font-weight: 500;">
                                            {{ $ticket->user->name ?? 'N/A' }}
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
        // Automatic Filtering Logic
        let timeout = null;
        function debounceSubmit() {
            clearTimeout(timeout);
            if (document.activeElement && document.activeElement.id) {
                sessionStorage.setItem('lastFocusedFilter', document.activeElement.id);
            }
            timeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 600);
        }

        window.onload = function () {
            const lastFocusedId = sessionStorage.getItem('lastFocusedFilter');
            if (lastFocusedId) {
                const element = document.getElementById(lastFocusedId);
                if (element) {
                    element.focus();
                    const val = element.value;
                    element.value = '';
                    element.value = val;
                }
                sessionStorage.removeItem('lastFocusedFilter');
            }
        };

        document.querySelectorAll('table tbody tr:not(.empty-state-row)').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function (e) {
                const isInteractive = e.target.closest('a') ||
                    e.target.closest('button') ||
                    e.target.closest('input') ||
                    e.target.closest('select') ||
                    e.target.closest('.status-select-badge');

                if (isInteractive) return;

                const ticketId = this.getAttribute('data-ticket-id');
                if (ticketId) {
                    window.location.href = `/admin/tickets/${ticketId}`;
                }
            });
        });

        async function updateStatusLive(ticketId, newStatus, selectElement) {
            const previousValue = Array.from(selectElement.options).find(o => o.defaultSelected)?.value
                || Array.from(selectElement.options).find(o => o.selected)?.value;

            selectElement.style.opacity = '0.5';
            selectElement.disabled = true;

            try {
                const response = await fetch(`/admin/tickets/${ticketId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    selectElement.classList.remove('status-open', 'status-progress', 'status-closed');
                    if (newStatus === 'open') selectElement.classList.add('status-open');
                    else if (newStatus === 'in progress') selectElement.classList.add('status-progress');
                    else if (newStatus === 'closed') selectElement.classList.add('status-closed');

                    Array.from(selectElement.options).forEach(o => o.defaultSelected = (o.value === newStatus));

                    const inprogressCell = document.getElementById(`inprogress-${ticketId}`);
                    if (inprogressCell && data.inprogress_by !== undefined) inprogressCell.textContent = data.inprogress_by;

                    const closerCell = document.getElementById(`closer-${ticketId}`);
                    if (closerCell && data.closer !== undefined) closerCell.textContent = data.closer;
                } else {
                    selectElement.value = previousValue;
                    alert('⚠️ ' + data.message);
                }
            } catch (error) {
                selectElement.value = previousValue;
                console.error('Status update failed:', error);
                alert('Connection error. Please try again.');
            } finally {
                selectElement.style.opacity = '1';
                selectElement.disabled = false;
            }
        }

        let highestTicketId = {{ $tickets->first()->id ?? 0 }};
        setInterval(async () => {
            const date = document.querySelector('input[name="date"]').value;
            const status = document.querySelector('select[name="status"]').value;
            const subject = document.getElementById('filter_subject').value;
            const user_name = document.getElementById('filter_user_name').value;
            const inprogress_name = document.getElementById('filter_inprogress_name').value;
            const closer_name = document.getElementById('filter_closer_name').value;

            try {
                const url = new URL('{{ route("admin.tickets.new-data") }}', window.location.origin);
                const ticket_id = document.getElementById('filter_ticket_id').value;
                url.searchParams.set('last_id', highestTicketId);
                url.searchParams.set('date', date);
                if (ticket_id) url.searchParams.set('ticket_id', ticket_id);
                if (status) url.searchParams.set('status', status);
                if (subject) url.searchParams.set('subject', subject);
                if (user_name) url.searchParams.set('user_name', user_name);
                if (inprogress_name) url.searchParams.set('inprogress_name', inprogress_name);
                if (closer_name) url.searchParams.set('closer_name', closer_name);

                const response = await fetch(url);
                const data = await response.json();

                if (data.success && data.new_tickets.length > 0) {
                    const tbody = document.querySelector('table tbody');
                    data.new_tickets.forEach(ticket => {
                        const existingRow = document.querySelector(`tr[data-ticket-id="${ticket.id}"]`);
                        if (!existingRow) {
                            const newRow = document.createElement('tr');
                            newRow.setAttribute('data-ticket-id', ticket.id);
                            newRow.className = 'new-entry-flash unread-row';
                            newRow.style.cursor = 'pointer';

                            newRow.innerHTML = `
                                    <td style="font-weight: 600; color: #d4af53;">#${ticket.id}</td>
                                    <td style="font-weight: 500;">${ticket.user_name}</td>
                                    <td>${ticket.subject}</td>
                                    <td>
                                        <select class="status-select-badge status-${ticket.status === 'in progress' ? 'progress' : ticket.status}" onchange="updateStatusLive(${ticket.id}, this.value, this)">
                                            <option value="open" ${ticket.status === 'open' ? 'selected' : ''}>Open 🎟️</option>
                                            <option value="in progress" ${ticket.status === 'in progress' ? 'selected' : ''}>In Progress 👍🏻</option>
                                            <option value="closed" ${ticket.status === 'closed' ? 'selected' : ''}>Closed ✅️</option>
                                        </select>
                                    </td>
                                    <td class="text-muted" id="inprogress-${ticket.id}">${ticket.inprogress_by}</td>
                                    <td class="text-muted" id="closer-${ticket.id}">${ticket.closer}</td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="color:#d4af53; flex-shrink:0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg></span>
                                            <div>
                                                <div style="font-weight:600; color:#333; font-size:0.88rem; line-height:1.2;">${ticket.time}</div>
                                                <div style="font-size:0.72rem; color:#aaa; margin-top:2px;">${ticket.relative_time}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="action-btn-premium position-relative" title="Chat">
                                            <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="messenger-grad" x1="0" y1="1" x2="1" y2="0"><stop offset="0%" stop-color="#00C6FF" /><stop offset="50%" stop-color="#0078FF" /><stop offset="100%" stop-color="#A033FF" /></linearGradient></defs><path fill="url(#messenger-grad)" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" /></svg>
                                            ${ticket.unread_count > 0 ? `<span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">${ticket.unread_count}</span>` : ''}
                                        </a>
                                    </td>
                                `;

                            newRow.addEventListener('click', function (e) {
                                if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
                                window.location.href = `/admin/tickets/${ticket.id}`;
                            });

                            const emptyRow = tbody.querySelector('.empty-state-row');
                            if (emptyRow) emptyRow.remove();
                            const emptyContainer = document.querySelector('.empty-state-container');
                            if (emptyContainer) emptyContainer.closest('tr').remove();

                            tbody.insertBefore(newRow, tbody.firstChild);
                        }
                    });
                    highestTicketId = data.new_highest_id;
                }
            } catch (error) {
                console.error('Error fetching new tickets:', error);
            }
        }, 10000);
    </script>
@endsection