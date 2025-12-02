@extends('layouts.adminlte')

@section('title', $viewMode === 'dashboard' ? 'Performance Dashboard' : 'Target vs Realisasi Report')

@section('content-header', $viewMode === 'dashboard' ? 'Performance Dashboard' : 'Target vs Realisasi Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">{{ $viewMode === 'dashboard' ? 'Performance Dashboard' : 'Target vs Realisasi Report' }}</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- View Toggle -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group float-right" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'dashboard']) }}"
                       class="btn {{ $viewMode === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-chart-bar"></i> Dashboard View
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'detailed']) }}"
                       class="btn {{ $viewMode === 'detailed' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-table"></i> Detailed View
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Form Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="year">Year:</label>
                        <select name="year" id="year" class="form-control">
                            @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="month">Month:</label>
                        <select name="month" id="month" class="form-control">
                            <option value="" {{ request('month') === null || request('month') === '' || $selectedMonth === null || $selectedMonth === '' ? 'selected' : '' }}>All Months</option>
                            @foreach ($availableMonths as $monthNum => $monthName)
                                <option value="{{ $monthNum }}"
                                    {{ $selectedMonth == $monthNum ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="office">Office:</label>
                        <select name="office" id="office" class="form-control">
                            <option value="">All Offices</option>
                            @foreach ($offices as $office)
                                <option value="{{ $office->id }}"
                                    {{ $selectedOffice == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="outlet">Outlet:</label>
                        <select name="outlet" id="outlet" class="form-control">
                            <option value="">All Outlets</option>
                            @foreach ($allOutlets as $outlet)
                                <option value="{{ $outlet->id }}"
                                    {{ $selectedOutlet == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary" id="filterBtn">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button type="button" class="btn btn-default ml-1" id="resetBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="{{ route('reports.target-realization.export') }}" class="btn btn-success ml-1">
                                <i class="fas fa-file-excel"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex align-items-center">
                            <small class="text-muted">Current View:
                                <strong>{{ $viewMode === 'dashboard' ? 'Dashboard' : 'Detailed' }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($viewMode === 'dashboard' && $dashboardData)
            <!-- Dashboard View -->
            <!-- Info Boxes -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <!-- Total Active Outlets -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $dashboardData['totalActiveOutlets'] }}</h3>
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
                            <h3>{{ $dashboardData['outletsOnTrack'] }}</h3>
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
                            <h3>Rp {{ number_format($dashboardData['totalIncome'] / 1000000, 1, '.', '.') }}M</h3>
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
                            <h3>{{ number_format($dashboardData['avgAchievementRate'], 1) }}%</h3>
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
        @elseif($viewMode === 'detailed')
            <!-- Detailed Table View -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Target vs Realisasi Report</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'outlet_name', 'direction' => request('sort') == 'outlet_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                                    Nama Outlet
                                                    @if(request('sort') == 'outlet_name')
                                                        <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th>Periode</th>
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'target', 'direction' => request('sort') == 'target' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                                    Target
                                                    @if(request('sort') == 'target')
                                                        <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'realization', 'direction' => request('sort') == 'realization' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                                    Realisasi
                                                    @if(request('sort') == 'realization')
                                                        <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'progress', 'direction' => request('sort') == 'progress' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                                    Progres
                                                    @if(request('sort') == 'progress')
                                                        <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                                    Status
                                                    @if(request('sort') == 'status')
                                                        <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    @endif
                                                </a>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($reportData) && is_iterable($reportData))
                                            @foreach ($reportData as $data)
                                                <tr>
                                                    <td>{{ $data['outlet_name'] }}</td>
                                                    <td>{{ $selectedMonth ? ($availableMonths[$selectedMonth] ?? '') : 'All Months' }} {{ $selectedYear }}</td>
                                                    <td>Rp {{ number_format($data['target'], 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($data['realization'], 0, ',', '.') }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-2" style="min-width: 100px;">
                                                                <div class="progress-bar" role="progressbar"
                                                                    style="width: {{ min($data['progress'], 100) }}%;
                                                             background-color: {{ $data['progress'] >= 100 ? '#28a745' : ($data['progress'] >= 80 ? '#ffc107' : '#dc3545') }};">
                                                                </div>
                                                            </div>
                                                            <span class="text-nowrap">{{ round($data['progress'], 2) }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $data['status'] === 'Achieved' ? 'success' : ($data['status'] === 'On Track' ? 'warning' : 'danger') }}">
                                                            {{ $data['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">No data available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if(isset($reportData) && method_exists($reportData, 'hasPages') && $reportData->hasPages())
                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div class="table-info-text">
                                    Showing {{ $reportData->firstItem() }} to {{ $reportData->lastItem() }} of {{ $reportData->total() }} results
                                </div>
                                <ul class="pagination pagination-sm m-0">
                                    @php
                                        $currentSort = request('sort', 'progress');
                                        $currentDirection = request('direction', 'desc');
                                        $appendParams = ['sort' => $currentSort, 'direction' => $currentDirection];
                                    @endphp

                                    @if ($reportData->onFirstPage())
                                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $reportData->previousPageUrl() }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">&laquo;</a></li>
                                    @endif

                                    @php
                                        $start = max(1, $reportData->currentPage() - 2);
                                        $end = min($reportData->lastPage(), $reportData->currentPage() + 2);
                                    @endphp

                                    @if ($start > 1)
                                        <li class="page-item"><a class="page-link" href="{{ $reportData->url(1) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">1</a></li>
                                        @if ($start > 2)
                                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $reportData->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $reportData->url($i) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    @if ($end < $reportData->lastPage())
                                        @if ($end < $reportData->lastPage() - 1)
                                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                        @endif
                                        <li class="page-item"><a class="page-link" href="{{ $reportData->url($reportData->lastPage()) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">{{ $reportData->lastPage() }}</a></li>
                                    @endif

                                    @if ($reportData->hasMorePages())
                                        <li class="page-item"><a class="page-link" href="{{ $reportData->nextPageUrl() }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">&raquo;</a></li>
                                    @else
                                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                                    @endif
                                </ul>
                            </div>
                            @endif
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // Filter button click event - simplified
        document.getElementById('filterBtn').addEventListener('click', function() {
            var year = document.getElementById('year').value;
            var month = document.getElementById('month').value;
            var office = document.getElementById('office').value;
            var outlet = document.getElementById('outlet').value;

            // Get current sorting parameters
            var sort = '{{ request('sort', 'progress') }}';
            var direction = '{{ request('direction', 'desc') }}';
            var view = '{{ $viewMode }}'; // Preserve current view mode

            // Construct URL with query parameters
            var url = '{{ route('reports.target-realization.index') }}';
            var params = [];

            if (year) params.push('year=' + encodeURIComponent(year));
            // Always include month parameter, even if it's empty (for "All Months" selection)
            params.push('month=' + encodeURIComponent(month));
            if (office) params.push('office=' + encodeURIComponent(office));
            if (outlet) params.push('outlet=' + encodeURIComponent(outlet));
            if (sort) params.push('sort=' + encodeURIComponent(sort));
            if (direction) params.push('direction=' + encodeURIComponent(direction));
            if (view) params.push('view=' + encodeURIComponent(view));

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            window.location.href = url;
        });

        // Reset button click event
        document.getElementById('resetBtn').addEventListener('click', function() {
            var view = '{{ $viewMode }}'; // Preserve current view mode when resetting
            var url = '{{ route('reports.target-realization.index') }}';
            if (view) {
                url += '?view=' + encodeURIComponent(view);
            }
            window.location.href = url;
        });
    </script>

    @if($viewMode === 'dashboard' && $dashboardData)
        <!-- ChartJS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <script>
            // Format currency function
            function formatCurrency(amount) {
                return 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Prepare chart data from PHP variables
            var chartData = @json($dashboardData['chartData'] ?? []);
            var monthName = @json($monthNameForDashboard);
            var yearValue = @json($selectedYear);

            // Only initialize charts if there's data
            if (chartData && chartData.length > 0) {
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

                // Income vs Target Chart
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
            } else {
                console.log('No chart data available or chart data is empty');
            }
        </script>
    @endif
@endsection