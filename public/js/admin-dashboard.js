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
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '80%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 11, ...fonts }
                    }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#111',
                    bodyColor: '#666',
                    borderColor: 'rgba(0,0,0,0.05)',
                    borderWidth: 1,
                    padding: 12,
                    usePointStyle: true,
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.raw} Tickets`
                    }
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

// Real-time Dashboard Summary Discovery
setInterval(async () => {
    try {
        const config = window.AdminDashboardConfig;
        if (!config) return;

        const url = new URL(config.routes.newData, window.location.origin);
        if (config.currentSelection.date) {
            url.searchParams.set('date', config.currentSelection.date);
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.counts) {
            const counts = data.counts;
            updateValueWithEffect('open-count', counts.open);
            updateValueWithEffect('progress-count', counts.in_progress);
            updateValueWithEffect('closed-count', counts.closed);
            updateValueWithEffect('total-count', counts.total);

            // Real-time Chart Updates (Only if viewing current month/year)
            const isCurrentMonth = config.currentSelection.month == config.currentDate.month && config.currentSelection.year == config.currentDate.year;
            
            if (isCurrentMonth) {
                if (window.distributionChart && data.monthly_counts) {
                    const m = data.monthly_counts;
                    distributionChart.data.datasets[0].data = [m.open, m.in_progress, m.closed];
                    distributionChart.update();
                    
                    // Update center total
                    const centerVal = document.querySelector('.stat-center-value');
                    if (centerVal) {
                        centerVal.textContent = m.open + m.in_progress + m.closed;
                    }
                }

                if (window.trendChart && data.today_label) {
                    const todayIdx = trendChart.data.labels.indexOf(data.today_label);
                    if (todayIdx !== -1) {
                        trendChart.data.datasets[0].data[todayIdx] = counts.open;
                        trendChart.data.datasets[1].data[todayIdx] = counts.in_progress;
                        trendChart.data.datasets[2].data[todayIdx] = counts.closed;
                        trendChart.update('none'); // Update without animation for smoothness
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error fetching summary data:', error);
    }
}, 10000);

function updateValueWithEffect(id, newValue) {
    const el = document.getElementById(id);
    if (!el) return;
    const currentVal = parseInt(el.textContent);
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
