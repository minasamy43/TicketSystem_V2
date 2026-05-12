@extends('layouts.app')

@section('title', 'Messages')
@section('breadcrumb', 'Messages')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin-messages-index.css') }}">
@endpush

@section('content')
    <div class="premium-container container-fluid p-0 p-lg-3">
        <div class="d-none d-lg-flex flex-column mb-3">
            <h1 class="page-title mb-1">Messages</h1>
            <p class="text-muted small mb-0">Manage all customer support conversations in one place.</p>
        </div>

        <div class="messages-layout">
            <!-- ── Sidebar ── -->
            <div class="chat-sidebar" id="messagesSidebar">
                <div class="sidebar-header">
                    <div class="sidebar-title d-lg-none">Messages</div>
                    <form method="GET" action="{{ route('admin.messages.index') }}" id="dateFilterForm">
                        <div class="filter-container">
                            <input type="hidden" name="filter" value="custom">
                            <div class="position-relative flex-grow-1">
                                <input type="date" name="date" id="customDateInput"
                                    class="form-control form-control-sm border-0 bg-light rounded-pill px-3"
                                    value="{{ $date }}" max="{{ date('Y-m-d') }}" onchange="this.form.submit()">
                            </div>
                            @if($date !== date('Y-m-d'))
                                <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-light rounded-pill"
                                    title="Today">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="chat-list" id="chatList">
                    @forelse ($messages as $message)
                        <div class="chat-item" data-ticket-id="{{ $message->ticket_id }}"
                            onclick="selectChat({{ $message->ticket_id }}, this)">
                            <div class="chat-avatar">
                                @if($message->ticket->user && $message->ticket->user->avatar)
                                    <img src="{{ asset('storage/' . $message->ticket->user->avatar) }}" alt=""
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr($message->ticket->user->name ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <div class="chat-info">
                                <div class="chat-info-top">
                                    <span class="chat-name">{{ $message->ticket->user->name ?? 'Unknown User' }}</span>
                                    <span class="chat-time">{{ $message->created_at->diffForHumans(null, true) }}</span>
                                </div>
                                <div class="chat-preview">
                                    @if(!$message->is_read && !$message->isFromAdmin())
                                        <span class="unread-dot"></span>
                                    @endif
                                    @if($message->image)
                                        <i class="fa-solid fa-image text-muted me-1" style="font-size: 0.75rem;"></i>
                                    @endif
                                    {{ $message->body }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small">
                            No messages found for this date.
                        </div>
                    @endforelse
                </div>

                <div class="p-3 border-top d-none d-lg-block">
                    <div class="small text-muted text-center">
                        Showing {{ $messages->count() }} threads
                    </div>
                </div>
            </div>

            <!-- ── Main Chat Area ── -->
            <div class="chat-main" id="messagesMain">
                <!-- Mobile Header (Back Button) -->
                <div class="chat-header d-lg-none bg-white border-bottom py-3 px-3">
                    <button class="mobile-back-btn" onclick="closeMobileChat()">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <div id="mobileChatHeaderTitle" class="fw-bold text-truncate"></div>
                </div>

                <div class="chat-placeholder" id="chatPlaceholder">
                    <div class="placeholder-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="placeholder-title">Your Conversations</div>
                    <p>Select a message from the list to view the full conversation thread and reply to the user.</p>
                </div>

                <div id="chatContentWrapper">
                    @include('admin.partials._chat', ['isStatic' => true, 'withTrigger' => false])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentActiveTicketId = null;
        let lastReplyId = {{ $messages->first()->id ?? 0 }};
        const chatList = document.getElementById('chatList');
        const chatPlaceholder = document.getElementById('chatPlaceholder');
        const chatContentWrapper = document.getElementById('chatContentWrapper');
        const sidebar = document.getElementById('messagesSidebar');
        const mainArea = document.getElementById('messagesMain');

        function selectChat(ticketId, element) {
            // Remove active class from all
            document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
            // Add to clicked
            element.classList.add('active');

            currentActiveTicketId = ticketId;

            // Show content area
            chatPlaceholder.style.display = 'none';
            chatContentWrapper.style.display = 'block';

            // Mobile handling
            if (window.innerWidth < 992) {
                sidebar.classList.add('hidden');
                mainArea.classList.add('active');
                document.getElementById('mobileChatHeaderTitle').textContent = element.querySelector('.chat-name').textContent;
            }

            // Call the global openAdminChat from partials
            if (typeof openAdminChat === 'function') {
                openAdminChat(ticketId);
            }

            // Mark as read visually in sidebar
            const dot = element.querySelector('.unread-dot');
            if (dot) dot.remove();
        }

        function closeMobileChat() {
            sidebar.classList.remove('hidden');
            mainArea.classList.remove('active');
            currentActiveTicketId = null;
            // Remove active class from all items when returning to the list
            document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
        }

        // Real-time updates for sidebar
        setInterval(async () => {
            try {
                const response = await fetch(`{{ route('admin.messages.new-data') }}?last_reply_id=${lastReplyId}&filter={{ $filter }}&date={{ $date }}`);
                const data = await response.json();

                if (data.success && data.new_messages.length > 0) {
                    data.new_messages.forEach(msg => {
                        let item = document.querySelector(`.chat-item[data-ticket-id="${msg.ticket_id}"]`);

                        if (item) {
                            // Update existing item and move to top
                            item.querySelector('.chat-preview').innerHTML = `
                                    ${(!msg.is_read && !msg.is_from_admin) ? '<span class="unread-dot"></span>' : ''}
                                    ${msg.image ? '<i class="fa-solid fa-image text-muted me-1" style="font-size: 0.75rem;"></i>' : ''}
                                    ${msg.body}
                                `;
                            item.querySelector('.chat-time').textContent = msg.relative_time;

                            // If it's active, it's already read (or being read)
                            if (currentActiveTicketId == msg.ticket_id) {
                                const dot = item.querySelector('.unread-dot');
                                if (dot) dot.remove();
                            }

                            chatList.prepend(item);
                        } else {
                            // Prepend new item
                            const newItem = document.createElement('div');
                            newItem.className = 'chat-item';
                            newItem.setAttribute('data-ticket-id', msg.ticket_id);
                            newItem.onclick = () => selectChat(msg.ticket_id, newItem);

                            newItem.innerHTML = `
                                    <div class="chat-avatar">
                                        ${msg.user_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="chat-info">
                                        <div class="chat-info-top">
                                            <span class="chat-name">${msg.user_name}</span>
                                            <span class="chat-time">${msg.relative_time}</span>
                                        </div>
                                        <div class="chat-preview">
                                            ${(!msg.is_read && !msg.is_from_admin) ? '<span class="unread-dot"></span>' : ''}
                                            ${msg.image ? '<i class="fa-solid fa-image text-muted me-1" style="font-size: 0.75rem;"></i>' : ''}
                                            ${msg.body}
                                        </div>
                                    </div>
                                `;

                            chatList.prepend(newItem);
                        }
                    });

                    lastReplyId = data.new_highest_id;
                }
            } catch (error) {
                console.error('Error fetching new sidebar data:', error);
            }
        }, 10000);
    </script>
@endpush