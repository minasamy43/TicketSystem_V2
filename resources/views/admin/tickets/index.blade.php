@extends('layouts.app')

@section('title', 'Tickets')
@section('breadcrumb', request('sender_type') === 'user' ? 'User Tickets' : 'Agent Tickets')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/Admin-tickets-index.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .flatpickr-day.has-unread-tickets {
            background: #fff5f5 !important;
            border-color: #ffcdd2 !important;
            color: #c62828 !important;
            font-weight: 700 !important;
        }
        .flatpickr-day.has-unread-tickets::after {
            content: '';
            position: absolute;
            bottom: 3px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: #c62828;
            border-radius: 50%;
        }
        .flatpickr-day.has-unread-tickets:hover {
            background: #feeaea !important;
        }

        /* ── Modern Premium Report Widget ── */
        .report-widget {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            padding: 14px 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.01);
            min-width: 320px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Glowing top border with primary color gradient */
        .report-widget::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color), white 40%) 100%);
        }
        .report-widget:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06), 0 2px 5px rgba(0, 0, 0, 0.03);
            transform: translateY(-2px);
        }
        .report-widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
        }
        .report-widget-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .report-widget-title svg {
            color: var(--primary-color);
        }
        .report-widget-title-badge {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--primary-color);
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: 0.02em;
        }
        .report-inputs-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .report-input-wrapper {
            position: relative;
            flex: 1;
        }
        .report-date-input {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 12px;
            font-size: 0.78rem;
            color: #334155;
            background: #f8fafc;
            outline: none;
            transition: all 0.25s ease;
            width: 100%;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
        }
        .report-date-input:focus {
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .report-arrow {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 700;
            user-select: none;
        }
        .report-actions {
            display: flex;
            gap: 8px;
        }
        .report-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            flex: 1;
        }
        .report-btn-pdf {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }
        .report-btn-pdf:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.25);
            background: linear-gradient(135deg, #f87171, #dc2626);
        }
        .report-btn-pdf:active {
            transform: translateY(1px);
        }
        .report-btn-excel {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
        }
        .report-btn-excel:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.25);
            background: linear-gradient(135deg, #34d399, #059669);
        }
        .report-btn-excel:active {
            transform: translateY(1px);
        }
        .report-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        .report-btn svg {
            transition: transform 0.2s ease;
        }
        .report-btn:hover svg {
            transform: translateY(-1px);
        }
    </style>
@endpush
 
