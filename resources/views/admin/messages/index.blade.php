@extends('layouts.app')

@section('title', 'Support Messages')
@section('breadcrumb', 'Messages')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: var(--primary-color);
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
            cursor: pointer;
        }
        
        .table-premium tbody tr:hover {
            box-shadow: 0 5px 15px var(--primary-light);
            transform: translateY(-2px);
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

        .message-preview {
            max-width: 400px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #555;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Redesigned unread badge */
        .msg-unread-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.25);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2em 0.65em;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 8px;
            letter-spacing: 0.04em;
            vertical-align: middle;
            white-space: nowrap;
        }

        .msg-pulse-dot {
            width: 5px;
            height: 5px;
            background-color: #dc3545;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            animation: msgPulse 1.5s infinite;
        }

        @keyframes msgPulse {
            0%   { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70%  { box-shadow: 0 0 0 5px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        /* Time column */
        .msg-time-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .msg-time-icon { color: var(--primary-color); flex-shrink: 0; }
        .msg-time-main { font-weight: 600; color: #333; font-size: 0.88rem; line-height: 1.2; }
        .msg-time-relative { font-size: 0.72rem; color: #aaa; margin-top: 2px; }

        /* ── Filter Bar ── */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.6rem;
            background: var(--primary-light);
            border: 1px solid var(--primary-light);
            border-radius: 14px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .filter-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-right: 0.25rem;
            white-space: nowrap;
        }

        .date-input {
            border: 1.5px solid var(--primary-light);
            border-radius: 50px;
            padding: 0.32rem 1rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
            font-family: 'Outfit', sans-serif;
            background: #fff;
        }

        .date-input:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .date-btn {
            padding: 0.32rem 1rem;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            background: var(--gold-primary);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: opacity 0.18s;
            font-family: 'Outfit', sans-serif;
        }

        .date-btn:hover { opacity: 0.85; }

        .clear-link {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.32rem 0.6rem;
            border-radius: 50px;
            transition: color 0.15s;
        }
        .clear-link:hover { color: #dc3545; text-decoration: none; }

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

            .table-premium tbody td::before {
                content: attr(data-label) " : ";
                font-weight: 700;
                font-size: 0.75rem;
                color: var(--text-muted);
                margin-right: 15px;
                text-align: left;
            }

            .table-premium tbody td:last-child {
                border-bottom: 1px solid rgba(0, 0, 0, 0.04);
                justify-content: flex-end;
            }

            .table-premium tbody td:last-child::before {
                display: none;
            }
        }
        
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
@endpush

@section('content')
    <div class="premium-container container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-end mb-4 gap-3">
            <div class="text-center text-md-start">
                <h1 class="page-title mb-1">Messages</h1>
                <p class="text-muted lead mb-0" style="font-size: 1rem;">All ticket messages and conversation threads.</p>
            </div>
        </div>

        {{-- ── Date Filter Bar ── --}}
        <div class="filter-bar">
            <span class="filter-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:middle">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Filter by Date
            </span>

            <form method="GET" action="{{ route('admin.messages.index') }}" class="d-flex align-items-center gap-2" id="dateFilterForm">
                <input type="hidden" name="filter" value="custom">
                <input
                    type="date"
                    name="date"
                    id="customDateInput"
                    class="date-input"
                    value="{{ $date }}"
                    max="{{ date('Y-m-d') }}"
                    title="Pick a date"
                >
                <button type="submit" class="date-btn">Go</button>
            </form>

            @if($date !== date('Y-m-d'))
                <a href="{{ route('admin.messages.index') }}" class="clear-link">↩ Today</a>
            @endif
        </div>

        <div class="premium-card">
            <div class="table-responsive" style="overflow: visible;">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Message Preview</th>
                            <th>Ticket ID</th>
                            <th>Time</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>        
                    <tbody>
                        @forelse ($messages as $message)
                            <tr data-ticket-id="{{ $message->ticket_id }}" style="animation: slideUp {{ 0.3 + ($loop->index * 0.05) }}s ease forwards; cursor:pointer;" onclick="openAdminChat({{ $message->ticket_id }})">
                                <td data-label="Sender">
                                    <div class="d-flex align-items-center">
                                        @if(!$message->is_read && !$message->isFromAdmin())
                                            <span class="msg-unread-badge">
                                                <span class="msg-pulse-dot"></span>
                                                New
                                            </span>
                                        @endif
                                        <div class="fw-bold">
                                            {{ $message->ticket->user->name ?? 'Unknown User' }}
                                        </div>
                                    </div>
                                </td>

                                
                                <td data-label="Message">
                                    <div class="message-preview">
                                        @if($message->image)
                                            <i class="fa-solid fa-image text-muted me-1"></i> [Image Attachment] 
                                        @endif
                                        {{ $message->body }}
                                    </div>
                                </td>
                                <td>
                                    {{ $message->ticket_id }}
                                </td>
                                <td data-label="Time">
                                    <div class="msg-time-wrap">
                                        <span class="msg-time-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <div class="msg-time-relative">{{ $message->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Action" class="text-end">
                                    <button type="button" onclick="event.stopPropagation(); openAdminChat({{ $message->ticket_id }})" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                        View Chat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">No messages found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('admin.partials._chat', ['isStatic' => false, 'withTrigger' => false])
    <script>
        let lastReplyId = {{ $messages->first()->id ?? 0 }};
        
        setInterval(async () => {
            try {
                const response = await fetch(`{{ route('admin.messages.new-data') }}?last_reply_id=${lastReplyId}`);
                const data = await response.json();
                
                if (data.success && data.new_messages.length > 0) {
                    const tbody = document.querySelector('.table-premium tbody');
                    
                    data.new_messages.forEach(msg => {
                        let row = document.querySelector(`tr[data-ticket-id="${msg.ticket_id}"]`);
                        
                        if (row) {
                            // Update existing row and move to top
                            row.style.animation = 'none';
                            row.offsetHeight; // trigger reflow
                            row.style.animation = 'slideUp 0.5s ease forwards';
                            
                            // Update unread badge
                            const senderCell = row.querySelector('td[data-label="Sender"] .d-flex');
                            let badge = senderCell.querySelector('.msg-unread-badge');
                            if (!msg.is_read && !msg.is_from_admin) {
                                if (!badge) {
                                    senderCell.insertAdjacentHTML('afterbegin', `
                                        <span class="msg-unread-badge">
                                            <span class="msg-pulse-dot"></span>
                                            New
                                        </span>
                                    `);
                                }
                            } else if (badge) {
                                badge.remove();
                            }
                            
                            // Update preview
                            const preview = row.querySelector('.message-preview');
                            preview.innerHTML = `${msg.image ? '<i class="fa-solid fa-image text-muted me-1"></i> [Image Attachment] ' : ''}${msg.body}`;
                            
                            // Update time
                            row.querySelector('.msg-time-relative').textContent = msg.relative_time;
                            
                            // Move to top
                            tbody.prepend(row);
                        } else {
                            // Prepend new row
                            const newRow = document.createElement('tr');
                            newRow.setAttribute('data-ticket-id', msg.ticket_id);
                            newRow.style.cursor = 'pointer';
                            newRow.style.animation = 'slideUp 0.5s ease forwards';
                            newRow.onclick = () => openAdminChat(msg.ticket_id);
                            
                            newRow.innerHTML = `
                                <td data-label="Sender">
                                    <div class="d-flex align-items-center">
                                        ${(!msg.is_read && !msg.is_from_admin) ? `
                                            <span class="msg-unread-badge">
                                                <span class="msg-pulse-dot"></span>
                                                New
                                            </span>
                                        ` : ''}
                                        <div class="fw-bold">${msg.user_name}</div>
                                    </div>
                                </td>
                                <td data-label="Message">
                                    <div class="message-preview">
                                        ${msg.image ? '<i class="fa-solid fa-image text-muted me-1"></i> [Image Attachment] ' : ''}
                                        ${msg.body}
                                    </div>
                                </td>
                                <td>${msg.ticket_id}</td>
                                <td data-label="Time">
                                    <div class="msg-time-wrap">
                                        <span class="msg-time-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <div class="msg-time-relative">${msg.relative_time}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Action" class="text-end">
                                    <button type="button" onclick="event.stopPropagation(); openAdminChat(${msg.ticket_id})" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                        View Chat
                                    </button>
                                </td>
                            `;
                            
                            const emptyRow = tbody.querySelector('tr td.text-center.py-5');
                            if (emptyRow) emptyRow.closest('tr').remove();
                            
                            tbody.prepend(newRow);
                        }
                    });
                    
                    lastReplyId = data.new_highest_id;
                }
            } catch (error) {
                console.error('Error fetching new messages:', error);
            }
        }, 10000);
    </script>
@endpush
