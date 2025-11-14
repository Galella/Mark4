@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('content-header', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    @if (!Auth::user()->isAdminOutlet())
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box -->
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-store"></i></span>
                    <div class="info-box-content">
                        <span
                            class="info-box-text">{{ Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() ? 'Outlets' : 'Area Outlets' }}</span>
                        <span class="info-box-number">{{ $totalOutlets ?? 0 }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <span class="progress-description">
                            <a href="{{ route('outlets.index') }}">View details</a>
                        </span>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box -->
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span
                            class="info-box-text">{{ Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() ? 'Users' : 'Area Users' }}</span>
                        <span class="info-box-number">{{ $totalUsers ?? 0 }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <span class="progress-description">
                            <a href="{{ route('users.index') }}">View details</a>
                        </span>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box -->
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-calendar-day"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Income</span>
                        <span class="info-box-number" id="today-income-stat">Rp 0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <span class="progress-description">Total income today</span>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box -->
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-bullseye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Progress Target</span>
                        <span class="info-box-number">
                            @if (isset($targetProgressData))
                                {{ round($targetProgressData['progress_percentage'], 2) }}%
                            @else
                                0%
                            @endif
                        </span>
                        <div class="progress">
                            <div class="progress-bar"
                                style="width: @if (isset($targetProgressData)) {{ $targetProgressData['progress_percentage'] }} @else 0 @endif%; 
                             background-color: @if (isset($targetProgressData) && $targetProgressData['progress_percentage'] >= 100) #28a745 @else #007bff @endif;">
                            </div>
                        </div>
                        <span class="progress-description">
                            @if (isset($targetProgressData) && $targetProgressData['is_achieved'])
                                <span class="text-success"><i class="fas fa-check-circle"></i> Target achieved</span>
                            @else
                                <span class="text-warning"><i class="fas fa-exclamation-circle"></i> Pending</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <!-- ./col -->

        </div>
    @else
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box for daily colly -->
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Colly</span>
                        <span class="info-box-number" id="today-colly">0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box for daily weight -->
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-weight"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Weight</span>
                        <span class="info-box-number" id="today-weight">0.00 kg</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box for daily income -->
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Income</span>
                        <span class="info-box-number" id="today-income">Rp 0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info box for current month progress -->
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-bullseye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Progress Target</span>
                        <span class="info-box-number">
                            @if (isset($targetProgressData))
                                {{ round($targetProgressData['progress_percentage'], 2) }}%
                            @else
                                0%
                            @endif
                        </span>
                        <div class="progress">
                            <div class="progress-bar"
                                style="width: @if (isset($targetProgressData)) {{ $targetProgressData['progress_percentage'] }} @else 0 @endif%; 
                             background-color: @if (isset($targetProgressData) && $targetProgressData['progress_percentage'] >= 100) #28a745 @else #007bff @endif;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./col -->
        </div>
    @endif



    </div>

    <div class="row">
        <!-- Todo Section - Available for all roles -->
        <div class="col-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Tasks</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#todoModal">
                            <i class="fas fa-plus"></i> Add Task
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Todo List -->
                    <div id="todoList">
                        <!-- Todos will be loaded here -->
                        <p class="text-muted">Loading tasks...</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Income Charts Row for non-admin-outlet -->
        @if (!Auth::user()->isAdminOutlet())
            <div class="col-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Income Trend (Last 7 Days)</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-responsive">
                            <canvas id="incomeTrendChart" height="200" style="height: 200px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Income per Month</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="incomeByOutletPerMonthChart" height="200" style="height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Show stacked bar chart for all users -->

        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Income by Moda per Month</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="incomeByModaPerMonthChart" height="200" style="height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Todo Modal -->
    <div class="modal fade" id="todoModal" tabindex="-1" role="dialog" aria-labelledby="todoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="todoModalLabel">Add New Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="todoForm">
                        @csrf
                        <div class="form-group">
                            <label for="todoTitle">Task Title *</label>
                            <input type="text" class="form-control" id="todoTitle" name="title"
                                placeholder="Enter task title" required>
                        </div>
                        <div class="form-group">
                            <label for="todoDescription">Description (Optional)</label>
                            <textarea class="form-control" id="todoDescription" name="description" rows="3"
                                placeholder="Enter task description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="todoDueDate">Due Date (Optional)</label>
                            <input type="date" class="form-control" id="todoDueDate" name="due_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTodoBtn">Add Task</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .todo-item {
            border-left: 3px solid #007bff;
        }

        .todo-item.completed {
            border-left-color: #28a745;
        }

        .text-decoration-line-through {
            text-decoration: line-through !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(function() {
            // Only initialize org chart for non-admin-outlet users
            @if (!Auth::user()->isAdminOutlet())
                var orgChartElement = $('#orgChart');
                if (orgChartElement.length > 0) {
                    var areaChartCanvas = orgChartElement.get(0).getContext('2d');

                    var chartData = @json($chartData ?? ['labels' => [], 'data' => []]);

                    var areaChartData = {
                        labels: chartData.labels || [],
                        datasets: [{
                            label: 'Count',
                            backgroundColor: 'rgba(60,141,188,0.9)',
                            borderColor: 'rgba(60,141,188,0.8)',
                            pointRadius: false,
                            pointColor: '#3b8bba',
                            pointStrokeColor: 'rgba(60,141,188,1)',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: 'rgba(60,141,188,1)',
                            data: chartData.data || []
                        }]
                    }

                    var areaChartOptions = {
                        maintainAspectRatio: false,
                        responsive: true,
                        legend: {
                            display: false
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                }
                            },
                            y: {
                                grid: {
                                    display: false,
                                },
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                }
                            }
                        }
                    }

                    new Chart(areaChartCanvas, {
                        type: 'bar',
                        data: areaChartData,
                        options: areaChartOptions
                    })
                }
            @endif

            // Function to format currency
            function formatCurrency(amount) {
                // Convert to number if it's not already a number
                const amountNum = typeof amount === 'number' ? amount : parseFloat(amount);
                // Check if the conversion was successful
                if (isNaN(amountNum)) {
                    return 'Rp 0';
                }
                return 'Rp ' + amountNum.toLocaleString('id-ID');
            }

            // Function to format weight
            function formatWeight(weight) {
                // Convert to number if it's not already a number
                const weightNum = typeof weight === 'number' ? weight : parseFloat(weight);
                // Check if the conversion was successful
                if (isNaN(weightNum)) {
                    return '0.00 kg';
                }
                return weightNum.toLocaleString('id-ID') + ' kg';
            }

            // Initialize charts for income data (for all users)
            let incomeTrendChart, incomeByModaChart, incomeByOutletChart, incomeByModaPerMonthChart;

            function updateIncomeTrendChart(data) {
                if (typeof incomeTrendChart !== 'undefined' && incomeTrendChart) {
                    incomeTrendChart.destroy();
                }

                const ctxTrend = document.getElementById('incomeTrendChart');
                if (ctxTrend) {
                    const ctxTrendCtx = ctxTrend.getContext('2d');
                    incomeTrendChart = new Chart(ctxTrendCtx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Income',
                                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                                borderColor: 'rgba(60, 141, 188, 0.8)',
                                pointRadius: false,
                                pointColor: '#3b8bba',
                                pointStrokeColor: 'rgba(60, 141, 188, 1)',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(60, 141, 188, 1)',
                                data: data.data
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false,
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            function updateIncomeByModaChart(data) {
                if (typeof incomeByModaChart !== 'undefined' && incomeByModaChart) {
                    incomeByModaChart.destroy();
                }

                const ctxModa = document.getElementById('incomeByModaChart');
                if (ctxModa) {
                    const ctxModaCtx = ctxModa.getContext('2d');
                    incomeByModaChart = new Chart(ctxModaCtx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: [
                                    '#007bff',
                                    '#28a745',
                                    '#dc3545',
                                    '#ffc107',
                                    '#17a2b8',
                                    '#6f42c1',
                                    '#fd7e14',
                                    '#6c757d'
                                ],
                                borderColor: 'transparent'
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                position: 'bottom'
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var label = data.labels[tooltipItem.index] || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'Rp ' + data.datasets[0].data[tooltipItem.index]
                                            .toLocaleString('id-ID');
                                        return label;
                                    }
                                }
                            }
                        }
                    });
                }
            }

            function updateIncomeByOutletChart(data) {
                if (typeof incomeByOutletChart !== 'undefined' && incomeByOutletChart) {
                    incomeByOutletChart.destroy();
                }

                const ctxOutlet = document.getElementById('incomeByOutletChart');
                if (ctxOutlet) {
                    const ctxOutletCtx = ctxOutlet.getContext('2d');
                    incomeByOutletChart = new Chart(ctxOutletCtx, {
                        type: 'horizontalBar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Income',
                                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                                borderColor: 'rgba(60, 141, 188, 0.8)',
                                pointRadius: false,
                                pointColor: '#3b8bba',
                                pointStrokeColor: 'rgba(60, 141, 188, 1)',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(60, 141, 188, 1)',
                                data: data.data
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false,
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Initialize stacked bar chart for income by outlet per month (for all users)
            let incomeByOutletPerMonthChart;

            // Function to update income by outlet per month chart
            function updateIncomeByOutletPerMonthChart() {
                $.get('{{ route('dashboard.income-by-outlet-per-month') }}', function(data) {
                    if (typeof incomeByOutletPerMonthChart !== 'undefined' && incomeByOutletPerMonthChart) {
                        incomeByOutletPerMonthChart.destroy();
                    }

                    const ctxOutletPerMonth = document.getElementById('incomeByOutletPerMonthChart');
                    if (ctxOutletPerMonth) {
                        const ctxOutletPerMonthCtx = ctxOutletPerMonth.getContext('2d');
                        incomeByOutletPerMonthChart = new Chart(ctxOutletPerMonthCtx, {
                            type: 'bar',
                            data: data,
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                scales: {
                                    x: {
                                        // Remove stacked for simple bar chart
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += 'Rp ' + context.parsed.y
                                                        .toLocaleString('id-ID');
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }).fail(function(xhr, status, error) {
                    console.error('Error fetching income by outlet per month data:', error);
                });
            }

            // Function to update income by moda per month chart
            function updateIncomeByModaPerMonthChart() {
                $.get('{{ route('dashboard.income-by-moda-per-month') }}', function(data) {
                    if (typeof incomeByModaPerMonthChart !== 'undefined' && incomeByModaPerMonthChart) {
                        incomeByModaPerMonthChart.destroy();
                    }

                    const ctxModaPerMonth = document.getElementById('incomeByModaPerMonthChart');
                    if (ctxModaPerMonth) {
                        const ctxModaPerMonthCtx = ctxModaPerMonth.getContext('2d');
                        incomeByModaPerMonthChart = new Chart(ctxModaPerMonthCtx, {
                            type: 'bar',
                            data: data,
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                scales: {
                                    x: {
                                        stacked: true,
                                    },
                                    y: {
                                        stacked: true,
                                        ticks: {
                                            beginAtZero: true,
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += 'Rp ' + context.parsed.y
                                                        .toLocaleString('id-ID');
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }).fail(function(xhr, status, error) {
                    console.error('Error fetching income by moda per month data:', error);
                });
            }

            // Function to fetch and update income statistics
            function updateIncomeStats() {
                $.get('{{ route('dashboard.income-stats') }}', function(data) {
                    console.log('Dashboard data received:', data); // Debug log

                    // For admin outlet - update colly, weight, and income
                    if ($('#today-colly').length > 0) {
                        console.log('Updating admin outlet widgets');
                        console.log('Today\'s colly:', data.today_colly);
                        console.log('Today\'s weight:', data.today_weight);
                        console.log('Today\'s income:', data.today_income);

                        $('#today-colly').text((data.today_colly !== null && data.today_colly !==
                            undefined ?
                            data.today_colly : 0).toLocaleString('id-ID'));
                        $('#today-weight').text(formatWeight(data.today_weight !== null && data
                            .today_weight !== undefined ? data.today_weight : 0));
                        $('#today-income').text(formatCurrency(data.today_income !== null && data
                            .today_income !== undefined ? data.today_income : 0));
                    }

                    // For other roles - update income stats
                    else if ($('#today-income-stat').length > 0) {
                        console.log('Updating other role widgets');
                        $('#today-income-stat').text(formatCurrency(data.today_income !== null && data
                            .today_income !== undefined ? data.today_income : 0));
                        $('#week-income-stat').text(formatCurrency(data.week_income !== null && data
                            .week_income !== undefined ? data.week_income : 0));
                        $('#month-income-stat').text(formatCurrency(data.month_income !== null && data
                            .month_income !== undefined ? data.month_income : 0));
                        $('#total-income-stat').text(formatCurrency(data.total_income !== null && data
                            .total_income !== undefined ? data.total_income : 0));

                        // Update income trend chart
                        updateIncomeTrendChart(data.income_trend);

                        // Update income by moda chart
                        updateIncomeByModaChart(data.income_by_moda);

                        // Update income by moda chart
                        updateIncomeByModaChart(data.income_by_moda);

                        // Update income by outlet per month chart
                        updateIncomeByOutletPerMonthChart();
                    }

                    // Update income by moda per month chart for all users
                    updateIncomeByModaPerMonthChart();
                }).fail(function(xhr, status, error) {
                    console.error('Error fetching dashboard data:', error);
                    console.log('Response:', xhr.responseText);
                });
            }

            // Update all charts and data on page load
            updateIncomeStats();
            updateIncomeByModaPerMonthChart(); // Initialize the moda stacked bar chart
            updateIncomeByOutletPerMonthChart(); // Initialize the outlet stacked bar chart

            // Update data every 5 minutes
            setInterval(updateIncomeStats, 300000);
            setInterval(updateIncomeByModaPerMonthChart, 300000);
            setInterval(updateIncomeByOutletPerMonthChart, 300000);

            // Initialize Target Achievement Chart
            @if (isset($targetProgressData))
                var targetChartElement = $('#targetAchievementChart');
                if (targetChartElement.length > 0) {
                    var targetChartCanvas = targetChartElement.get(0).getContext('2d');
                    var targetChartData = {
                        labels: ['Actual Income', 'Target Amount'],
                        datasets: [{
                            label: 'Amount (Rp)',
                            backgroundColor: ['rgba(40, 167, 69, 0.8)',
                                'rgba(0, 123, 255, 0.8)'
                            ], // Green for actual, Blue for target
                            borderColor: ['rgba(40, 167, 69, 1)', 'rgba(0, 123, 255, 1)'],
                            borderWidth: 1,
                            data: [{{ $targetProgressData['actual_income'] }},
                                {{ $targetProgressData['target_amount'] }}
                            ]
                        }]
                    }

                    var targetChartOptions = {
                        maintainAspectRatio: false,
                        responsive: true,
                        legend: {
                            display: true
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }

                    new Chart(targetChartCanvas, {
                        type: 'bar',
                        data: targetChartData,
                        options: targetChartOptions
                    })
                }
            @endif
        })

        // Todo functionality
        $(function() {
            // Load todos when page loads
            loadTodos();

            // Handle todo form submission via modal button
            $('#saveTodoBtn').on('click', function(e) {
                const formData = {
                    title: $('#todoTitle').val(),
                    description: $('#todoDescription').val(),
                    due_date: $('#todoDueDate').val(),
                    _token: $('input[name="_token"]').val()
                };

                $.ajax({
                    url: '{{ route('todos.store') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#todoForm')[0].reset();
                        $('#todoModal').modal('hide'); // Close the modal
                        loadTodos(); // Reload the todo list
                        toastr.success('Task added successfully!');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding todo:', xhr.responseText);
                        toastr.error('Error adding task. Please try again.');
                    }
                });
            });

            // Clear form when modal is closed
            $('#todoModal').on('hidden.bs.modal', function() {
                $('#todoForm')[0].reset();
            });

            // Function to load todos
            function loadTodos() {
                $.ajax({
                    url: '{{ route('todos.index') }}',
                    method: 'GET',
                    success: function(todos) {
                        displayTodos(todos);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading todos:', xhr.responseText);
                        $('#todoList').html(
                            '<p class="text-danger">Error loading tasks. Please refresh the page.</p>'
                        );
                    }
                });
            }

            // Function to display todos
            function displayTodos(todos) {
                let todoHtml = '';

                if (todos.length === 0) {
                    todoHtml = '<p class="text-muted">No tasks yet. Click "Add Task" to get started!</p>';
                } else {
                    todos.forEach(function(todo) {
                        const completedClass = todo.completed ? 'text-decoration-line-through text-muted' :
                            '';
                        const completedText = todo.completed ? 'Completed' : 'Pending';
                        const completedBadge = todo.completed ? 'success' : 'warning';
                        const dueDate = todo.due_date ? new Date(todo.due_date).toLocaleDateString() :
                            'No due date';

                        todoHtml += `
                            <div class="todo-item card mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="${completedClass} mb-0">${todo.title}</h6>
                                            ${todo.description ? '<small class="text-muted">' + todo.description + '</small>' : ''}
                                        </div>
                                        <div>
                                            <span class="badge badge-${completedBadge} mr-2">${completedText}</span>
                                            <span class="text-muted mr-2">${dueDate}</span>
                                            <button class="btn btn-sm btn-outline-primary toggle-todo mr-1" data-id="${todo.id}">
                                                ${todo.completed ? '<i class="fas fa-undo"></i>' : '<i class="fas fa-check"></i>'}
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-todo" data-id="${todo.id}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#todoList').html(todoHtml);
            }

            // Handle toggle completion
            $(document).on('click', '.toggle-todo', function() {
                const todoId = $(this).data('id');

                $.ajax({
                    url: `{{ url('todos') }}/${todoId}/toggle`,
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        loadTodos(); // Reload the todo list
                        toastr.success('Task updated successfully!');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error toggling todo:', xhr.responseText);
                        toastr.error('Error updating task. Please try again.');
                    }
                });
            });

            // Handle delete todo
            $(document).on('click', '.delete-todo', function() {
                const todoId = $(this).data('id');

                if (confirm('Are you sure you want to delete this task?')) {
                    $.ajax({
                        url: `{{ url('todos') }}/${todoId}`,
                        method: 'DELETE',
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            loadTodos(); // Reload the todo list
                            toastr.success('Task deleted successfully!');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting todo:', xhr.responseText);
                            toastr.error('Error deleting task. Please try again.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
