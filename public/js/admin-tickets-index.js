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

// Real-time updates via WebSockets (see realtime-handlers.js + realtime-echo.js)
