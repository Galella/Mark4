@extends('layouts.adminlte')

@section('title', 'Outlet Performance')

@section('content-header', 'Outlet Performance Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Outlet Performance</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outlet Performance Report</h3>
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
                    <!-- Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label for="year">Year:</label>
                            <select name="year" id="year" class="form-control">
                                @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                    <option value="{{ $year }}" {{ $year == request('year') ? 'selected' : '' }}>
                                        {{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month">Month:</label>
                            <select name="month" id="month" class="form-control">
                                <option value="" {{ request('month') === null || request('month') === '' ? 'selected' : '' }}>All Months</option>
                                @foreach ($availableMonths as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}"
                                        {{ $monthNum == request('month') ? 'selected' : '' }}>
                                        {{ $monthName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="outlet">Outlet:</label>
                            <select name="outlet" id="outlet" class="form-control">
                                <option value="">All Outlets</option>
                                @foreach ($availableOutlets as $outlet)
                                    <option value="{{ $outlet->id }}"
                                        {{ $outlet->id == request('outlet') ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-primary" id="filterBtn">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button type="button" class="btn btn-default ml-1" id="resetBtn">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </button>
                                <a href="{{ route('outlet-performance.dashboard') }}" class="btn btn-info ml-1">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- /.filter form -->

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'outlet_name', 'direction' => request('sort') == 'outlet_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Outlet Name
                                            @if(request('sort') == 'outlet_name')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'outlet_type', 'direction' => request('sort') == 'outlet_type' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Type
                                            @if(request('sort') == 'outlet_type')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_income', 'direction' => request('sort') == 'total_income' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Income
                                            @if(request('sort') == 'total_income')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_target', 'direction' => request('sort') == 'total_target' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Target
                                            @if(request('sort') == 'total_target')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'achievement_rate', 'direction' => request('sort') == 'achievement_rate' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Achievement (%)
                                            @if(request('sort') == 'achievement_rate')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_colly', 'direction' => request('sort') == 'total_colly' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Colly
                                            @if(request('sort') == 'total_colly')
                                                <i class="fas {{ request('direction') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'performance_score', 'direction' => request('sort') == 'performance_score' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                            Score
                                            @if(request('sort') == 'performance_score')
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
                                @if(isset($paginator) && $paginator->count() > 0)
                                    @foreach ($paginator as $index => $data)
                                        <tr>
                                            <td>{{ $paginator->firstItem() + $index }}</td>
                                            <td>{{ $data['outlet_name'] }}</td>
                                            <td>{{ $data['outlet_type'] }}</td>
                                            <td>Rp {{ number_format($data['total_income'], 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($data['total_target'], 0, ',', '.') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2" style="min-width: 80px;">
                                                        <div class="progress-bar" role="progressbar"
                                                            style="width: {{ min($data['achievement_rate'], 100) }}%;
                                                     background-color: {{ $data['achievement_rate'] >= 100 ? '#28a745' : ($data['achievement_rate'] >= 80 ? '#28a745' : ($data['achievement_rate'] >= 60 ? '#ffc107' : '#dc3545')) }};">
                                                        </div>
                                                    </div>
                                                    <span class="text-nowrap">{{ number_format($data['achievement_rate'], 2) }}%</span>
                                                </div>
                                            </td>
                                            <td>{{ number_format($data['total_colly'], 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $data['performance_score'] >= 90 ? 'success' : ($data['performance_score'] >= 75 ? 'info' : ($data['performance_score'] >= 60 ? 'warning' : 'danger')) }}">
                                                    {{ number_format($data['performance_score'], 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $data['status'] === 'Excellent' || $data['status'] === 'Good' ? 'success' : ($data['status'] === 'Average' ? 'info' : ($data['status'] === 'Below Average' ? 'warning' : 'danger')) }}">
                                                    {{ $data['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No data available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($paginator) && $paginator->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="table-info-text">
                            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
                        </div>
                        <ul class="pagination pagination-sm m-0">
                            @php
                                $currentSort = request('sort', 'performance_score');
                                $currentDirection = request('direction', 'desc');
                            @endphp

                            @if ($paginator->onFirstPage())
                                <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">&laquo;</a></li>
                            @endif

                            @php
                                $start = max(1, $paginator->currentPage() - 2);
                                $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                            @endphp

                            @if ($start > 1)
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">1</a></li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $paginator->url($i) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if ($end < $paginator->lastPage())
                                @if ($end < $paginator->lastPage() - 1)
                                    <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">{{ $paginator->lastPage() }}</a></li>
                            @endif

                            @if ($paginator->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}&sort={{ $currentSort }}&direction={{ $currentDirection }}">&raquo;</a></li>
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
</div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Filter button click event
            $('#filterBtn').on('click', function() {
                var year = $('#year').val();
                var month = $('#month').val();
                var outlet = $('#outlet').val();

                // Get current sorting parameters
                var sort = '{{ request('sort', 'performance_score') }}';
                var direction = '{{ request('direction', 'desc') }}';

                // Construct URL with query parameters
                var url = '{{ route('outlet-performance.index') }}';
                var params = [];

                if (year) params.push('year=' + year);
                if (month) params.push('month=' + month);
                if (outlet) params.push('outlet=' + outlet);
                if (sort) params.push('sort=' + sort);
                if (direction) params.push('direction=' + direction);

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.location.href = url;
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                window.location.href = '{{ route('outlet-performance.index') }}';
            });
        });
    </script>
@endsection