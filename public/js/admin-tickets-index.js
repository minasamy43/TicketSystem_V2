/**
 * Admin Tickets Index JavaScript
 */

// Automatic Filtering Logic
let timeout = null;

function debounceSubmit() {
    clearTimeout(timeout);
    if (document.activeElement && document.activeElement.id) {
        sessionStorage.setItem('lastFocusedFilter', document.activeElement.id);
    }
    timeout = setTimeout(() => {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.submit();
        }
    }, 600);
}

// Restore focus on page load
window.addEventListener('load', function () {
    const lastFocusedId = sessionStorage.getItem('lastFocusedFilter');
    if (lastFocusedId) {
        const element = document.getElementById(lastFocusedId);
        if (element) {
            element.focus();
            const val = element.value;
            element.value = '';
            element.value = val;
        }
        sessionStorage.removeItem('lastFocusedFilter');
    }
});

// Row click handling and unread row logic
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('table tbody');
    if (tableBody) {
        tableBody.querySelectorAll('tr:not(.empty-state-row)').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function (e) {
                const isInteractive = e.target.closest('a') ||
                    e.target.closest('button') ||
                    e.target.closest('input') ||
                    e.target.closest('select') ||
                    e.target.closest('.status-select-badge');

                // Handle chat button specifically to remove unread state
                if (e.target.closest('a[onclick^="openAdminChat"]')) {
                    this.classList.remove('unread-row');
                    const badge = this.querySelector('.new-badge');
                    if (badge) badge.remove();
                }

                if (isInteractive) return;

                // Mark as read locally
                this.classList.remove('unread-row');
                const badge = this.querySelector('.new-badge');
                if (badge) badge.remove();

                const ticketId = this.getAttribute('data-ticket-id');
                if (ticketId) {
                    window.location.href = `/admin/tickets/${ticketId}`;
                }
            });
        });
    }
});

// AJAX Status Update
async function updateStatusLive(ticketId, newStatus, selectElement) {
    const previousValue = Array.from(selectElement.options).find(o => o.defaultSelected)?.value
        || Array.from(selectElement.options).find(o => o.selected)?.value;

    selectElement.style.opacity = '0.5';
    selectElement.disabled = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/admin/tickets/${ticketId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const data = await response.json();

        if (data.success) {
            selectElement.classList.remove('status-open', 'status-progress', 'status-closed');
            if (newStatus === 'open') selectElement.classList.add('status-open');
            else if (newStatus === 'in progress') selectElement.classList.add('status-progress');
            else if (newStatus === 'closed') selectElement.classList.add('status-closed');

            Array.from(selectElement.options).forEach(o => o.defaultSelected = (o.value === newStatus));

            const inprogressCell = document.getElementById(`inprogress-${ticketId}`);
            if (inprogressCell && data.inprogress_by !== undefined) inprogressCell.textContent = data.inprogress_by;

            const closerCell = document.getElementById(`closer-${ticketId}`);
            if (closerCell && data.closer !== undefined) closerCell.textContent = data.closer;
        } else {
            selectElement.value = previousValue;
            alert('⚠️ ' + data.message);
        }
    } catch (error) {
        selectElement.value = previousValue;
        console.error('Status update failed:', error);
        alert('Connection error. Please try again.');
    } finally {
        selectElement.style.opacity = '1';
        selectElement.disabled = false;
    }
}

