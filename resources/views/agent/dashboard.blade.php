@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Agent-dashboard.css') }}">
@endpush

@section('content')
    <div class="container mt-4">
        <div class="row align-items-center mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0 text-center text-lg-start">
                <h2 style="font-family: 'Playfair Display', serif; font-weight: 600; color: #0a0a0a; margin-bottom: 4px;">
                    👋 Welcome, <span style="color: var(--primary-color);">{{ Auth::user()->name }}</span>
                </h2>
                <p class="d-flex align-items-center justify-content-center justify-content-lg-start text-center text-lg-start"
                    style="color: #666; margin-top: 15px; font-size: 0.95rem; gap: 12px; flex-wrap: wrap;">
                    <span
                        style="font-family: 'DM Sans', sans-serif; font-size: 0.68rem; color: #3b6fd4; font-weight: 800; letter-spacing: 1.2px; background: rgba(59, 111, 212, 0.07); padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(59, 111, 212, 0.12); box-shadow: 0 2px 5px rgba(59, 111, 212, 0.05); white-space: nowrap;">
                        <span style="margin-right: 4px;">👤</span> Agent
                    </span>
                    <span>Manage your support tickets and track their status below.</span>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('knowledge.base') }}"
                    style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; background: #fff; border: 1px solid var(--primary-light); border-radius: 14px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px var(--primary-light);">
                    <div
                        style="width: 40px; height: 40px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                    </div>
                    <div style="text-align: left;">
                        <div style="font-size: 0.75rem; color: #aaa; letter-spacing: 0.05em; font-weight: 700;">
                            Need Help?</div>
                        <div style="font-size: 0.95rem; color: #111; font-weight: 600;">Knowledge Base</div>
                    </div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)"
                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 10px;">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Open Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('agent.dashboard', ['status' => 'open', 'date' => $date]) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #dc3545; --icon-bg: rgba(220, 53, 69, 0.08);">
                        <div class="royal-card-watermark">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M2 9V5.2a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2V9a2 2 0 0 0 0 6v3.8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V15a2 2 0 0 0 0-6z" />
                                <path d="M14 3v2" />
                                <path d="M14 8v2" />
                                <path d="M14 13v2" />
                                <path d="M14 18v2" />
                            </svg>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Open Tickets</div>
                            <div class="royal-card-value" id="open-count">{{ $openTickets }}</div>
                            <div class="royal-card-sub">New today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- In Progress -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('agent.dashboard', ['status' => 'in progress', 'date' => $date]) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #d4af53; --icon-bg: rgba(212, 175, 83, 0.08);">
                        <div class="royal-card-watermark">
                            <span style="font-size: 55px;">👍🏻</span>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">In Progress</div>
                            <div class="royal-card-value" id="progress-count">{{ $inProgress }}</div>
                            <div class="royal-card-sub">Handled today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Closed Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('agent.dashboard', ['status' => 'closed', 'date' => $date]) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #198754; --icon-bg: rgba(25, 135, 84, 0.08);">
                        <div class="royal-card-watermark">
                            <span style="font-size: 55px;">✅️</span>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Closed Tickets</div>
                            <div class="royal-card-value" id="closed-count">{{ $closedTickets }}</div>
                            <div class="royal-card-sub">Resolved today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('agent.dashboard', ['date' => $date]) }}" style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #3b6fd4; --icon-bg: rgba(59, 111, 212, 0.08);">
                        <div class="royal-card-watermark">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                <path d="M12 11h4"></path>
                                <path d="M12 16h4"></path>
                                <path d="M8 11h.01"></path>
                                <path d="M8 16h.01"></path>
                            </svg>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Total Tickets</div>
                            <div class="royal-card-value" id="total-count">{{ $totalTickets }}</div>
                            <div class="royal-card-sub">Requests today</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card mt-4 shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white pt-4 px-3 border-bottom-0" style="padding-bottom: 20px;">
                <div style="border-left: 4px solid var(--primary-color); padding-left: 12px;">
                    <h5 class="m-0"
                        style="font-weight: 600; color: #111; font-family: 'Inter', sans-serif; font-size: 1.15rem; letter-spacing: -0.3px;">
                        My Tickets
                    </h5>
                </div>
            </div>

            <div class="card-body p-0">
                <form method="GET" action="{{ route('agent.dashboard') }}" id="filterForm"></form>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Closed By</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                            <tr style="background: var(--primary-light);">
                                <td style="padding: 10px 15px;">
                                    <input type="text" name="subject" id="filter_subject" form="filterForm"
                                        class="inline-filter-input search-icon-input" placeholder="Subject..."
                                        value="{{ request('subject') }}" oninput="debounceSubmit()">
                                </td>
                                <td style="padding: 10px 15px;">
                                    <select name="status" class="inline-filter-select" form="filterForm"
                                        onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Status</option>
                                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in progress" {{ request('status') == 'in progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed
                                        </option>
                                    </select>
                                </td>
                                <td style="padding: 10px 15px;">
                                    <input type="text" name="closer_name" id="filter_closer_name" form="filterForm"
                                        class="inline-filter-input search-icon-input" placeholder="Closed by..."
                                        value="{{ request('closer_name') }}" oninput="debounceSubmit()">
                                </td>
                                <td style="padding: 10px 15px;">
                                    <input type="date" name="date" class="inline-filter-input" value="{{ $date }}"
                                        form="filterForm" onchange="document.getElementById('filterForm').submit()">
                                </td>
                                <td class="text-center" style="padding: 10px 15px;">
                                    <a href="{{ route('agent.dashboard') }}" class="btn-clear-inline" title="Clear Filters">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="1 4 1 10 7 10"></polyline>
                                            <polyline points="23 20 23 14 17 14"></polyline>
                                            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15">
                                            </path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr data-ticket-id="{{ $ticket->id }}" style="cursor: pointer;">
                                    <td style="font-weight: 500;">{{ $ticket->subject }}</td>
                                    <td>
                                        <span class="badge"
                                            style="padding: 0.5rem 0.8rem; border-radius: 10px; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px;
                                                    @if ($ticket->status == 'open') background: rgba(220, 53, 69, 0.1); color: #dc3545;@elseif($ticket->status == 'in progress') background: rgba(212, 175, 83, 0.15); color: #d4af53;@else background: rgba(25, 135, 84, 0.1); color: #198754; @endif">
                                            {{ ucfirst($ticket->status) }}
                                            @if ($ticket->status == 'open')
                                                🎟️
                                            @elseif($ticket->status == 'in progress')
                                                👍🏻
                                            @else
                                                ✅️
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $ticket->closer->name ?? '---' }}</td>
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
                                                <div style="font-weight:600; color:#333; font-size:0.88rem; line-height:1.2;">
                                                    {{ $ticket->created_at->format('g:i A') }}
                                                </div>
                                                <div style="font-size:0.72rem; color:#aaa; margin-top:2px;">
                                                    {{ $ticket->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            @php
                                                $msgCount = $ticket->unread_replies_count ?? 0;
                                            @endphp
                                            <a href="javascript:void(0)" onclick="openAdminChat({{ $ticket->id }})"
                                                class="action-btn-premium" title="Chat">
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

                                            {{-- Direct Delete Button --}}
                                            <form method="POST" action="{{ route('agent.tickets.destroy', $ticket->id) }}"
                                                class="m-0" onsubmit="return confirm('Delete this ticket?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="action-btn-premium action-btn-danger"
                                                    title="Delete Ticket">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path
                                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                        </path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-state-row" style="display: none;"></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tickets->isEmpty())
                    <div class="empty-state-container text-center w-100"
                        style="padding: 4rem 1rem; background: #fff; border-radius: 0 0 16px 16px;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div style="font-size: 3.5rem; margin-bottom: 0.5rem; opacity: 0.6; filter: grayscale(0.2);">📭
                            </div>
                            <h5
                                style="font-family: 'Playfair Display', serif; color: #111; font-weight: 600; margin-bottom: 0.3rem;">
                                It's quiet here!</h5>
                            <p style="color: #777; font-size: 0.95rem; margin-bottom: 1.5rem;">You haven't submitted any tickets
                                matching this filter.</p>
                            <a href="{{ route('agent.tickets.create') }}" class="btn-create" style="text-decoration: none;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Create Your Ticket
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Pagination inside the card-body --}}
                @if ($tickets->hasPages())
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
        const DASHBOARD_CONFIG = {
            highestTicketId: {{ $tickets->first()->id ?? 0 }},
            newDataUrl: '{{ route("agent.dashboard.new-data") }}'
        };
    </script>
    <script src="{{ asset('js/user-dashboard.js') }}"></script>
@endsection