@extends('layouts.app')

@section('title', 'Support Messages')
@section('breadcrumb', 'Messages')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --chat-sidebar-width: 380px;
            --gold-primary: var(--primary-color);
            --bg-light: #f8f9fa;
            --card-bg: #ffffff;
            --border-soft: rgba(0, 0, 0, 0.06);
            --text-dark: #1a1a1a;
            --text-muted: #6c757d;
            --active-bg: var(--primary-light);
        }

        .messages-layout {
            display: flex;
            height: calc(100vh - 180px); /* Adjust based on navbar/footer height */
            min-height: 600px;
            background: var(--card-bg);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-soft);
            font-family: 'Outfit', sans-serif;
            margin-top: 1rem;
        }

        /* ── Sidebar ── */
        .chat-sidebar {
            width: var(--chat-sidebar-width);
            border-right: 1px solid var(--border-soft);
            display: flex;
            flex-direction: column;
            background: #fff;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-soft);
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .filter-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem;
        }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 4px;
            position: relative;
        }

        .chat-item:hover {
            background: var(--bg-light);
        }

        .chat-item.active {
            background: var(--active-bg);
        }

        .chat-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 4px;
            background: var(--gold-primary);
            border-radius: 0 4px 4px 0;
        }

        .chat-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--primary-light);
            color: var(--gold-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-info-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .chat-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .chat-preview {
            font-size: 0.85rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .unread-dot {
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-block;
            box-shadow: 0 0 0 2px #fff;
        }

        /* ── Main Area ── */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fcfcfc;
            position: relative;
        }

        .chat-placeholder {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
            color: var(--text-muted);
        }

        .placeholder-icon {
            width: 120px;
            height: 120px;
            background: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--gold-primary);
            font-size: 3rem;
        }

        .placeholder-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        #chatContentWrapper {
            flex: 1;
            display: none;
            height: 100%;
        }

        /* Override partial chat styles for static embedding */
        .messages-layout .chat-container.static-chat {
            border: none;
            box-shadow: none;
            border-radius: 0;
            height: 100%;
            max-height: none;
        }

        .messages-layout .chat-header {
            border-radius: 0;
            border-top: none;
            padding: 1.25rem 2rem;
            background: #fff;
        }

        .messages-layout .chat-footer {
            border-radius: 0;
            padding: 1.25rem 2rem;
            background: #fff;
        }

        /* Custom Scrollbar for Chat List */
        .chat-list::-webkit-scrollbar { width: 4px; }
        .chat-list::-webkit-scrollbar-track { background: transparent; }
        .chat-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
        .chat-list:hover::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); }

        /* Mobile Adjustments */
        @media (max-width: 991px) {
            .messages-layout {
                height: calc(100vh - 120px);
                margin-top: 0;
                border-radius: 0;
                border: none;
            }
            .chat-sidebar {
                width: 100%;
                display: block;
            }
            .chat-sidebar.hidden {
                display: none;
            }
            .chat-main {
                display: none;
            }
            .chat-main.active {
                display: flex;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 100;
            }
            .mobile-back-btn {
                display: flex !important;
            }
        }

        .mobile-back-btn {
            display: none;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            color: var(--text-dark);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            margin-right: 15px;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .chat-item {
            animation: slideIn 0.3s ease forwards;
        }
    </style>
@endpush

@section('content')
    <div class="premium-container container-fluid p-0 p-lg-3">
        <div class="d-none d-lg-flex flex-column mb-3">
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-dark);">Messages</h1>
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
                                <input
                                    type="date"
                                    name="date"
                                    id="customDateInput"
                                    class="form-control form-control-sm border-0 bg-light rounded-pill px-3"
                                    value="{{ $date }}"
                                    max="{{ date('Y-m-d') }}"
                                    onchange="this.form.submit()"
                                >
                            </div>
                            @if($date !== date('Y-m-d'))
                                <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-light rounded-pill" title="Today">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="chat-list" id="chatList">
                    @forelse ($messages as $message)
                        <div class="chat-item" data-ticket-id="{{ $message->ticket_id }}" onclick="selectChat({{ $message->ticket_id }}, this)">
                            <div class="chat-avatar">
                                @if($message->ticket->user && $message->ticket->user->avatar)
                                    <img src="{{ asset('storage/' . $message->ticket->user->avatar) }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
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
        }

        // Real-time updates for sidebar
        setInterval(async () => {
            try {
                const response = await fetch(`{{ route('admin.messages.new-data') }}?last_reply_id=${lastReplyId}`);
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
