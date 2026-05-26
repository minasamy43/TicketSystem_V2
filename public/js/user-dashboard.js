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
                window.location.href = `/agent/tickets/${ticketId}`;
            }
        });
    }
});

// Real-time updates via WebSockets for agent dashboard (see realtime-handlers.js)
