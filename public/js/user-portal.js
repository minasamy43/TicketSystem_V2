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
            const row = e.target.closest('tr[data-ticket-id]');
            if (!row) return;

            // Don't navigate if clicking on interactive elements
            const isInteractive = e.target.closest('a') ||
                e.target.closest('button') ||
                e.target.closest('input') ||
                e.target.closest('select') ||
                e.target.closest('.chat-btn-modern');

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
        const filterForm = document.getElementById('filterForm');
        if (!filterForm) return;

        const date = filterForm.querySelector('input[name="date"]')?.value || '';
        const status = filterForm.querySelector('input[name="status"]')?.value || '';
        const subject = filterForm.querySelector('input[name="subject"]')?.value || '';
        
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
                            // Update status pill
                            const statusPill = row.querySelector('.status-pill');
                            if (statusPill) {
                                // Remove old status classes
                                statusPill.classList.remove('open', 'in-progress', 'closed');
                                // Add new status class
                                const normalizedStatus = update.status_label.toLowerCase().replace(' ', '-');
                                statusPill.classList.add(normalizedStatus);
                                statusPill.innerHTML = `${update.status_icon} ${update.status_label}`;
                            }
                            
                            // Update chat unread count
                            const chatBtn = row.querySelector('.chat-btn-modern[title="Open Chat"]');
                            if (chatBtn) {
                                let unreadBadge = chatBtn.querySelector('.badge');
                                if (update.unread_count > 0) {
                                    if (!unreadBadge) {
                                        unreadBadge = document.createElement('span');
                                        unreadBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                        unreadBadge.style = 'font-size: 0.6rem; padding: 0.3em 0.5em;';
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

                            const normalizedStatus = ticket.status_label.toLowerCase().replace(' ', '-');

                            newRow.innerHTML = `
                                <td>
                                    <div class="ticket-subject">${ticket.subject}</div>
                                    <div class="ticket-meta">
                                        <span class="me-2">#${ticket.id}</span>
                                        <span>Opened Just now</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-pill ${normalizedStatus}">
                                        ${ticket.status_icon} ${ticket.status_label}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #333; font-size: 0.9rem;">
                                        Just now
                                    </div>
                                    <div style="font-size: 0.75rem; color: #aaa;">
                                        ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0)" onclick="openAdminChat(${ticket.id})" 
                                           class="chat-btn-modern" title="Open Chat">
                                            <i class="fa-solid fa-message"></i>
                                            ${ticket.unread_count > 0 ? '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.3em 0.5em;">' + (ticket.unread_count > 99 ? '99+' : ticket.unread_count) + '</span>' : ''}
                                        </a>
                                        <a href="/user/tickets/${ticket.id}" class="chat-btn-modern" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
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
