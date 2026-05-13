// Automatic Filtering Logic
let timeout = null;

function debounceSubmit() {
    clearTimeout(timeout);
    // Store the ID of the element currently in focus
    if (document.activeElement && document.activeElement.id) {
        sessionStorage.setItem('lastFocusedUserFilter', document.activeElement.id);
    }
    timeout = setTimeout(() => {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.submit();
        }
    }, 600); // 600ms delay to feel natural
}

// Restore focus on page load
window.addEventListener('load', function () {
    const lastFocusedId = sessionStorage.getItem('lastFocusedUserFilter');
    if (lastFocusedId) {
        const element = document.getElementById(lastFocusedId);
        if (element) {
            // Set focus and move cursor to the end
            element.focus();
            const val = element.value;
            element.value = '';
            element.value = val;
        }
        sessionStorage.removeItem('lastFocusedUserFilter');
    }
});

// Make table rows clickable
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('table tbody');
    if (tableBody) {
        tableBody.addEventListener('click', function(e) {
            const row = e.target.closest('tr:not(.empty-state-row)');
            if (!row) return;

            // Don't navigate if clicking on interactive elements
            const isInteractive = e.target.closest('a') ||
                e.target.closest('button') ||
                e.target.closest('input') ||
                e.target.closest('select');

            if (isInteractive) return;

            const ticketId = row.getAttribute('data-ticket-id');
            if (ticketId) {
                window.location.href = `/user/tickets/${ticketId}`;
            }
        });
    }
});

// Real-time Updates logic
(function() {
    let highestTicketId = typeof DASHBOARD_CONFIG !== 'undefined' ? DASHBOARD_CONFIG.highestTicketId : 0;
    
    if (typeof DASHBOARD_CONFIG === 'undefined') {
        console.warn('DASHBOARD_CONFIG is not defined. Real-time updates might not work correctly.');
        return;
    }

    setInterval(async () => {
        const dateElement = document.querySelector('input[name="date"]');
        const statusElement = document.querySelector('select[name="status"]');
        const subjectElement = document.getElementById('filter_subject');

        if (!dateElement || !statusElement || !subjectElement) return;

        const date = dateElement.value;
        const status = statusElement.value;
        const subject = subjectElement.value;
        
        // Gather existing IDs
        const existingIds = [];
        document.querySelectorAll('table tbody tr[data-ticket-id]').forEach(row => {
            existingIds.push(row.getAttribute('data-ticket-id'));
        });

        try {
            const newDataUrl = DASHBOARD_CONFIG.newDataUrl;
            const url = new URL(newDataUrl, window.location.origin);
            url.searchParams.set('last_id', highestTicketId);
            url.searchParams.set('date', date);
            if (status) url.searchParams.set('status', status);
            if (subject) url.searchParams.set('subject', subject);
            
            existingIds.forEach(id => url.searchParams.append('existing_ids[]', id));

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                // Update existing tickets
                if (data.updates && data.updates.length > 0) {
                    data.updates.forEach(update => {
                        const row = document.querySelector(`tr[data-ticket-id="${update.id}"]`);
                        if (row) {
                            const statusBadge = row.querySelector('.badge');
                            if (statusBadge) {
                                statusBadge.style.background = update.status_bg;
                                statusBadge.style.color = update.status_color;
                                statusBadge.innerHTML = `${update.status_label} ${update.status_icon}`;
                            }
                            
                            const closerCell = row.cells[2];
                            if (closerCell) closerCell.textContent = update.closer;
                            
                            const chatBtn = row.querySelector('.action-btn-premium[title="Chat"]');
                            if (chatBtn) {
                                let unreadBadge = chatBtn.querySelector('span[id^="unread-count-"]');
                                if (update.unread_count > 0) {
                                    if (!unreadBadge) {
                                        unreadBadge = document.createElement('span');
                                        unreadBadge.id = `unread-count-${update.id}`;
                                        unreadBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm';
                                        unreadBadge.style = 'font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;';
                                        chatBtn.appendChild(unreadBadge);
                                    }
                                    unreadBadge.textContent = update.unread_count > 99 ? '99+' : update.unread_count;
                                } else if (unreadBadge) {
                                    unreadBadge.remove();
                                }
                            }
                        }
                    });
                }
                
                // Add new tickets
                if (data.new_tickets && data.new_tickets.length > 0) {
                    const tbody = document.querySelector('table tbody');
                    data.new_tickets.forEach(ticket => {
                        const existingRow = document.querySelector(`tr[data-ticket-id="${ticket.id}"]`);
                        if (!existingRow) {
                            const newRow = document.createElement('tr');
                            newRow.setAttribute('data-ticket-id', ticket.id);
                            newRow.style.cursor = 'pointer';

                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                            newRow.innerHTML = `
                                <td style="font-weight: 500;">${ticket.subject}</td>
                                <td>
                                    <span class="badge" style="padding: 0.5rem 0.8rem; border-radius: 10px; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px; background: ${ticket.status_bg}; color: ${ticket.status_color};">
                                        ${ticket.status_label} ${ticket.status_icon}
                                    </span>
                                </td>
                                <td class="text-muted">${ticket.closer}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="color:var(--primary-color); flex-shrink:0;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                        </span>
                                        <div>
                                            <div style="font-weight:600; color:#333; font-size:0.88rem; line-height:1.2;">${ticket.time}</div>
                                            <div style="font-size:0.72rem; color:#aaa; margin-top:2px;">${ticket.relative_time}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" class="action-btn-premium" title="Chat">
                                            <svg viewBox="0 0 256 256" width="24" height="24" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="messenger-grad" x1="0" y1="1" x2="1" y2="0"><stop offset="0%" stop-color="#00C6FF" /><stop offset="50%" stop-color="#0078FF" /><stop offset="100%" stop-color="#A033FF" /></linearGradient></defs><path fill="url(#messenger-grad)" d="M128,24C68.9,24,21,68.6,21,123.5c0,31.2,15.7,58.5,40.1,76.5c1.4,1,2.5,2.6,2.8,4.3l3.8,27.3c0.4,3,3.7,4.8,6.4,3.3l29.1-14.9c1-0.5,2.2-0.6,3.2-0.3c7.2,1.8,14.8,2.7,22.7,2.7c59.1,0,107-44.6,107-99.5S187.1,24,128,24z M138.8,148v-0.1l-25.5-27c-4-4.2-10.6-4.5-15.1-0.5l-31.5,28.5c-3,2.7-7.2-0.8-5.2-4.1l29.4-48c3.2-5.3,10.6-6.6,15.5-2.8l25.3,19.3c3.8,2.9,9.3,3.3,13.5-0.1l32-26.1c3-2.5,7,1,5.2,4.3L153,141.5C149.8,146.9,142.5,148.6,138.8,148z" /></svg>
                                            ${ticket.unread_count > 0 ? '<span id="unread-count-' + ticket.id + '" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm" style="font-size: 0.66rem; padding: 0.24em 0.45em; line-height: 1;">' + (ticket.unread_count > 99 ? '99+' : ticket.unread_count) + '</span>' : ''}
                                        </a>
                                    </div>
                                </td>
                            `;

                            const emptyRow = tbody.querySelector('.empty-state-row');
                            if (emptyRow) emptyRow.remove();
                            const emptyContainer = document.querySelector('.empty-state-container');
                            if (emptyContainer) emptyContainer.remove();

                            tbody.insertBefore(newRow, tbody.firstChild);
                        }
                    });
                    highestTicketId = data.new_highest_id;
                }
            }
        } catch (error) {
            console.error('Error fetching new tickets:', error);
        }
    }, 10000);
})();
