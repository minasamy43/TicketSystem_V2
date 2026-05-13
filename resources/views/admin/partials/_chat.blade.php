<style>
    /* Chat Container - can be static or floating */
    .chat-container {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #e8eaed;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Floating mode styles */
    .chat-container.floating-chat {
        position: fixed;
        bottom: 0px;
        right: 0px;
        width: 380px;
        height: 550px;
        max-height: calc(100vh - 40px);
        z-index: 9999;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
        transform: translateY(100%);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .chat-container.floating-chat.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    /* Floating Messenger Button Toggle */
    .chat-floating-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        z-index: 998;
    }

    .chat-floating-btn:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }

    .chat-badge-bubble {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: #fff;
        font-size: 0.80rem;
        font-weight: 700;
        padding: 2px 5px;
        border-radius: 20px;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        line-height: 1;
    }

    /* Static mode styles (Sidebar) */
    .chat-container.static-chat {
        position: relative;
        width: 100%;
        height: 100%;
        max-height: 800px;
        border-radius: 14px;
        box-shadow: 0 1px 15px rgba(0,0,0,0.05);
    }

    .chat-header { 
        padding: 0.9rem 1.25rem; 
        border-bottom: 1px solid rgba(0,0,0,0.04); 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        background: #fff; 
        border-radius: 20px 20px 0 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .chat-header h6 { margin: 0; font-weight: 700; font-size: 1rem; color: #111; letter-spacing: -0.01em; }
    
    .chat-close-btn { 
        background: transparent; border: none; width: 32px; height: 32px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; cursor: pointer; color: #999;
        transition: all 0.2s;
    }
    .chat-close-btn:hover { background: #f0f2f5; color: #dc3545; transform: rotate(90deg); }

    .header-status-badge {
        font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;
        padding: 2px 8px; border-radius: 6px; margin-left: 8px; vertical-align: middle;
    }
    .status-open-lite { background: rgba(220,53,69,0.1); color: #dc3545; }
    .status-progress-lite { background: rgba(212, 175, 83, 0.15); color: #d4af53; }
    .status-closed-lite { background: rgba(25,135,84,0.1); color: #198754; }

    .chat-messages { 
        flex: 1; overflow-y: auto; padding: 1.25rem; background: #fff; 
        display: flex; flex-direction: column; gap: 0.85rem;
        scroll-behavior: smooth;
    }

    /* Facebook-style Bubbles */
    .bubble { max-width: 85%; display: flex; flex-direction: column; position: relative; }
    
    /* 'me' appears on the left in Blue (as requested) */
    .bubble.me { align-self: flex-start; align-items: flex-start; }
    /* 'them' appears on the right in White */
    .bubble.them { align-self: flex-end; align-items: flex-end; }

    .bubble-content { 
        padding: 0.6rem 1rem; 
        border-radius: 18px; 
        font-size: 0.95rem; 
        line-height: 1.5; 
        position: relative;
        overflow-wrap: break-word;
        word-break: break-word;
    }
    
    .bubble.me .bubble-content { 
        background: #0084ff; 
        color: #fff; 
        border-bottom-left-radius: 4px; 
    }
    
    .bubble.them .bubble-content { 
        background: #f0f2f5; 
        color: #050505; 
        border-bottom-right-radius: 4px;
        border: 1px solid #e4e6eb;
    }

    .bubble-info { font-size: 0.72rem; color: #65676b; margin-top: 4px; }
    .bubble.me .bubble-info { text-align: left; margin-left: 4px; }
    .bubble.them .bubble-info { text-align: right; margin-right: 4px; }

    .unread-divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0 1rem; color: #1877f2; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; }
    .unread-divider::before, .unread-divider::after { content: ""; flex: 1; height: 1px; background: #e7f3ff; }
    .unread-divider span { background: #e7f3ff; padding: 4px 12px; border-radius: 20px; }

    /* Chat Input Area */
    .chat-footer { padding: 1rem 1.2rem; border-top: 1px solid #f0f2f5; background: #fff; border-radius: 0 0 14px 14px; }
    .chat-form { display: flex; align-items: center; gap: 0.5rem; }
    .chat-input-wrap { flex: 1; display: flex; align-items: center; }
    .chat-input { 
        width: 100%; border: none; background: #f0f2f5; border-radius: 20px; 
        padding: 8px 15px; font-size: 0.95rem; resize: none; outline: none; 
        max-height: 120px; font-family: inherit; line-height: 1.4; display: block;
        min-height: 38px;
    }
    .chat-input:focus { background: #eaebed; }
    .btn-send { 
        background: #0084ff; color: #fff; border: none; width: 34px; height: 34px; 
        border-radius: 50%; display: flex; align-items: center; justify-content: center; 
        cursor: pointer; transition: all 0.2s; flex-shrink: 0; padding: 0;
    }
    .btn-send:hover { background: #0073e6; transform: scale(1.05); }
    .btn-send svg { width: 17px; height: 17px; fill: currentColor; margin-left: 2px; }

    /* Bubble images */
    .bubble-image { 
        max-width: 100%; max-height: 240px; border-radius: 14px; margin-top: 6px; cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(0,0,0,0.08); 
        box-shadow: 0 4px 12px rgba(0,0,0,0.06); display: block; object-fit: cover;
    }
    .bubble-image:hover { transform: scale(1.02); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }

    /* Image Upload Button */
    .btn-upload {
        background: none; border: none; color: #65676b; width: 34px; height: 34px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s; flex-shrink: 0;
    }
    .btn-upload:hover { background: #f0f2f5; color: #1c1e21; }
    .btn-upload svg { width: 22px; height: 22px; }

    /* New CSS for image preview */
    .chat-image-preview-wrap {
        padding: 0.8rem 1.2rem; border-top: 1px solid #f0f2f5; display: none; background: #fff;
    }
    .chat-image-preview {
        position: relative; width: 80px; height: 80px; border-radius: 12px; overflow: visible; border: 1px solid #e8eaed;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .chat-image-preview img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
    .remove-preview {
        position: absolute; top: -8px; right: -8px; background: #fff; color: #333; border: 1px solid #e8eaed;
        border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
        font-size: 14px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: all 0.2s;
    }
    .remove-preview:hover { background: #f0f2f5; color: #dc3545; transform: scale(1.1); }

    /* Sending indicator styles */
    .bubble.sending { opacity: 0.7; }
    .bubble-image-container {
        position: relative;
        display: inline-block;
        margin-top: 6px;
        min-width: 100px;
        min-height: 60px;
        background: #f0f2f5;
        border-radius: 14px;
        overflow: hidden;
    }
    .image-loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
    }
    .spinner-border-sm-custom {
        width: 1.2rem;
        height: 1.2rem;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-top: 2px solid #0084ff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .sending-status {
        font-size: 0.65rem;
        color: #65676b;
        margin-top: 2px;
        font-style: italic;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .bubble.me .sending-status { justify-content: flex-start; }
    .bubble.them .sending-status { justify-content: flex-end; }

    /* Scrollbar */
    .chat-messages::-webkit-scrollbar { width: 6px; }

    /* Mobile Responsiveness */
    @media (max-width: 480px) {
        .chat-container.floating-chat {
            width: 100%;
            height: 90vh; /* Fill more of the screen on mobile */
            right: 0;
            border-radius: 20px 20px 0 0;
            max-width: 100vw;
        }

        .chat-floating-btn {
            bottom: 15px;
            right: 15px;
            width: 44px;
            height: 44px;
        }

        .bubble {
            max-width: 90%;
        }

        .bubble-image {
            max-height: 200px;
        }

        .chat-header {
            padding: 0.75rem 1rem;
        }

        .chat-messages {
            padding: 1rem;
        }
    }
</style>

@if(isset($withTrigger) && $withTrigger && isset($ticket))
    {{-- Floating Messenger Button --}}
    <button class="chat-floating-btn" onclick="openAdminChat({{ $ticket->id }})" title="Open Chat">
        <svg viewBox="0 0 256 256" width="34" height="34" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="floating-messenger-grad" x1="0" y1="1" x2="1" y2="0">
                    <stop offset="0%" stop-color="#00C6FF" />
                    <stop offset="50%" stop-color="#0078FF" />
                    <stop offset="100%" stop-color="#A033FF" />
                </linearGradient>
            </defs>
            <path fill="url(#floating-messenger-grad)"
                d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" />
        </svg>

        @php
            $msgCount = $ticket->unread_replies_count ?? 0;
        @endphp
        @if($msgCount > 0)
            <span class="chat-badge-bubble" id="floatingChatBadge">
                {{ $msgCount > 99 ? '99+' : $msgCount }}
            </span>
        @endif
    </button>
@endif

<div id="adminChatContainer" class="chat-container {{ isset($isStatic) && $isStatic ? 'static-chat' : 'floating-chat' }}">
    <div class="chat-header">
        <div style="flex: 1; min-width: 0; overflow: hidden;">
            <div class="d-flex align-items-center">
                <h6 id="chatHeaderTitle" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ isset($ticket) ? $ticket->subject : '' }}">
                    {{ isset($ticket) ? $ticket->subject : 'Select a ticket' }}
                </h6>
                <span id="chatStatusBadge" class="header-status-badge {{ isset($ticket) ? ($ticket->status == 'open' ? 'status-open-lite' : ($ticket->status == 'in progress' ? 'status-progress-lite' : 'status-closed-lite')) : 'd-none' }}">
                    {{ isset($ticket) ? ucfirst($ticket->status) : '' }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 0.75rem; margin-top: 4px;">
                <span id="chatUserName">{{ isset($ticket) ? ($ticket->user->name ?? 'User') : '' }}</span>
                <span id="chatUserRoleBadge">
                    @if(isset($ticket))
                        @if($ticket->user->role == 0)
                            <span class="badge" style="font-size: 0.6rem; background: rgba(59, 111, 212, 0.1); color: #3b6fd4; border: 1px solid rgba(59, 111, 212, 0.2); padding: 1px 4px;">Agent</span>
                        @else
                            <span class="badge" style="font-size: 0.6rem; background: rgba(212, 175, 83, 0.1); color: #d4af53; border: 1px solid rgba(212, 175, 83, 0.2); padding: 1px 4px;">User</span>
                        @endif
                    @endif
                </span>
                @if(Auth::check() && Auth::user()->role == 1)
                     <span class="text-muted">·</span>
                     <span id="chatUserEmail">{{ isset($ticket) ? ($ticket->user->email ?? '') : '' }}</span>
                @endif
            </div>
        </div>
        @if(!isset($isStatic) || !$isStatic)
            <button class="chat-close-btn" onclick="closeAdminChat()" style="flex-shrink: 0; margin-left: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        @endif
    </div>

    <div class="chat-messages" id="chatMessages">
        @if(isset($ticket))
            @if($ticket->replies->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted small">No messages yet.</div>
                </div>
            @else
                @php 
                    $unreadReplies = $ticket->replies->where('is_read', 0)->where('is_admin', 0);
                    $unreadShown = false;
                @endphp
                @foreach($ticket->replies as $reply)
                    @php
                        $isReplyAdmin = $reply->isFromAdmin();
                        $isMe = (Auth::check() && Auth::user()->role == 1) ? $isReplyAdmin : !$isReplyAdmin;
                        $bubbleClass = $isMe ? 'me' : 'them';
                    @endphp
                    @if(!$unreadShown && !$reply->isFromAdmin() && !$reply->is_read)
                        <div class="unread-divider"><span>{{ $unreadReplies->count() }} New Coming</span></div>
                        @php $unreadShown = true; @endphp
                    @endif
                    <div class="bubble {{ $bubbleClass }}" data-id="{{ $reply->id }}">
                        @if($reply->image)
                            <div class="bubble-image-container">
                                <div class="image-loader"><div class="spinner-border-sm-custom"></div></div>
                                <img src="{{ asset('storage/' . $reply->image) }}" 
                                     class="bubble-image" 
                                     style="margin-top: 0; opacity: 0; transition: opacity 0.3s;"
                                     onload="this.style.opacity='1'; this.previousElementSibling.style.display='none'; window.scrollToBottom(50);" 
                                     onclick="openGlobalLightbox(this.src)" 
                                     alt="Attachment">
                            </div>
                        @endif
                        @if($reply->body)
                            <div class="bubble-content {{ $reply->image ? 'mt-1' : '' }}">
                                {{ $reply->body }}
                            </div>
                        @endif
                        <div class="bubble-info">
                            @if($reply->isFromAdmin())
                                <span style="font-weight: 700; color: #3b6fd4;">Support</span> · {{ $reply->admin->name ?? 'Admin' }}
                            @else
                                {{ $isMe ? 'You' : ($reply->user->name ?? 'User') }}
                                @if($reply->user->role == 0)
                                    <span class="badge ms-1" style="font-size: 0.6rem; background: rgba(59, 111, 212, 0.1); color: #3b6fd4; border: 1px solid rgba(59, 111, 212, 0.2); padding: 1px 4px;">Agent👤</span>
                                @else
                                    <span class="badge ms-1" style="font-size: 0.6rem; background: rgba(212, 175, 83, 0.1); color: #d4af53; border: 1px solid rgba(212, 175, 83, 0.2); padding: 1px 4px;">User👥</span>
                                @endif
                            @endif
                            · {{ $reply->created_at->format('g:i A') }}
                        </div>
                    </div>
                @endforeach
            @endif
        @else
            <div class="text-center py-5">
                <div class="text-muted small">Loading conversation...</div>
            </div>
        @endif
    </div>

    <div id="imagePreviewWrap" class="chat-image-preview-wrap">
        <div class="chat-image-preview">
            <img id="imagePreview" src="" alt="Preview">
            <button type="button" class="remove-preview" onclick="clearImagePreview()">×</button>
        </div>
    </div>

    <div class="chat-footer">
        @php
            $defaultAction = '#';
            if (isset($ticket)) {
                $defaultAction = (Auth::check() && Auth::user()->role == 1) 
                    ? route('admin.tickets.comment', $ticket->id) 
                    : route('agent.tickets.reply', $ticket->id);
            }
            $finalAction = $customAction ?? $defaultAction;
        @endphp
        <form id="chatForm" method="POST" action="{{ $finalAction }}" class="chat-form" enctype="multipart/form-data">
            @csrf
            <button type="button" class="btn-upload" onclick="document.getElementById('chatImageInput').click()" title="Attach an image">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            </button>
            <input type="file" name="image" id="chatImageInput" accept="image/*" style="display: none;" onchange="handleImageSelect(this)">
            <div class="chat-input-wrap">
                <textarea name="body" class="chat-input" placeholder="Aa" rows="1"></textarea>
            </div>
            <button type="submit" class="btn-send">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </form>
    </div>
</div>

<script>
    (function() {
        const container = document.getElementById('adminChatContainer');
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const chatInput = chatForm.querySelector('.chat-input');
        const imageInput = document.getElementById('chatImageInput');
        const previewWrap = document.getElementById('imagePreviewWrap');
        const previewImg = document.getElementById('imagePreview');
        const isUserContext = {{ (Auth::check() && Auth::user()->role != 1) ? 'true' : 'false' }};
        const isSingleTicketView = {{ isset($ticket) ? 'true' : 'false' }};
        let currentTicketId = {{ isset($ticket) ? $ticket->id : 'null' }};
        let lastMessageId = null;
        let pollInterval = null;
        let isSubmitting = false;

        // Auto-scroll helper
        window.scrollToBottom = (delay = 0) => {
            if (delay > 0) {
                setTimeout(() => {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, delay);
            } else {
                requestAnimationFrame(() => {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
            }
        };
        const scrollToBottom = window.scrollToBottom;

        if ({{ isset($isStatic) && $isStatic ? 'true' : 'false' }}) {
            window.addEventListener('load', () => {
                scrollToBottom();
                const lastBubble = chatMessages.querySelector('.bubble:last-child');
                // We'll trust the initial load IDs if we need polling for static, 
                // but usually static is just for the show page.
                // For now, let's start polling even if static if there is a ticket.
                if (currentTicketId) startPolling();
            });
        }

        window.handleImageSelect = function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewWrap.style.display = 'block';
                    scrollToBottom();
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.clearImagePreview = function() {
            imageInput.value = '';
            previewWrap.style.display = 'none';
            previewImg.src = '';
        };

        function startPolling() {
            stopPolling();
            pollInterval = setInterval(pollForNewMessages, 2000); // Faster polling for chat
        }

        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }

        async function pollForNewMessages() {
            if (!currentTicketId) return;

            try {
                // Ensure last_id is properly handled
                const lastIdParam = lastMessageId ? `last_id=${lastMessageId}` : '';
                const dataUrl = isUserContext 
                    ? `/agent/tickets/${currentTicketId}/chat-data?${lastIdParam}` 
                    : `/admin/tickets/${currentTicketId}/chat-data?${lastIdParam}`;
                
                const response = await fetch(dataUrl);
                const data = await response.json();

                if (data.success) {
                    if (data.replies && data.replies.length > 0) {
                        appendMessages(data.replies);
                    }
                }
            } catch (error) {
                console.warn('Polling failed:', error);
            }
        }

        async function pollGlobalUnreadCounts() {
            try {
                const response = await fetch('/tickets/unread-counts');
                const data = await response.json();
                if (data.success) {
                    updateAllBadges(data.counts);
                }
            } catch (error) {
                console.warn('Global unread poll failed:', error);
            }
        }

        function updateAllBadges(counts) {
            let totalUnread = 0;
            
            for (const [ticketId, count] of Object.entries(counts)) {
                totalUnread += count;
                
                // Update dashboard badges
                const badgeId = `unread-count-${ticketId}`;
                let badge = document.getElementById(badgeId);
                const row = document.querySelector(`tr[data-ticket-id="${ticketId}"]`);
                
                if (count > 0) {
                    if (badge) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'block';
                    } else {
                        // Try to find the chat button to append badge
                        const chatBtn = document.querySelector(`tr[data-ticket-id="${ticketId}"] .action-btn-premium`);
                        if (chatBtn) {
                            chatBtn.insertAdjacentHTML('beforeend', `
                                <span id="${badgeId}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm"
                                      style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">
                                    ${count > 99 ? '99+' : count}
                                </span>
                            `);
                        }
                    }
                    if (row) row.classList.add('unread-row');
                } else {
                    if (badge) badge.style.display = 'none';
                    if (row) row.classList.remove('unread-row');
                }
            }
            
            // Update floating button badge
            let floatingBadge = document.getElementById('floatingChatBadge');
            const floatingBtn = document.querySelector('.chat-floating-btn');
            
            // Determine which count to show on the floating messenger
            let displayCount = totalUnread;
            if (isSingleTicketView && currentTicketId) {
                displayCount = counts[currentTicketId] || 0;
            }

            // Only show the floating badge if the count > 0 AND the chat is NOT currently open
            if (displayCount > 0 && !container.classList.contains('active')) {
                if (floatingBadge) {
                    floatingBadge.textContent = displayCount > 99 ? '99+' : displayCount;
                    floatingBadge.style.display = 'block';
                } else if (floatingBtn) {
                    floatingBtn.insertAdjacentHTML('beforeend', `
                        <span class="chat-badge-bubble" id="floatingChatBadge">
                            ${displayCount > 99 ? '99+' : displayCount}
                        </span>
                    `);
                }
            } else {
                if (floatingBadge) floatingBadge.style.display = 'none';
            }
            
            // Update the sidebar Messages menu badge
            const sidebarBadge = document.getElementById('sidebar-messages-badge');
            const userSidebarBadge = document.getElementById('sidebar-user-messages-badge');
            
            if (sidebarBadge) {
                if (totalUnread > 0) {
                    sidebarBadge.textContent = totalUnread > 99 ? '99+' : totalUnread;
                    sidebarBadge.style.display = '';
                } else {
                    sidebarBadge.style.display = 'none';
                }
            }

            if (userSidebarBadge) {
                if (totalUnread > 0) {
                    userSidebarBadge.textContent = totalUnread > 99 ? '99+' : totalUnread;
                    userSidebarBadge.style.display = '';
                } else {
                    userSidebarBadge.style.display = 'none';
                }
            }
        }

        // Start global polling
        setInterval(pollGlobalUnreadCounts, 5000); // Faster Global polling (5s)
        pollGlobalUnreadCounts(); // Initial check

        // Global function to open chat (used by dashboard)
        window.openAdminChat = async function(ticketId) {
            currentTicketId = ticketId;
            lastMessageId = null;
            stopPolling();
            
            container.classList.add('active');
            chatMessages.innerHTML = '<div class="text-center py-5"><div class="text-muted small">Loading messages...</div></div>';
            
            const commentUrl = isUserContext 
                ? `/agent/tickets/${ticketId}/reply` 
                : `/admin/tickets/${ticketId}/comment`;
            chatForm.action = commentUrl;
            clearImagePreview();

            // Clear unread visual indicators instantly
            const badge = document.getElementById(`unread-count-${ticketId}`);
            if (badge) {
                const count = parseInt(badge.textContent) || 0;
                const sidebarBadge = document.getElementById('sidebar-messages-badge');
                if (sidebarBadge) {
                    const currentTotal = parseInt(sidebarBadge.textContent) || 0;
                    const newTotal = Math.max(0, currentTotal - count);
                    if (newTotal > 0) {
                        sidebarBadge.textContent = newTotal > 99 ? '99+' : newTotal;
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }
                badge.remove();
            }

            const floatingBadge = document.getElementById('floatingChatBadge');
            if (floatingBadge) floatingBadge.remove();
            
            const ticketRow = document.querySelector(`tr[data-ticket-id="${ticketId}"], .chat-item[data-ticket-id="${ticketId}"]`);
            if (ticketRow) {
                ticketRow.classList.remove('unread-row');
                const dot = ticketRow.querySelector('.unread-indicator-dot, .unread-dot');
                if (dot) dot.remove();
                const msgBadge = ticketRow.querySelector('.msg-unread-badge');
                if (msgBadge) msgBadge.remove();
            }

            try {
                const dataUrl = isUserContext 
                    ? `/agent/tickets/${ticketId}/chat-data` 
                    : `/admin/tickets/${ticketId}/chat-data`;
                const response = await fetch(dataUrl);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('chatHeaderTitle').textContent = data.ticket.subject || `Ticket #${data.ticket.id}`;
                    document.getElementById('chatHeaderTitle').title = data.ticket.subject || '';
                    document.getElementById('chatUserName').textContent = data.ticket.user_name || 'User';
                    
                    const statusBadge = document.getElementById('chatStatusBadge');
                    statusBadge.classList.remove('d-none', 'status-open-lite', 'status-progress-lite', 'status-closed-lite');
                    statusBadge.textContent = data.ticket.status.charAt(0).toUpperCase() + data.ticket.status.slice(1);
                    
                    if (data.ticket.status === 'open') statusBadge.classList.add('status-open-lite');
                    else if (data.ticket.status === 'in progress') statusBadge.classList.add('status-progress-lite');
                    else statusBadge.classList.add('status-closed-lite');

                    renderMessages(data.replies, data.unread_count);
                    
                    if (data.replies.length > 0) {
                        lastMessageId = data.replies[data.replies.length - 1].id;
                    }
                    
                    startPolling();

                    scrollToBottom(50); 
                    scrollToBottom(300);
                }
            } catch (error) {
                console.error('Failed to load chat data:', error);
                chatMessages.innerHTML = '<div class="text-center py-5 text-danger small">Error loading messages.</div>';
            }
        };

        window.closeAdminChat = function() {
            container.classList.remove('active');
            stopPolling();
        };

        function renderMessages(replies, unreadCount) {
            if (replies.length === 0) {
                chatMessages.innerHTML = '<div class="text-center py-5"><div class="text-muted small">No messages yet.</div></div>';
                return;
            }

            chatMessages.innerHTML = replies.map(reply => buildMessageHtml(reply, unreadCount)).join('');
        }

        function appendMessages(replies) {
            if (!replies || replies.length === 0) return;

            // Remove empty state message if it exists
            const emptyState = chatMessages.querySelector('.text-center');
            if (emptyState && emptyState.textContent.includes('No messages yet')) {
                chatMessages.innerHTML = '';
            }

            let hasNew = false;
            replies.forEach(reply => {
                // Avoid duplicates using ID
                if (reply.id && document.querySelector(`.bubble[data-id="${reply.id}"]`)) return;

                const html = buildMessageHtml(reply);
                chatMessages.insertAdjacentHTML('beforeend', html);
                hasNew = true;
                
                // Only update lastMessageId if a valid ID is present
                if (reply.id) {
                    lastMessageId = reply.id;
                }
            });

            if (hasNew) {
                scrollToBottom(50);
            }
        }

        // Click outside to close
        document.addEventListener('mousedown', function(e) {
            // Only handle if chat is floating and active
            if (container.classList.contains('floating-chat') && container.classList.contains('active')) {
                const floatingBtn = document.querySelector('.chat-floating-btn');
                const isClickInside = container.contains(e.target);
                const isClickOnBtn = floatingBtn && floatingBtn.contains(e.target);
                
                // If click is outside both the chat and the trigger button, close it
                if (!isClickInside && !isClickOnBtn) {
                    closeAdminChat();
                }
            }
        });

        function buildMessageHtml(reply, unreadCount = 0) {
            const isMe = isUserContext ? !reply.is_admin : reply.is_admin;
            const bubbleClass = isMe ? 'me' : 'them';
            const senderLabel = isUserContext 
                ? (reply.is_admin ? (reply.sender || 'Support') : 'You')
                : (reply.is_admin ? (reply.sender || 'Admin') : (reply.sender || 'User'));
            
            const isSending = reply.is_sending || false;
            const bubbleIdAttr = reply.id ? `data-id="${reply.id}"` : '';
            const tempIdAttr = reply.temp_id ? `data-temp-id="${reply.temp_id}"` : '';

            let imageHtml = '';
            if (reply.image) {
                imageHtml = `
                    <div class="bubble-image-container">
                        <div class="image-loader"><div class="spinner-border-sm-custom"></div></div>
                        <img src="${reply.image}" 
                             class="bubble-image" 
                             style="margin-top:0; opacity: 0; transition: opacity 0.3s;" 
                             onload="this.style.opacity='1'; this.previousElementSibling.style.display='none'; window.scrollToBottom(50);"
                             onclick="openGlobalLightbox(this.src)" 
                             alt="Attachment">
                    </div>
                `;
            }

            return `
                ${reply.is_first_unread ? `<div class="unread-divider"><span>${unreadCount} New Coming</span></div>` : ''}
                <div class="bubble ${bubbleClass} ${isSending ? 'sending' : ''}" ${bubbleIdAttr} ${tempIdAttr}>
                    ${imageHtml}
                    ${reply.body ? `<div class="bubble-content ${reply.image ? 'mt-1' : ''}">${reply.body}</div>` : ''}
                    <div class="bubble-info">
                        ${senderLabel} · ${reply.time}
                        ${isSending ? `<span class="sending-status"><div class="spinner-border-sm-custom" style="width:10px;height:10px;border-width:1px;"></div> Sending...</span>` : ''}
                    </div>
                </div>
            `;
        }

        // AJAX Submission
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (isSubmitting || (!chatInput.value.trim() && !imageInput.files.length) || !currentTicketId) return;

            isSubmitting = true;
            const submitBtn = this.querySelector('.btn-send');
            const originalOpacity = submitBtn.style.opacity;
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';

            const formData = new FormData(this);
            
            // Immediate feedback for image
            let tempId = null;
            if (imageInput.files.length > 0) {
                tempId = 'temp_' + Date.now();
                const tempReply = {
                    temp_id: tempId,
                    body: chatInput.value.trim(),
                    image: URL.createObjectURL(imageInput.files[0]),
                    is_admin: !isUserContext,
                    sender: isUserContext ? 'You' : 'Admin',
                    time: new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
                    is_sending: true
                };
                appendMessages([tempReply]);
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    // Remove temporary bubble if it exists
                    if (tempId) {
                        const tempBubble = chatMessages.querySelector(`[data-temp-id="${tempId}"]`);
                        if (tempBubble) tempBubble.remove();
                    }

                    appendMessages([data.reply]);
                    
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    clearImagePreview();
                    
                    const countEl = document.getElementById('chatCount');
                    if (countEl) {
                        const currentCount = parseInt(countEl.textContent) || 0;
                        countEl.textContent = (currentCount + 1) + ' messages';
                    }
                } else {
                    if (tempId) {
                        const tempBubble = chatMessages.querySelector(`[data-temp-id="${tempId}"]`);
                        if (tempBubble) tempBubble.remove();
                    }
                    alert(data.message || 'Submission failed');
                }
            } catch (error) {
                if (tempId) {
                    const tempBubble = chatMessages.querySelector(`[data-temp-id="${tempId}"]`);
                    if (tempBubble) tempBubble.remove();
                }
                console.error('Submission failed:', error);
                alert('Connection error. Please try again.');
            } finally {
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.style.opacity = originalOpacity || '1';
            }
        });

        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.requestSubmit();
            }
        });

        // Expose a helper to update the chat badge externally
        window.updateChatStatusBadge = function(newStatus) {
             const chatBadge = document.getElementById('chatStatusBadge');
             if (chatBadge) {
                 chatBadge.className = 'header-status-badge';
                 if (newStatus === 'open') chatBadge.classList.add('status-open-lite');
                 else if (newStatus === 'in progress') chatBadge.classList.add('status-progress-lite');
                 else chatBadge.classList.add('status-closed-lite');
                 chatBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
             }
        };
    })();
</script>
