document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById('salesChart').getContext('2d');

    const monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const weekLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

    const regionColors = {
        JPN: 'rgba(192, 57, 43, 0.7)',
        PHL: 'rgba(255, 170, 0, 0.7)',
        AUS: 'rgba(39, 174, 96, 0.7)',
        USA: 'rgba(41, 128, 185, 0.7)'
    };


    let views = ['year', 'month', 'week'];
    let index = 0;
    let currentView = views[index];

    function buildDatasets(view) {
        let datasets = [{
            type: 'line',
            label: 'Total Sales ($)',
            data: salesDataSets[view].total,
            borderColor: 'rgba(44, 62, 80, 0.9)',
            backgroundColor: 'rgba(44, 62, 80, 0.15)',
            tension: 0.3,
            yAxisID: 'y'
        }];

        Object.keys(salesDataSets[view].regions).forEach(region => {
            datasets.push({
                type: 'bar',
                label: region,
                data: Object.values(salesDataSets[view].regions[region]),
                backgroundColor: regionColors[region],
                yAxisID: 'y1'
            });
        });

        return {
            labels: salesDataSets[view].labels,
            datasets
        };
    }

    // chart initilization
    let chart = new Chart(ctx, {
        data: buildDatasets(currentView),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left'
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    function updateChart() {
        currentView = views[index];
        document.getElementById('chartMode').innerText =
            currentView.charAt(0).toUpperCase() + currentView.slice(1);
        chart.data = buildDatasets(currentView);
        chart.update();
    }

    // click the left N right arrows to swith views
    document.getElementById('leftOverlay').addEventListener('click', () => {
        index = (index - 1 + views.length) % views.length;
        updateChart();
    });
    document.getElementById('rightOverlay').addEventListener('click', () => {
        index = (index + 1) % views.length;
        updateChart();
    });
});