// Real-time updates logic
(function() {
    let highestTicketId = typeof ADMIN_TICKETS_CONFIG !== 'undefined' ? ADMIN_TICKETS_CONFIG.highestTicketId : 0;
    
    if (typeof ADMIN_TICKETS_CONFIG === 'undefined') {
        console.warn('ADMIN_TICKETS_CONFIG is not defined. Real-time updates might not work correctly.');
        return;
    }

    setInterval(async () => {
        const dateElement = document.querySelector('input[name="date"]');
        const statusElement = document.querySelector('select[name="status"]');
        const subjectElement = document.getElementById('filter_subject');
        const userNameElement = document.getElementById('filter_user_name');
        const inprogressNameElement = document.getElementById('filter_inprogress_name');
        const closerNameElement = document.getElementById('filter_closer_name');
        const ticketIdElement = document.getElementById('filter_ticket_id');

        if (!dateElement || !statusElement || !subjectElement || !userNameElement || !inprogressNameElement || !closerNameElement || !ticketIdElement) return;

        const date = dateElement.value;
        const status = statusElement.value;
        const subject = subjectElement.value;
        const user_name = userNameElement.value;
        const inprogress_name = inprogressNameElement.value;
        const closer_name = closerNameElement.value;
        const ticket_id = ticketIdElement.value;

        try {
            const newDataUrl = ADMIN_TICKETS_CONFIG.newDataUrl;
            const url = new URL(newDataUrl, window.location.origin);
            url.searchParams.set('last_id', highestTicketId);
            url.searchParams.set('date', date);
            if (ticket_id) url.searchParams.set('ticket_id', ticket_id);
            if (status) url.searchParams.set('status', status);
            if (subject) url.searchParams.set('subject', subject);
            if (user_name) url.searchParams.set('user_name', user_name);
            if (inprogress_name) url.searchParams.set('inprogress_name', inprogress_name);
            if (closer_name) url.searchParams.set('closer_name', closer_name);

            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.new_tickets.length > 0) {
                const tbody = document.querySelector('table tbody');
                data.new_tickets.forEach(ticket => {
                    const existingRow = document.querySelector(`tr[data-ticket-id="${ticket.id}"]`);
                    if (!existingRow) {
                        const newRow = document.createElement('tr');
                        newRow.setAttribute('data-ticket-id', ticket.id);
                        newRow.className = 'new-entry-flash unread-row';
                        newRow.style.cursor = 'pointer';

                        newRow.innerHTML = `
                                <td style="font-weight: 600; color: #000;">#${ticket.id}</td>
                                <td>
                                    ${ticket.user_role == 0 
                                        ? '<span class="badge" style="background: rgba(59, 111, 212, 0.1); color: #3b6fd4; border: 1px solid rgba(59, 111, 212, 0.2); font-size: 0.7rem;">Agent 👤</span>' 
                                        : '<span class="badge" style="background: rgba(212, 175, 83, 0.1); color: #d4af53; border: 1px solid rgba(212, 175, 83, 0.2); font-size: 0.7rem;">User 👥</span>'
                                    }
                                </td>
                                <td style="font-weight: 500;">
                                    ${ticket.user_name}
                                    <span class="new-badge rounded-pill ms-2"><span class="pulse-dot"></span> New</span>
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
                                        <span style="color:#d4af53; flex-shrink:0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg></span>
                                        <div>
                                            <div style="font-weight:600; color:#333; font-size:0.88rem; line-height:1.2;">${ticket.time}</div>
                                            <div style="font-size:0.72rem; color:#aaa; margin-top:2px;">${ticket.relative_time}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="action-btn-premium position-relative" title="Chat">
                                        <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="messenger-grad" x1="0" y1="1" x2="1" y2="0"><stop offset="0%" stop-color="#00C6FF" /><stop offset="50%" stop-color="#0078FF" /><stop offset="100%" stop-color="#A033FF" /></linearGradient></defs><path fill="url(#messenger-grad)" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" /></svg>
                                        ${ticket.unread_count > 0 ? `<span id="unread-count-${ticket.id}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">${ticket.unread_count}</span>` : ''}
                                    </a>
                                </td>
                            `;

                        newRow.addEventListener('click', function (e) {
                            if (e.target.closest('a[onclick^="openAdminChat"]')) {
                                this.classList.remove('unread-row');
                                const badge = this.querySelector('.new-badge');
                                if (badge) badge.remove();
                            }
                            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
                            
                            this.classList.remove('unread-row');
                            const badge = this.querySelector('.new-badge');
                            if (badge) badge.remove();
                            
                            window.location.href = `/admin/tickets/${ticket.id}`;
                        });

                        const emptyRow = tbody.querySelector('.empty-state-row');
                        if (emptyRow) emptyRow.remove();
                        const emptyContainer = document.querySelector('.empty-state-container');
                        if (emptyContainer) {
                            const containerRow = emptyContainer.closest('tr');
                            if (containerRow) containerRow.remove();
                        }

                        tbody.insertBefore(newRow, tbody.firstChild);
                    }
                });
                highestTicketId = data.new_highest_id;
            }
        } catch (error) {
            console.error('Error fetching new tickets:', error);
        }
    }, 10000);
})();
