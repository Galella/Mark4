@extends('layouts.adminlte')

@section('title', 'Outlet Performance Dashboard')

@section('content-header', 'Outlet Performance Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Outlet Performance Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-md-3">
            <label for="year">Year:</label>
            <select name="year" id="year" class="form-control">
                @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                    <option value="{{ $year }}" {{ $year == request('year') ? 'selected' : '' }}>
                        {{ $year }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="month">Month:</label>
            <select name="month" id="month" class="form-control">
                <option value="" {{ request('month') === null || request('month') === '' ? 'selected' : '' }}>All Months</option>
                @foreach ([
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ] as $monthNum => $monthName)
                    <option value="{{ $monthNum }}" {{ $monthNum == request('month') ? 'selected' : '' }}>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <div>
                <button type="button" class="btn btn-primary" id="filterBtn">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button type="button" class="btn btn-default ml-1" id="resetBtn">
                    <i class="fas fa-sync-alt"></i> Reset
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <div>
                <a href="{{ route('outlet-performance.index') }}" class="btn btn-info">
                    <i class="fas fa-table"></i> Detailed View
                </a>
            </div>
        </div>
    </div>

    <!-- Info Boxes -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <!-- Total Active Outlets -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalActiveOutlets }}</h3>
                    <p>Total Active Outlets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>
        <!-- Outlets On Track -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $outletsOnTrack }}</h3>
                    <p>Outlets On Track</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <!-- Income -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Rp {{ number_format($totalIncome / 1000000, 1, '.', '.') }}M</h3>
                    <p>Total Income</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <!-- Average Achievement -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($avgAchievementRate, 1) }}%</h3>
                    <p>Avg Achievement</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-md-8">
            <!-- Top Performing Outlets Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Performing Outlets</h3>
                </div>
                <div class="card-body">
                    <canvas id="topOutletsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Performance Distribution -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performance Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="performanceDistributionChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mt-4">
        <div class="col-md-6">
            <!-- Income vs Target Comparison -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Income vs Target</h3>
                </div>
                <div class="card-body">
                    <canvas id="incomeVsTargetChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Achievement Rate Distribution -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Achievement Rate Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="achievementRateChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        $(function() {
            // Filter button click event
            $('#filterBtn').on('click', function() {
                var year = $('#year').val();
                var month = $('#month').val();

                // Construct URL with query parameters
                var url = '{{ route('outlet-performance.dashboard') }}';
                var params = [];

                if (year) params.push('year=' + year);
                if (month) params.push('month=' + month);

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.location.href = url;
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                window.location.href = '{{ route('outlet-performance.dashboard') }}';
            });

            // Prepare data for charts
            var chartData = @json($chartData);
            var monthName = @json($month ? ($availableMonths[$month] ?? 'Unknown') : 'All Months');
            var yearValue = @json($year);

            // Format currency function
            function formatCurrency(amount) {
                return 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Top Performing Outlets Chart (Horizontal Bar)
            var topOutletsCtx = document.getElementById('topOutletsChart').getContext('2d');
            var topOutletsChart = new Chart(topOutletsCtx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.outlet_name),
                    datasets: [
                        {
                            label: 'Target',
                            data: chartData.map(item => item.target),
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Achievement',
                            data: chartData.map(item => item.income),
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top Performing Outlets: Income vs Target (' + monthName + ' ' + yearValue + ')'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    }
                }
            });

            // Income vs Target Chart (Stacked Bar)
            var incomeVsTargetCtx = document.getElementById('incomeVsTargetChart').getContext('2d');
            var incomeVsTargetChart = new Chart(incomeVsTargetCtx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.outlet_name),
                    datasets: [
                        {
                            label: 'Target',
                            data: chartData.map(item => item.target),
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Achievement',
                            data: chartData.map(item => item.income),
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Income vs Target Comparison (' + monthName + ' ' + yearValue + ')'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    }
                }
            });

            // Achievement Rate Chart
            var achievementRateCtx = document.getElementById('achievementRateChart').getContext('2d');
            var achievementRateChart = new Chart(achievementRateCtx, {
                type: 'radar',
                data: {
                    labels: chartData.map(item => item.outlet_name),
                    datasets: [
                        {
                            label: 'Achievement Rate (%)',
                            data: chartData.map(item => item.achievement_rate),
                            fill: true,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                        }
                    ]
                },
                options: {
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Achievement Rate Distribution (' + monthName + ' ' + yearValue + ')'
                        }
                    }
                }
            });

            // Performance Distribution Chart (Pie/Doughnut)
            var performanceDistributionCtx = document.getElementById('performanceDistributionChart').getContext('2d');
            
            // Calculate performance segments
            var excellent = chartData.filter(item => item.achievement_rate >= 90).length;
            var good = chartData.filter(item => item.achievement_rate >= 70 && item.achievement_rate < 90).length;
            var average = chartData.filter(item => item.achievement_rate >= 50 && item.achievement_rate < 70).length;
            var below = chartData.filter(item => item.achievement_rate < 50).length;
            
            var performanceDistributionChart = new Chart(performanceDistributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent (>90%)', 'Good (70-89%)', 'Average (50-69%)', 'Below (0-49%)'],
                    datasets: [{
                        data: [excellent, good, average, below],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',    // Excellent - Green
                            'rgba(0, 123, 255, 0.8)',    // Good - Blue
                            'rgba(255, 193, 7, 0.8)',    // Average - Yellow
                            'rgba(220, 53, 69, 0.8)'     // Below - Red
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',      // Excellent - Green
                            'rgba(0, 123, 255, 1)',      // Good - Blue
                            'rgba(255, 193, 7, 1)',      // Average - Yellow
                            'rgba(220, 53, 69, 1)'       // Below - Red
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Performance Distribution (' + monthName + ' ' + yearValue + ')'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endsection