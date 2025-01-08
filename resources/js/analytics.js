const initAnalytics = () => {
    // Plays Timeline Chart
    const playsCtx = document.getElementById('playsChart').getContext('2d');
    new Chart(playsCtx, {
        type: 'line',
        data: {
            labels: playsData.map(item => item.date),
            datasets: [{
                label: 'Daily Plays',
                data: playsData.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Demographics Chart
    const demoCtx = document.getElementById('demographicsChart').getContext('2d');
    new Chart(demoCtx, {
        type: 'doughnut',
        data: {
            labels: ['18-24', '25-34', '35-44', '45+'],
            datasets: [{
                data: [30, 40, 20, 10],
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(249, 115, 22)',
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
};


