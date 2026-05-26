/**
 * Laravel Echo WebSocket client for admin, agent & user interfaces.
 */
(function () {
    const config = window.RealtimeConfig;
    if (!config || !config.key) {
        console.warn('[Realtime] Missing RealtimeConfig or app key. Run: php artisan reverb:start');
        return;
    }

    if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
        console.warn('[Realtime] Pusher or Echo library not loaded.');
        return;
    }

    const useTls = config.scheme === 'https';
    const listeners = { 'ticket.changed': [], 'ticket.deleted': [], 'reply.created': [] };
    window.Realtime = {
        on(event, callback) {
            if (!listeners[event]) listeners[event] = [];
            listeners[event].push(callback);
        },
        emit(event, payload) {
            (listeners[event] || []).forEach((cb) => {
                try { cb(payload); } catch (e) { console.error('[Realtime] listener error', e); }
            });
        },
        refreshSidebar: debounce(refreshSidebarCounts, 400),
    };

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: config.key,
        cluster: 'mt1',
        wsHost: config.host,
        wsPort: config.port,
        wssPort: config.port,
        forceTLS: useTls,
        encrypted: useTls,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: config.authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });

    if (config.role === 1) {
        window.Echo.private('admin')
            .listen('.ticket.changed', (e) => {
                window.Realtime.emit('ticket.changed', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.ticket.deleted', (e) => {
                window.Realtime.emit('ticket.deleted', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.reply.created', (e) => {
                window.Realtime.emit('reply.created', e);
                window.Realtime.refreshSidebar();
            });
    }

    if (config.role === 0) {
        window.Echo.private(`agent.${config.userId}`)
            .listen('.ticket.changed', (e) => {
                window.Realtime.emit('ticket.changed', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.ticket.deleted', (e) => {
                window.Realtime.emit('ticket.deleted', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.reply.created', (e) => {
                window.Realtime.emit('reply.created', e);
                window.Realtime.refreshSidebar();
            });
    }

    if (config.role === 2) {
        window.Echo.private(`user.${config.userId}`)
            .listen('.ticket.changed', (e) => {
                window.Realtime.emit('ticket.changed', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.ticket.deleted', (e) => {
                window.Realtime.emit('ticket.deleted', e);
                window.Realtime.refreshSidebar();
            })
            .listen('.reply.created', (e) => {
                window.Realtime.emit('reply.created', e);
                window.Realtime.refreshSidebar();
            });
    }

    refreshSidebarCounts();

    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    async function refreshSidebarCounts() {
        const url = '/sidebar/unread-counts';
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!data.success) return;

            if (data.role == 1 && data.admin) {
                updateBadge('sidebar-admin-tickets-badge', data.admin.total_tickets);
                updateBadge('sidebar-agent-tickets-badge', data.admin.agent_tickets);
                updateBadge('sidebar-user-tickets-badge', data.admin.user_tickets);
                updateBadge('sidebar-messages-badge', data.admin.messages);
            } else if (data.user) {
                updateBadge('sidebar-user-messages-badge', data.user.messages);
            }
        } catch (error) {
            console.error('[Realtime] Sidebar refresh failed:', error);
        }
    }

    function updateBadge(id, count) {
        const badge = document.getElementById(id);
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }
})();
