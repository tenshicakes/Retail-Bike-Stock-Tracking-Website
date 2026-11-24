<x-layout>

    <!-- Initial Chart Data Payload -->
    <div id="homeChartData" data-charts='@json($chartData)' class="d-none"></div>

    <style>
        @keyframes spinRefresh {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .icon-spin {
            display: inline-block;
            animation: spinRefresh 0.75s linear infinite;
        }

        .chart-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
        }

        .chart-card:hover {
            transform: translateY(-2px);
        }

        .chart-card.chart-refreshing {
            opacity: 0.55;
            transform: scale(0.99);
        }

        .chart-canvas-wrapper {
            position: relative;
            height: 250px;
            width: 100%;
        }
    </style>

    <!-- ==================== 1. ANALYTICS CHARTS SECTION (ABOVE EVERYTHING) ==================== -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="fw-bolder text-dark m-0">Inventory Analytics Overview</h4>
                <small class="text-muted">Live breakdown of stock levels, valuation, and 7-day movement</small>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline">
                    Updated: <strong id="chartLastUpdated">{{ now()->format('h:i:s A') }}</strong>
                </span>
                <button type="button" class="btn btn-dark fw-bold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" id="btnRefreshCharts">
                    <i class="bi bi-arrow-clockwise fs-5" id="refreshChartIcon"></i>
                    <span>Refresh Charts</span>
                </button>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Chart 1: Bar Chart (Stock Quantity by Category) -->
            <div class="col-12 col-lg-6">
                <div class="card home-stat-card chart-card border-0 rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Stock Units by Category</h6>
                            <small class="text-muted">Total available units per product category</small>
                        </div>
                        <span class="badge bg-light text-dark border">Bar Chart</span>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="barChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Pie Chart (Inventory Value Share by Category) -->
            <div class="col-12 col-lg-6">
                <div class="card home-stat-card chart-card border-0 rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Inventory Valuation Share (₱)</h6>
                            <small class="text-muted">Total stock value distribution across categories</small>
                        </div>
                        <span class="badge bg-light text-dark border">Pie Chart</span>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="pieChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 3: Line Chart (7-Day Stock-In vs Stock-Out Trend) -->
            <div class="col-12 col-lg-6">
                <div class="card home-stat-card chart-card border-0 rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">7-Day Stock Movement Trend</h6>
                            <small class="text-muted">Daily Stock-In vs. Stock-Out item quantities</small>
                        </div>
                        <span class="badge bg-light text-dark border">Line Chart</span>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="lineChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 4: Doughnut Chart (Inventory Stock Health) -->
            <div class="col-12 col-lg-6">
                <div class="card home-stat-card chart-card border-0 rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Inventory Stock Health</h6>
                            <small class="text-muted">Proportion of Healthy, Low Stock, and Out-of-Stock items</small>
                        </div>
                        <span class="badge bg-light text-dark border">Doughnut Chart</span>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="doughnutChartCanvas"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ==================== 2. DIGITAL CLOCK & DATE ==================== -->
    <div class="mb-4">
        <h1 class="fw-bolder text-dark display-5 mb-0" id="digitalClock" style="letter-spacing: -1px;">
            00:00:00 AM
        </h1>
        <h5 class="text-muted fw-semibold" id="currentDate">
            {{ now()->format('F j, Y l') }}
        </h5>
    </div>

    <!-- ==================== 3. STAT COUNTERS ==================== -->
    <div class="row g-4 mb-5">
        
        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-bicycle fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Products Listed</h6>
                        <h2 class="fw-bolder mb-0">{{ $totalProducts }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Low Stocks</h6>
                        <h2 class="fw-bolder mb-0">{{ $lowStocks }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-x-octagon-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">No Stocks</h6>
                        <h2 class="fw-bolder mb-0">{{ $noStocks }}</h2>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ==================== 4. ALL PRODUCTS TABLE ==================== -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold m-0">All Products</h4>
        </div>

        <div class="card-body p-0">
            <x-product-table :products="$products" />
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script type="module">
        // Digital Clock
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12;
            
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Charts Initialization & Animated Refresh
        $(document).ready(function() {
            const initialData = $('#homeChartData').data('charts');

            const palette = {
                navy: '#0c112c',
                royalBlue: '#243179',
                stockInBlue: '#97b9f1',
                stockInBorder: '#7ea9e5',
                stockOutSlate: '#92b3d2',
                stockOutBorder: '#7ca1c2',
                warningAmber: '#f59e0b',
                dangerCoral: '#ef4444'
            };

            const sharedAnimation = {
                duration: 900,
                easing: 'easeOutQuart'
            };

            // 1. Bar Chart
            const barChart = new Chart(document.getElementById('barChartCanvas'), {
                type: 'bar',
                data: {
                    labels: initialData.bar.labels,
                    datasets: [{
                        label: 'Units in Stock',
                        data: initialData.bar.values,
                        backgroundColor: [palette.royalBlue, palette.stockInBlue, palette.stockOutSlate],
                        borderColor: [palette.navy, palette.stockInBorder, palette.stockOutBorder],
                        borderWidth: 1.5,
                        borderRadius: 8,
                        maxBarThickness: 56
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: sharedAnimation,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // 2. Pie Chart
            const pieChart = new Chart(document.getElementById('pieChartCanvas'), {
                type: 'pie',
                data: {
                    labels: initialData.pie.labels,
                    datasets: [{
                        data: initialData.pie.values,
                        backgroundColor: [palette.navy, palette.stockInBlue, palette.stockOutSlate],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 950,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { usePointStyle: true, padding: 16 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const val = Number(ctx.parsed || 0).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                    return ` ${ctx.label}: ₱${val}`;
                                }
                            }
                        }
                    }
                }
            });

            // 3. Line Chart
            const lineChart = new Chart(document.getElementById('lineChartCanvas'), {
                type: 'line',
                data: {
                    labels: initialData.line.labels,
                    datasets: [
                        {
                            label: 'Stock-In Qty',
                            data: initialData.line.stockIn,
                            borderColor: palette.royalBlue,
                            backgroundColor: 'rgba(151, 185, 241, 0.25)',
                            borderWidth: 2.5,
                            pointBackgroundColor: palette.royalBlue,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.35
                        },
                        {
                            label: 'Stock-Out Qty',
                            data: initialData.line.stockOut,
                            borderColor: palette.stockOutBorder,
                            backgroundColor: 'rgba(146, 179, 210, 0.18)',
                            borderWidth: 2.5,
                            borderDash: [5, 4],
                            pointBackgroundColor: palette.stockOutSlate,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: false,
                            tension: 0.35
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: sharedAnimation,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // 4. Doughnut Chart
            const doughnutChart = new Chart(document.getElementById('doughnutChartCanvas'), {
                type: 'doughnut',
                data: {
                    labels: initialData.doughnut.labels,
                    datasets: [{
                        data: initialData.doughnut.values,
                        backgroundColor: [palette.royalBlue, palette.warningAmber, palette.dangerCoral],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 950,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { usePointStyle: true, padding: 16 }
                        }
                    }
                }
            });

            // Refresh Button Click Handler with Spin & Replay Animation
            $('#btnRefreshCharts').on('click', function() {
                const btn = $(this);
                const icon = $('#refreshChartIcon');
                const cards = $('.chart-card');

                btn.prop('disabled', true);
                icon.addClass('icon-spin');
                cards.addClass('chart-refreshing');

                $.ajax({
                    url: '{{ route("dashboard.charts") }}',
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        const c = res.charts;

                        // Update datasets
                        barChart.data.labels = c.bar.labels;
                        barChart.data.datasets[0].data = c.bar.values;

                        pieChart.data.labels = c.pie.labels;
                        pieChart.data.datasets[0].data = c.pie.values;

                        lineChart.data.labels = c.line.labels;
                        lineChart.data.datasets[0].data = c.line.stockIn;
                        lineChart.data.datasets[1].data = c.line.stockOut;

                        doughnutChart.data.labels = c.doughnut.labels;
                        doughnutChart.data.datasets[0].data = c.doughnut.values;

                        // Reset and replay Chart.js animations from zero
                        [barChart, pieChart, lineChart, doughnutChart].forEach(chart => {
                            chart.reset();
                            chart.update();
                        });

                        $('#chartLastUpdated').text(res.updatedAt);

                        if (window.showToast) {
                            window.showToast('Analytics charts refreshed!', 'success');
                        }
                    },
                    error: function() {
                        if (window.showToast) {
                            window.showToast('Failed to refresh chart data.', 'error');
                        }
                    },
                    complete: function() {
                        setTimeout(() => {
                            btn.prop('disabled', false);
                            icon.removeClass('icon-spin');
                            cards.removeClass('chart-refreshing');
                        }, 350);
                    }
                });
            });
        });
    </script>
    @endpush

</x-layout>