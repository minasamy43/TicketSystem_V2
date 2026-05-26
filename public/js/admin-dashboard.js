document.addEventListener('DOMContentLoaded', function () {
    // Shared config
    const fonts = { family: "'Outfit', sans-serif" };
    const config = window.AdminDashboardConfig;

    // 1. Distribution Chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    window.distributionChart = new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Open', 'In Progress', 'Closed'],
            datasets: [{
                data: [config.stats.allOpen, config.stats.allInProgress, config.stats.allClosed],
                backgroundColor: ['#dc3545', '#d4af53', '#198754'],
                hoverBackgroundColor: ['#e35d6a', '#dfc276', '#20ac6b'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '80%',
            responsive: true,
            maintainAspectRatio: false,
            onHover: (evt, elements) => {
                const centerValue = document.getElementById('distribution-center-total');
                const centerLabel = document.getElementById('distribution-center-label');
                if (!centerValue || !centerLabel) return;

                const data = window.distributionChart.data.datasets[0].data;

                if (elements.length > 0) {
                    const idx = elements[0].index;
                    centerValue.textContent = data[idx];
                    centerLabel.textContent = window.distributionChart.data.labels[idx];
                    centerValue.style.color = window.distributionChart.data.datasets[0].backgroundColor[idx];
                } else {
                    centerValue.textContent = data[0] + data[1] + data[2];
                    centerLabel.textContent = 'Total';
                    centerValue.style.color = '#1a1a1a';
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: false, // Using center text instead for a cleaner look
                }
            }
        }
    });

    // 2. Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');

    // Create gradients
    const gradOpen = trendCtx.createLinearGradient(0, 0, 0, 300);
    gradOpen.addColorStop(0, 'rgba(220, 53, 69, 0.15)');
    gradOpen.addColorStop(1, 'rgba(220, 53, 69, 0)');

    const gradProgress = trendCtx.createLinearGradient(0, 0, 0, 300);
    gradProgress.addColorStop(0, 'rgba(212, 175, 83, 0.15)');
    gradProgress.addColorStop(1, 'rgba(212, 175, 83, 0)');

    const gradClosed = trendCtx.createLinearGradient(0, 0, 0, 300);
    gradClosed.addColorStop(0, 'rgba(25, 135, 84, 0.15)');
    gradClosed.addColorStop(1, 'rgba(25, 135, 84, 0)');

    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: config.chartData.labels,
            datasets: [
                {
                    label: 'Open',
                    data: config.chartData.open,
                    borderColor: '#dc3545',
                    backgroundColor: gradOpen,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#dc3545',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'In Progress',
                    data: config.chartData.inProgress,
                    borderColor: '#d4af53',
                    backgroundColor: gradProgress,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#d4af53',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Closed',
                    data: config.chartData.closed,
                    borderColor: '#198754',
                    backgroundColor: gradClosed,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#198754',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#111',
                    bodyColor: '#666',
                    borderColor: 'rgba(0,0,0,0.05)',
                    borderWidth: 1,
                    padding: 12,
                    usePointStyle: true,
                    font: fonts
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: fonts, color: '#999' } },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: fonts, color: '#999' },
                    grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.03)' }
                }
            }
        }
    });
});

// Real-time dashboard updates via WebSockets (see realtime-handlers.js)
