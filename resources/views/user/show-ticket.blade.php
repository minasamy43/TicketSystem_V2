@extends('layouts.app')

@section('title', 'View Ticket')
@section('breadcrumb', 'View Ticket')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/User-show-ticket.css') }}">
@endpush

@section('content')
    <div class="tk-wrap">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('user.dashboard') }}" class="btn-back-premium">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Dashboard
            </a>

            @if($ticket->status !== 'closed')
                <form method="POST" action="{{ route('tickets.close', $ticket->id) }}"
                    onsubmit="return confirm('Close this ticket?');" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-close-ticket-prem">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            style="margin-right: 4px;">
                            <path d="M18 6L6 18M6 6l12 12"></path>
                        </svg>
                        Close Ticket
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
                style="background: #e8f5e9; border: 1px solid #c8e6c9; color: #2e7d32; border-radius: 12px;">
                <div class="d-flex align-items-center gap-2">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <strong>{{ session('success') }}</strong>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="opacity: 0.6;"></button>
            </div>
        @endif

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

            </div>

            <div class="orig-request-box">
                <div class="orig-request-label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    My Ticket Details
                </div>
                <div class="orig-request-text">{{ $ticket->message }}</div>
            </div>

            @if($ticket->images && count($ticket->images) > 0)
                <h5 class="mt-5 mb-4"
                    style="font-family:'Playfair Display',serif; font-size: 1.5rem; font-weight:700; color:#111; display:flex; align-items:center; gap:10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af53" stroke-width="2.5">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    Attached Files
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
@endsection