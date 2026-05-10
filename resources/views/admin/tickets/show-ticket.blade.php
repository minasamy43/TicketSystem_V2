@extends('layouts.app')

@section('title', 'View Ticket')
@section('breadcrumb', 'View Ticket')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Admin-show-ticket.css') }}">
@endpush

@section('content')
    <div class="tk-wrap">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('admin.tickets.index') }}" class="btn-back-premium">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                 Back to Tickets
            </a>

            <div class="d-flex align-items-center gap-2 m-0">
                @php
                    $ticketOwnerId = $ticket->inprogress_by ?: $ticket->closed_by;
                    $canReopen = !$ticketOwnerId || $ticketOwnerId == Auth::id();
                @endphp
                <select name="status" class="status-select-header"
                    onchange="updateTicketStatusLive({{ $ticket->id }}, this.value, this)">
                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}
                        {{ !$canReopen ? 'disabled' : '' }}
                        {{ !$canReopen ? 'title="Only ' . ($ticket->inprogressBy->name ?? $ticket->closer->name ?? 'the assigned admin') . ' can reopen this ticket"' : '' }}>
                        Open 🎟️
                    </option>
                    <option value="in progress" {{ $ticket->status === 'in progress' ? 'selected' : '' }}>In Progress 👍🏻
                    </option>
                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed ✅️</option>
                </select>
            </div>
        </div>



        {{-- Main Section: Ticket Info --}}
        <div class="premium-card main-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="ticket-id-badge mb-3">TICKET #{{ $ticket->id }}</span>
                    <h1 class="ticket-title">{{ $ticket->subject }}</h1>
                </div>
                <div>
                    @if($ticket->status === 'open')
                        <span class="status-pill-premium status-open-prem" id="mainStatusPill">
                            <span class="pulse-dot-prem"></span> Open
                        </span>
                    @elseif($ticket->status === 'in progress')
                        <span class="status-pill-premium status-progress-prem" id="mainStatusPill">👍🏻 In Progress</span>
                    @else
                        <span class="status-pill-premium status-closed-prem" id="mainStatusPill">✅️ Closed</span>
                    @endif
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-item-premium">
                    <span class="meta-label-premium">Submitted On</span>
                    <span class="meta-value-premium">
                        <span class="meta-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg></span>
                        {{ $ticket->created_at->format('M d, Y') }} at {{ $ticket->created_at->format('g:i A') }}
                    </span>
                </div>
                <div class="meta-item-premium">
                    <span class="meta-label-premium">Customer Name</span>
                    <span class="meta-value-premium">
                        <span class="meta-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg></span>
                        {{ $ticket->user->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="meta-item-premium">
                    <span class="meta-label-premium">Email Address</span>
                    <span class="meta-value-premium">
                        <span class="meta-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg></span>
                        {{ $ticket->user->email ?? 'N/A' }}
                    </span>
                </div>
                <div class="meta-item-premium">
                    <span class="meta-label-premium">Message Status</span>
                    <span class="meta-value-premium">
                        <span class="meta-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <path
                                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                            </svg></span>
                        @php $uCount = $ticket->unread_replies_count ?? 0; @endphp
                        <span>{{ $uCount > 0 ? 'New Customer Reply' : 'All Read' }}</span>
                        @if($uCount > 0)
                            <span id="unread-count-{{ $ticket->id }}" class="badge rounded-pill bg-danger ms-1"
                                style="font-size: 0.66rem; padding: 0.2em 0.35em; vertical-align: middle; line-height: 1.5;">
                                {{ $uCount }} new
                            </span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="orig-request-box">
                <div class="orig-request-label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    Original Message
                </div>
                <div class="orig-request-text">{{ $ticket->message }}</div>
            </div>

            @if($ticket->images && count($ticket->images) > 0)
                <h5 class="mt-5 mb-4"
                    style="font-family:'Playfair Display',serif; font-size: 1.5rem; font-weight:700; color:#111; display:flex; align-items:center; gap:10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2.5">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    Attachments
                </h5>
                <div class="row g-4">
                    @foreach($ticket->images as $img)
                        <div class="col-md-4">
                            <img src="{{ asset('storage/' . $img) }}" alt="Attachment" onclick="openGlobalLightbox(this.src)"
                                class="tk-attachment-img">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Messenger Chat Component --}}
    @include('admin.partials._chat', ['ticket' => $ticket, 'isStatic' => false, 'withTrigger' => true])

    <script>
        // Live Ticket Auto-updater
        async function updateTicketStatusLive(ticketId, newStatus, selectElement) {
            // Store previous value in case we need to revert
            const previousValue = Array.from(selectElement.options).find(o => o.defaultSelected)?.value
                || Array.from(selectElement.options).find(o => o.selected)?.value;

            selectElement.style.opacity = '0.5';
            selectElement.disabled = true;
            try {
                const response = await fetch(`/admin/tickets/${ticketId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    // Mark the new value as the default (so future reverts use this)
                    Array.from(selectElement.options).forEach(o => o.defaultSelected = (o.value === newStatus));

                    // Update the pill badge in the header dynamically
                    const mainPill = document.getElementById('mainStatusPill');
                    if (mainPill) {
                        mainPill.className = 'status-pill-premium'; // reset
                        if (newStatus === 'open') {
                            mainPill.classList.add('status-open-prem');
                            mainPill.innerHTML = '<span class="pulse-dot-prem"></span> Open';
                        } else if (newStatus === 'in progress') {
                            mainPill.classList.add('status-progress-prem');
                            mainPill.innerHTML = '👍🏻 In Progress';
                        } else if (newStatus === 'closed') {
                            mainPill.classList.add('status-closed-prem');
                            mainPill.innerHTML = '✅️ Closed';
                        }
                    }

                    // Update the chat popup header badge inside partials._chat
                    if (window.updateChatStatusBadge) {
                        window.updateChatStatusBadge(newStatus);
                    }
                } else {
                    // Revert the select back to the previous value
                    selectElement.value = previousValue;
                    alert('⚠️ ' + data.message);
                }
            } catch (error) {
                // Revert on network error too
                selectElement.value = previousValue;
                console.error('Status update failed:', error);
                alert('Connection error. Please try again.');
            } finally {
                selectElement.style.opacity = '1';
                selectElement.disabled = false;
            }
        }
    </script>
@endsection