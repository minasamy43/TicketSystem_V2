/**
 * DOM handlers for WebSocket-driven updates (admin, agent & user).
 */
(function () {
    function getAdminTicketFilters() {
        const dateElement = document.querySelector('input[name="date"]');
        const statusElement = document.querySelector('select[name="status"]');
        const subjectElement = document.getElementById('filter_subject');
        const userNameElement = document.getElementById('filter_user_name');
        const inprogressNameElement = document.getElementById('filter_inprogress_name');
        const closerNameElement = document.getElementById('filter_closer_name');
        const ticketIdElement = document.getElementById('filter_ticket_id');

        if (!dateElement) return null;

        return {
            date: dateElement.value,
            status: statusElement?.value || '',
            subject: subjectElement?.value || '',
            user_name: userNameElement?.value || '',
            inprogress_name: inprogressNameElement?.value || '',
            closer_name: closerNameElement?.value || '',
            ticket_id: ticketIdElement?.value || '',
            sender_type: (typeof ADMIN_TICKETS_CONFIG !== 'undefined' && ADMIN_TICKETS_CONFIG.senderType) ? ADMIN_TICKETS_CONFIG.senderType : '',
        };
    }

    function getAgentTicketFilters() {
        const dateElement = document.querySelector('input[name="date"]');
        const statusElement = document.querySelector('select[name="status"]');
        const subjectElement = document.getElementById('filter_subject');
        const closerNameElement = document.getElementById('filter_closer_name');
        if (!dateElement) return null;

        return {
            date: dateElement.value,
            status: statusElement?.value || '',
            subject: subjectElement?.value || '',
            closer_name: closerNameElement?.value || '',
        };
    }

    function getUserTicketFilters() {
        const filterForm = document.getElementById('filterForm');
        if (!filterForm) return null;

        return {
            date: filterForm.querySelector('input[name="date"]')?.value || '',
            status: filterForm.querySelector('input[name="status"]')?.value || '',
            subject: filterForm.querySelector('input[name="subject"]')?.value || '',
        };
    }

    function matchesSenderType(ticket, senderType) {
        if (!senderType) return true;
        const role = senderType === 'agent' ? 0 : 2;
        return (ticket.user_role ?? 2) === role;
    }

    function matchesAdminFilters(ticket, filters) {
        if (!filters) return false;
        if (ticket.created_date !== filters.date) return false;
        if (filters.ticket_id && String(ticket.id) !== String(filters.ticket_id)) return false;
        if (filters.status && ticket.status !== filters.status) return false;
        if (filters.subject && !ticket.subject.toLowerCase().includes(filters.subject.toLowerCase())) return false;
        if (filters.user_name && !ticket.user_name.toLowerCase().includes(filters.user_name.toLowerCase())) return false;
        if (filters.inprogress_name && !ticket.inprogress_by.toLowerCase().includes(filters.inprogress_name.toLowerCase())) return false;
        if (filters.closer_name && !ticket.closer.toLowerCase().includes(filters.closer_name.toLowerCase())) return false;
        if (!matchesSenderType(ticket, filters.sender_type)) return false;
        return true;
    }

    function matchesAgentFilters(ticket, filters) {
        if (!filters) return false;
        if (ticket.created_date !== filters.date) return false;
        if (filters.status && ticket.status !== filters.status) return false;
        if (filters.subject && !ticket.subject.toLowerCase().includes(filters.subject.toLowerCase())) return false;
        if (filters.closer_name && !ticket.closer.toLowerCase().includes(filters.closer_name.toLowerCase())) return false;
        return true;
    }

    function matchesUserFilters(ticket, filters) {
        if (!filters) return true;
        if (filters.date && ticket.created_date !== filters.date) return false;
        if (filters.status && ticket.status !== filters.status) return false;
        if (filters.subject && !ticket.subject.toLowerCase().includes(filters.subject.toLowerCase())) return false;
        return true;
    }

    function removeTicketRow(ticketId) {
        const row = document.querySelector(`tr[data-ticket-id="${ticketId}"]`);
        if (row) row.remove();

        const chatItem = document.querySelector(`.chat-item[data-ticket-id="${ticketId}"]`);
        if (chatItem) chatItem.remove();

        const badge = document.getElementById(`unread-count-${ticketId}`);
        if (badge) badge.remove();

        if (typeof window.handleInboxTicketDeleted === 'function') {
            window.handleInboxTicketDeleted(ticketId);
        }

        if (typeof window.closeAdminChat === 'function' && window.currentTicketId == ticketId) {
            window.closeAdminChat();
        }
    }

    window.AdminTicketRealtime = {
        removeRow: removeTicketRow,

        applyUpdate(update) {
            const row = document.querySelector(`tr[data-ticket-id="${update.id}"]`);
            if (!row) return;

            const statusSelect = row.querySelector('.status-select-badge');
            if (statusSelect && statusSelect.value !== update.status) {
                statusSelect.value = update.status;
                statusSelect.className = 'status-select-badge';
                if (update.status === 'open') statusSelect.classList.add('status-open');
                else if (update.status === 'in progress') statusSelect.classList.add('status-progress');
                else if (update.status === 'closed') statusSelect.classList.add('status-closed');
                Array.from(statusSelect.options).forEach(o => o.defaultSelected = (o.value === update.status));
            }

            const inprogressCell = document.getElementById(`inprogress-${update.id}`);
            if (inprogressCell) inprogressCell.textContent = update.inprogress_by;

            const closerCell = document.getElementById(`closer-${update.id}`);
            if (closerCell) closerCell.textContent = update.closer;

            let unreadCount = update.unread_count;
            if (window.isTicketChatOpen?.(update.id)) {
                unreadCount = 0;
            }

            const chatBtn = row.querySelector('.action-btn-premium[title="Chat"]');
            if (chatBtn) {
                // Use the always-rendered span (hide/show) rather than remove/re-create
                let unreadBadge = document.getElementById(`unread-count-${update.id}`)
                    || chatBtn.querySelector(`span[id^="unread-count-"]`);
                if (unreadCount > 0) {
                    if (!unreadBadge) {
                        unreadBadge = document.createElement('span');
                        unreadBadge.id = `unread-count-${update.id}`;
                        unreadBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm';
                        unreadBadge.style.cssText = 'font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;';
                        chatBtn.appendChild(unreadBadge);
                    }
                    unreadBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    unreadBadge.style.display = '';
                    row.classList.add('unread-row');
                } else {
                    if (unreadBadge) unreadBadge.style.display = 'none'; // hide, don't remove
                    row.classList.remove('unread-row');
                }
            }
        },

        insertRow(ticket) {
            const tbody = document.querySelector('table tbody');
            if (!tbody || document.querySelector(`tr[data-ticket-id="${ticket.id}"]`)) return;

            const newRow = document.createElement('tr');
            newRow.setAttribute('data-ticket-id', ticket.id);
            newRow.className = 'new-entry-flash unread-row';
            newRow.style.cursor = 'pointer';

            newRow.innerHTML = `
                <td style="font-weight: 600; color: #000;">#${ticket.id}</td>
                <td style="font-weight: 500;">
                    <div class="d-flex align-items-center gap-1">
                        ${(ticket.user_role ?? 2) === 0
                            ? `<i class="fa-solid fa-user-cog" style="font-size: 0.75rem; color: var(--primary-color);" title="Agent"></i>`
                            : `<i class="fa-solid fa-users" style="font-size: 0.75rem; color: var(--primary-color);" title="User"></i>`
                        }
                        <span class="ms-1">${ticket.user_name}</span>
                        <span class="new-badge rounded-pill ms-2"><span class="pulse-dot"></span> New</span>
                    </div>
                </td>
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
                        <span style="color:var(--primary-color); flex-shrink:0;"><svg width="16" height="16" viewBox="0  0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg></span>
                        <div>
                            <div style="font-weight:600; color:#333; font-size:0.88rem;">${ticket.time}</div>
                            <div style="font-size:0.72rem; color:#aaa;">${ticket.relative_time}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="action-btn-premium position-relative" title="Chat">
                        <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="messenger-grad-${ticket.id}" x1="0" y1="1" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#00C6FF" />
                                    <stop offset="50%" stop-color="#0078FF" />
                                    <stop offset="100%" stop-color="#A033FF" />
                                </linearGradient>
                            </defs>
                            <path fill="url(#messenger-grad-${ticket.id})" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" />
                        </svg>
                        <span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1; ${ticket.unread_count > 0 ? '' : 'display:none;'}">
                            ${ticket.unread_count > 99 ? '99+' : (ticket.unread_count || '')}
                        </span>
                    </a>
                </td>`;

            newRow.addEventListener('click', function (e) {
                if (e.target.closest('a[onclick^="openAdminChat"]')) {
                    this.classList.remove('unread-row');
                    const badge = this.querySelector('.new-badge');
                    if (badge) badge.remove();
                }
                if (e.target.closest('a, button, input, select')) return;
                window.location.href = `/admin/tickets/${ticket.id}`;
            });

            const emptyRow = tbody.querySelector('.empty-state-row');
            if (emptyRow) emptyRow.remove();
            tbody.insertBefore(newRow, tbody.firstChild);

            if (typeof ADMIN_TICKETS_CONFIG !== 'undefined') {
                ADMIN_TICKETS_CONFIG.highestTicketId = Math.max(ADMIN_TICKETS_CONFIG.highestTicketId || 0, ticket.id);
            }
        },
    };

    window.UserTicketRealtime = {
        removeRow: removeTicketRow,

        applyUpdate(update) {
            const row = document.querySelector(`tr[data-ticket-id="${update.id}"]`);
            if (!row) return;

            const statusPill = row.querySelector('.status-pill');
            if (statusPill) {
                statusPill.classList.remove('open', 'in-progress', 'closed');
                const normalizedStatus = update.status_label.toLowerCase().replace(' ', '-');
                statusPill.classList.add(normalizedStatus);
                statusPill.innerHTML = `${update.status_icon} ${update.status_label}`;
            }

            let unreadCount = update.unread_count;
            if (window.isTicketChatOpen?.(update.id)) {
                unreadCount = 0;
            }

            const chatBtn = row.querySelector('.chat-btn-modern[title="Open Chat"]');
            if (chatBtn) {
                let unreadBadge = document.getElementById(`unread-count-${update.id}`);
                if (unreadCount > 0) {
                    if (!unreadBadge) {
                        unreadBadge = document.createElement('span');
                        unreadBadge.id = `unread-count-${update.id}`;
                        unreadBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm';
                        unreadBadge.style.cssText = 'font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;';
                        chatBtn.appendChild(unreadBadge);
                    }
                    unreadBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    unreadBadge.style.display = '';
                } else if (unreadBadge) {
                    unreadBadge.style.display = 'none';
                }
            }
        },

        insertRow(ticket) {
            const tbody = document.querySelector('table tbody');
            if (!tbody || document.querySelector(`tr[data-ticket-id="${ticket.id}"]`)) return;

            const normalizedStatus = ticket.status_label.toLowerCase().replace(' ', '-');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-ticket-id', ticket.id);
            newRow.style.cursor = 'pointer';

            const unreadHtml = ticket.unread_count > 0
                ? `<span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">${ticket.unread_count > 99 ? '99+' : ticket.unread_count}</span>`
                : '';

            newRow.innerHTML = `
                <td>
                    <div class="ticket-subject">${ticket.subject}</div>
                    <div class="ticket-meta">
                        <span class="me-2">#${ticket.id}</span>
                        <span>Opened ${ticket.relative_time}</span>
                    </div>
                </td>
                <td>
                    <span class="status-pill ${normalizedStatus}">
                        ${ticket.status_icon} ${ticket.status_label}
                    </span>
                </td>
                <td>
                    <div style="font-weight: 600; color: #333; font-size: 0.9rem;">${ticket.relative_time}</div>
                    <div style="font-size: 0.75rem; color: #aaa;">${ticket.time}</div>
                </td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="chat-btn-modern position-relative" title="Open Chat">
                            <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="messenger-grad-${ticket.id}" x1="0" y1="1" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#00C6FF" />
                                        <stop offset="50%" stop-color="#0078FF" />
                                        <stop offset="100%" stop-color="#A033FF" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#messenger-grad-${ticket.id})" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" />
                            </svg>
                            <span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1; ${ticket.unread_count > 0 ? '' : 'display:none;'}">
                                ${ticket.unread_count > 99 ? '99+' : (ticket.unread_count || '')}
                            </span>
                        </a>
                        <button type="button" class="chat-btn-modern" title="Delete Ticket"
                            style="background: rgba(220,53,69,0.08); border-color: rgba(220,53,69,0.15); color: #dc3545;"
                            onclick="confirmDelete(${ticket.id}, '${ticket.subject.replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>`;

            newRow.addEventListener('click', function (e) {
                if (e.target.closest('a, button')) return;
                window.location.href = `/user/tickets/${ticket.id}`;
            });

            const emptyRow = tbody.querySelector('tr td[colspan]');
            if (emptyRow) emptyRow.closest('tr')?.remove();
            tbody.insertBefore(newRow, tbody.firstChild);

            if (typeof DASHBOARD_CONFIG !== 'undefined') {
                DASHBOARD_CONFIG.highestTicketId = Math.max(DASHBOARD_CONFIG.highestTicketId || 0, ticket.id);
            }
        },
    };

    window.AgentTicketRealtime = {
        removeRow: removeTicketRow,

        applyUpdate(update) {
            const row = document.querySelector(`tr[data-ticket-id="${update.id}"]`);
            if (!row) return;

            const statusBadge = row.querySelector('.badge');
            if (statusBadge) {
                statusBadge.style.background = update.status_bg;
                statusBadge.style.color = update.status_color;
                statusBadge.innerHTML = `${update.status_label} ${update.status_icon}`;
            }

            const closerCell = row.cells[2];
            if (closerCell) closerCell.textContent = update.closer;

            let unreadCount = update.unread_count;
            if (window.isTicketChatOpen?.(update.id)) {
                unreadCount = 0;
            }

            const chatBtn = row.querySelector('.action-btn-premium[title="Chat"]');
            if (chatBtn) {
                let unreadBadge = chatBtn.querySelector(`span[id^="unread-count-"]`);
                if (unreadCount > 0) {
                    if (!unreadBadge) {
                        unreadBadge = document.createElement('span');
                        unreadBadge.id = `unread-count-${update.id}`;
                        unreadBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm';
                        unreadBadge.style.cssText = 'font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;';
                        chatBtn.appendChild(unreadBadge);
                    }
                    unreadBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    unreadBadge.style.display = '';
                } else if (unreadBadge) {
                    unreadBadge.style.display = 'none';
                }
            }
        },

        insertRow(ticket) {
            const tbody = document.querySelector('table tbody');
            if (!tbody || document.querySelector(`tr[data-ticket-id="${ticket.id}"]`)) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-ticket-id', ticket.id);
            newRow.style.cursor = 'pointer';

            newRow.innerHTML = `
                <td style="font-weight: 500;">${ticket.subject}</td>
                <td><span class="badge" style="padding: 0.5rem 0.8rem; border-radius: 10px; font-size: 0.72rem; background: ${ticket.status_bg}; color: ${ticket.status_color};">${ticket.status_label} ${ticket.status_icon}</span></td>
                <td class="text-muted">${ticket.closer}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="color:var(--primary-color);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                        <div>
                            <div style="font-weight:600; color:#333; font-size:0.88rem;">${ticket.time}</div>
                            <div style="font-size:0.72rem; color:#aaa;">${ticket.relative_time}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="action-btn-premium position-relative" title="Chat">
                            <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="messenger-grad-${ticket.id}" x1="0" y1="1" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#00C6FF" />
                                        <stop offset="50%" stop-color="#0078FF" />
                                        <stop offset="100%" stop-color="#A033FF" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#messenger-grad-${ticket.id})" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" />
                            </svg>
                            <span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1; ${ticket.unread_count > 0 ? '' : 'display:none;'}">
                                ${ticket.unread_count > 99 ? '99+' : (ticket.unread_count || '')}
                            </span>
                        </a>
                        <form method="POST" action="/agent/tickets/${ticket.id}" class="m-0" onsubmit="return confirm('Delete this ticket?')">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="action-btn-premium action-btn-danger" title="Delete Ticket">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>`;

            const emptyRow = tbody.querySelector('.empty-state-row');
            if (emptyRow) emptyRow.remove();
            tbody.insertBefore(newRow, tbody.firstChild);

            if (typeof DASHBOARD_CONFIG !== 'undefined') {
                DASHBOARD_CONFIG.highestTicketId = Math.max(DASHBOARD_CONFIG.highestTicketId || 0, ticket.id);
            }
        },
    };

    function updateDistributionCenter(m) {
        const total = m.open + m.in_progress + m.closed;
        const centerValue = document.getElementById('distribution-center-total');
        const centerLabel = document.getElementById('distribution-center-label');
        if (centerValue) {
            centerValue.textContent = total;
            centerValue.style.color = '#1a1a1a';
        }
        if (centerLabel) {
            centerLabel.textContent = 'Total';
        }
        return total;
    }

    window.AdminDashboardRealtime = {
        applyStats(dashboard) {
            if (!dashboard || !dashboard.counts) return;
            const config = window.AdminDashboardConfig;
            const counts = dashboard.counts;

            updateValueWithEffect('open-count', counts.open);
            updateValueWithEffect('progress-count', counts.in_progress);
            updateValueWithEffect('closed-count', counts.closed);
            updateValueWithEffect('total-count', counts.total);

            if (!config) return;

            const isCurrentMonth = Number(config.currentSelection.month) === Number(config.currentDate.month)
                && Number(config.currentSelection.year) === Number(config.currentDate.year);

            if (!isCurrentMonth) return;

            if (dashboard.monthly_counts) {
                const m = dashboard.monthly_counts;

                if (window.distributionChart) {
                    window.distributionChart.data.datasets[0].data = [m.open, m.in_progress, m.closed];
                    window.distributionChart.update();
                }

                updateDistributionCenter(m);

                config.stats.allOpen = m.open;
                config.stats.allInProgress = m.in_progress;
                config.stats.allClosed = m.closed;
                config.stats.agentCount = m.agent_count;
                config.stats.userCount = m.user_count;

                const totalSource = m.agent_count + m.user_count;
                const agentPct = totalSource > 0 ? Math.round((m.agent_count / totalSource) * 100) : 0;
                const userPct = totalSource > 0 ? Math.round((m.user_count / totalSource) * 100) : 0;

                const agentBar = document.getElementById('mini-agent-bar');
                const userBar = document.getElementById('mini-user-bar');
                if (agentBar) {
                    agentBar.style.width = agentPct + '%';
                    const inner = agentBar.querySelector('div');
                    if (inner) inner.textContent = agentPct + '%';
                }
                if (userBar) {
                    userBar.style.width = userPct + '%';
                    const inner = userBar.querySelector('div');
                    if (inner) inner.textContent = userPct + '%';
                }
                const agentCount = document.getElementById('mini-agent-count');
                const userCount = document.getElementById('mini-user-count');
                if (agentCount) agentCount.textContent = m.agent_count;
                if (userCount) userCount.textContent = m.user_count;
            }

            if (window.trendChart && dashboard.today_label) {
                const todayIdx = window.trendChart.data.labels.indexOf(dashboard.today_label);
                if (todayIdx !== -1) {
                    window.trendChart.data.datasets[0].data[todayIdx] = counts.open;
                    window.trendChart.data.datasets[1].data[todayIdx] = counts.in_progress;
                    window.trendChart.data.datasets[2].data[todayIdx] = counts.closed;
                    window.trendChart.update('none');
                }
            }
        },
    };

    function updateValueWithEffect(id, newValue) {
        const el = document.getElementById(id);
        if (!el) return;
        const currentVal = parseInt(el.textContent, 10);
        if (currentVal !== newValue) {
            el.style.transition = 'all 0.3s ease';
            el.style.transform = 'scale(1.2)';
            el.style.color = '#d4af53';
            setTimeout(() => {
                el.textContent = newValue;
                el.style.transform = 'scale(1)';
                el.style.color = '';
            }, 300);
        }
    }

  function bindRealtimeWhenReady(fn) {
        if (window.Realtime) {
            fn();
            return;
        }
        const interval = setInterval(() => {
            if (window.Realtime) {
                clearInterval(interval);
                fn();
            }
        }, 100);
        setTimeout(() => clearInterval(interval), 10000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ADMIN_TICKETS_CONFIG !== 'undefined') {
            bindRealtimeWhenReady(() => {
                window.Realtime.on('ticket.changed', (e) => {
                    const filters = getAdminTicketFilters();
                    if (e.action === 'created' && matchesAdminFilters(e.ticket, filters)) {
                        window.AdminTicketRealtime.insertRow(e.ticket);
                    }
                    if (e.update) window.AdminTicketRealtime.applyUpdate(e.update);
                });
                window.Realtime.on('reply.created', (e) => {
                    if (e.ticket_update) window.AdminTicketRealtime.applyUpdate(e.ticket_update);
                });
                window.Realtime.on('ticket.deleted', (e) => {
                    if (e.id) window.AdminTicketRealtime.removeRow(e.id);
                });
            });
        }

        if (typeof AdminDashboardConfig !== 'undefined') {
            bindRealtimeWhenReady(() => {
                window.Realtime.on('ticket.changed', (e) => {
                    if (e.dashboard) window.AdminDashboardRealtime.applyStats(e.dashboard);
                });
                window.Realtime.on('ticket.deleted', (e) => {
                    if (e.dashboard) window.AdminDashboardRealtime.applyStats(e.dashboard);
                });
            });
        }

        if (typeof DASHBOARD_CONFIG !== 'undefined' && DASHBOARD_CONFIG.mode === 'agent') {
            bindRealtimeWhenReady(() => {
                window.Realtime.on('ticket.changed', (e) => {
                    const filters = getAgentTicketFilters();
                    if (e.action === 'created' && e.agent_ticket && matchesAgentFilters(e.agent_ticket, filters)) {
                        window.AgentTicketRealtime.insertRow(e.agent_ticket);
                    }
                    if (e.agent_update) window.AgentTicketRealtime.applyUpdate(e.agent_update);
                });
                window.Realtime.on('reply.created', (e) => {
                    if (e.agent_ticket_update) window.AgentTicketRealtime.applyUpdate(e.agent_ticket_update);
                });
                window.Realtime.on('ticket.deleted', (e) => {
                    if (e.id) window.AgentTicketRealtime.removeRow(e.id);
                });
            });
        }

        if (typeof DASHBOARD_CONFIG !== 'undefined' && DASHBOARD_CONFIG.mode === 'user') {
            bindRealtimeWhenReady(() => {
                window.Realtime.on('ticket.changed', (e) => {
                    const filters = getUserTicketFilters();
                    if (e.action === 'created' && e.user_ticket && matchesUserFilters(e.user_ticket, filters)) {
                        window.UserTicketRealtime.insertRow(e.user_ticket);
                    }
                    if (e.user_update) window.UserTicketRealtime.applyUpdate(e.user_update);
                });
                window.Realtime.on('reply.created', (e) => {
                    if (e.user_ticket_update) window.UserTicketRealtime.applyUpdate(e.user_ticket_update);
                });
                window.Realtime.on('ticket.deleted', (e) => {
                    if (e.id) window.UserTicketRealtime.removeRow(e.id);
                });
            });
        }

        if (window.MESSAGES_REALTIME_ENABLED) {
            bindRealtimeWhenReady(() => {
                window.Realtime.on('reply.created', (e) => {
                    if (e.message && typeof window.handleInboxMessageRealtime === 'function') {
                        window.handleInboxMessageRealtime(e.message);
                    }
                });
                window.Realtime.on('ticket.deleted', (e) => {
                    if (e.id) removeTicketRow(e.id);
                });
            });
        }
    });
})();