@section('content')


    <div class="container mt-4">
        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between mb-4 gap-4">
            <div class="flex-grow-1">
                <h1 class="page-title mb-1">Tickets Management</h1>
                <p class="text-muted lead mb-0">Track and manage all customer support tickets across the platform.</p>
            </div>

            {{-- ── Report Widget ── --}}
            <div class="report-widget flex-shrink-0" style="min-width:unset;">
                {{-- Single row: label + date range + export icon buttons --}}
                <div class="report-inputs-container" style="margin-bottom:0;">
                    <span style="font-family:'Outfit',sans-serif;font-size:0.8rem;font-weight:700;color:#1a1a1a;text-transform:uppercase;letter-spacing:0.04em;white-space:nowrap;">Report</span>
                    <div class="report-input-wrapper">
                        <input type="text" id="report_date_from" class="report-date-input" value="{{ now()->format('Y-m-d') }}" placeholder="From Date" title="From date" readonly>
                    </div>
                    <span class="report-arrow">→</span>
                    <div class="report-input-wrapper">
                        <input type="text" id="report_date_to" class="report-date-input" value="{{ now()->format('Y-m-d') }}" placeholder="To Date" title="To date" readonly>
                    </div>
                    <button class="report-btn report-btn-pdf" id="adminReportPdf" title="Open PDF in browser" style="flex:0 0 auto;padding:8px 10px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </button>
                    <button class="report-btn report-btn-excel" id="adminReportExcel" title="Download Excel" style="flex:0 0 auto;padding:8px 10px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </button>
                </div>
            </div>
        </div>


        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body p-0">
                <form method="GET" action="{{ route('admin.tickets.index') }}" id="filterForm">
                    @if(request('sender_type'))
                        <input type="hidden" name="sender_type" value="{{ request('sender_type') }}">
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th width="80">Id</th>
                                    <th>Sender</th>
                                    <th>Category</th>
                                    <th width="200">Subject</th>
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
                                            class="inline-filter-input search-icon-input" placeholder="Sender..."
                                            value="{{ request('user_name') }}" oninput="debounceSubmit()">
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <select name="category" class="inline-filter-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">All </option>
                                            <option value="live Egypt" {{ request('category') == 'live Egypt' ? 'selected' : '' }}>live Egypt</option>
                                            <option value="live pro" {{ request('category') == 'live pro' ? 'selected' : '' }}>live pro</option>
                                            <option value="demo Egypt" {{ request('category') == 'demo Egypt' ? 'selected' : '' }}>demo Egypt</option>
                                            <option value="demo pro" {{ request('category') == 'demo pro' ? 'selected' : '' }}>demo pro</option>
                                            <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>other</option>
                                        </select>
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
                                        <input type="text" name="date" class="inline-filter-input date-picker-trigger" value="{{ $date }}" placeholder="Select Date...">
                                    </td>
                                    <td class="text-center" style="padding: 10px 15px;">
                                        <a href="{{ route('admin.tickets.index', ['sender_type' => request('sender_type')]) }}" class="btn-clear-inline"
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
                                            <div class="d-flex align-items-center gap-1">
                                                @if(($ticket->user->role ?? 2) == 0)
                                                    <i class="fa-solid fa-user-cog" style="font-size: 0.75rem; color: var(--primary-color);" title="Agent"></i>
                                                @else
                                                    <i class="fa-solid fa-users" style="font-size: 0.75rem; color: var(--primary-color);" title="User"></i>
                                                @endif
                                                
                                                <span class="ms-1">{{ $ticket->user->name ?? 'N/A' }}</span>

                                                @if(!$ticket->has_admin_read)
                                                    <span class="new-badge rounded-pill ms-2"><span class="pulse-dot"></span> New</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary" style="font-size: 0.8rem; padding: 0.4em 0.6em; border-radius: 6px; background-color: #f1f3f5 !important; color: #495057 !important; border: 1px solid #dee2e6;">
                                                {{ ucfirst($ticket->category ?? 'None') }}
                                            </span>
                                        </td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $ticket->subject }}">{{ $ticket->subject }}</td>
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
                                                {{-- Always render badge span so JS can update it without DOM insertion --}}
                                                @php $msgCount = $ticket->unread_replies_count ?? 0; @endphp
                                                <span id="unread-count-{{ $ticket->id }}"
                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm"
                                                    style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1; {{ $msgCount > 0 ? '' : 'display:none;' }}">
                                                    {{ $msgCount > 99 ? '99+' : ($msgCount ?: '') }}
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="empty-state-row">
                                        <td colspan="9" class="p-0">
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('js/report-export.js') }}"></script>
    <script>
        const ADMIN_TICKETS_CONFIG = {
            highestTicketId: {{ $tickets->first()->id ?? 0 }},
            newDataUrl: '{{ route("admin.tickets.new-data") }}',
            senderType: '{{ request("sender_type", "") }}',
            unreadDatesUrl: '{{ route("admin.tickets.unread-dates") }}',
            reportDataUrl: '{{ route("admin.tickets.report-data") }}',
        };

        document.addEventListener('DOMContentLoaded', async function() {
            // Fetch unread dates
            let unreadDates = [];
            try {
                const response = await fetch(`${ADMIN_TICKETS_CONFIG.unreadDatesUrl}?sender_type=${ADMIN_TICKETS_CONFIG.senderType}`);
                unreadDates = await response.json();
            } catch (e) { console.error('Failed to fetch unread dates:', e); }

            flatpickr('.date-picker-trigger', {
                dateFormat: 'Y-m-d',
                defaultDate: '{{ $date }}',
                onChange: function(selectedDates, dateStr) {
                    const form = document.getElementById('filterForm');
                    if (form) form.submit();
                },
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const date = dayElem.dateObj;
                    const dateString = date.getFullYear() + "-" + 
                                     ("0" + (date.getMonth() + 1)).slice(-2) + "-" + 
                                     ("0" + date.getDate()).slice(-2);
                    
                    if (unreadDates.includes(dateString)) {
                        dayElem.classList.add('has-unread-tickets');
                        dayElem.title = 'Has unread tickets';
                    }
                }
            });

            // Initialize flatpickr for report date inputs
            flatpickr('#report_date_from', {
                dateFormat: 'Y-m-d',
                defaultDate: '{{ now()->format("Y-m-d") }}'
            });
            flatpickr('#report_date_to', {
                dateFormat: 'Y-m-d',
                defaultDate: '{{ now()->format("Y-m-d") }}'
            });

            function setReportBtnsLoading(loading) {
                document.getElementById('adminReportPdf').disabled   = loading;
                document.getElementById('adminReportExcel').disabled = loading;
            }

            // ── PDF Export ──
            document.getElementById('adminReportPdf').addEventListener('click', async function() {
                const dateFrom = document.getElementById('report_date_from').value;
                const dateTo   = document.getElementById('report_date_to').value;
                if (!dateFrom || !dateTo) { alert('Please select both From and To dates.'); return; }
                if (dateFrom > dateTo) { alert('"From" date must be before or equal to "To" date.'); return; }
                const senderType = ADMIN_TICKETS_CONFIG.senderType;
                const url = `${ADMIN_TICKETS_CONFIG.reportDataUrl}?date_from=${dateFrom}&date_to=${dateTo}${senderType ? '&sender_type=' + senderType : ''}`;

                await ReportExportUtil.exportPdf({
                    title: 'Tickets Report',
                    fetchUrl: url,
                    isAdmin: true,
                    onStart: () => setReportBtnsLoading(true),
                    onEnd: () => setReportBtnsLoading(false)
                });
            });

            // ── Excel Export ──
            document.getElementById('adminReportExcel').addEventListener('click', async function() {
                const dateFrom = document.getElementById('report_date_from').value;
                const dateTo   = document.getElementById('report_date_to').value;
                if (!dateFrom || !dateTo) { alert('Please select both From and To dates.'); return; }
                if (dateFrom > dateTo) { alert('"From" date must be before or equal to "To" date.'); return; }
                const senderType = ADMIN_TICKETS_CONFIG.senderType;
                const url = `${ADMIN_TICKETS_CONFIG.reportDataUrl}?date_from=${dateFrom}&date_to=${dateTo}${senderType ? '&sender_type=' + senderType : ''}`;

                await ReportExportUtil.exportExcel({
                    title: 'Tickets Report',
                    fetchUrl: url,
                    isAdmin: true,
                    onStart: () => setReportBtnsLoading(true),
                    onEnd: () => setReportBtnsLoading(false)
                });
            });
        });
    </script>
    <script src="{{ asset('js/admin-tickets-index.js') }}"></script>
@endsection