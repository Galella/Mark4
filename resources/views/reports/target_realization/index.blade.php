@extends('layouts.adminlte')

@section('title', 'Target vs Realisasi Report')

@section('content-header', 'Target vs Realisasi Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Target vs Realisasi Report</li>
@endsection

@section('content')
    <div class="container-fluid">
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
                        <!-- Filter Form -->
                        <div class="row mb-4">
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
                                    @foreach ($availableMonths as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}"
                                            {{ $selectedMonth == $monthNum ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                                    <button type="button" class="btn btn-default" id="resetBtn">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <a href="{{ route('reports.target-realization.export') }}" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- /.filter form -->

                        <div class="table-responsive">
                            <table id="targetRealizationTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Outlet</th>
                                        <th>Periode</th>
                                        <th>Target</th>
                                        <th>Realisasi</th>
                                        <th>Progres</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData as $data)
                                        <tr>
                                            <td>{{ $data['outlet_name'] }}</td>
                                            <td>{{ $availableMonths[$selectedMonth] ?? '' }} {{ $selectedYear }}</td>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- DataTables & Bootstrap 4 -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">

    <script>
        $(function() {
            $("#targetRealizationTable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false
            });

            // Filter button click event
            $('#filterBtn').on('click', function() {
                var year = $('#year').val();
                var month = $('#month').val();
                var office = $('#office').val();
                var outlet = $('#outlet').val();

                // Construct URL with query parameters
                var url = '{{ route('reports.target-realization.index') }}';
                var params = [];

                if (year) params.push('year=' + year);
                if (month) params.push('month=' + month);
                if (office) params.push('office=' + office);
                if (outlet) params.push('outlet=' + outlet);

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.location.href = url;
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                window.location.href = '{{ route('reports.target-realization.index') }}';
            });
        });
    </script>
@endsection
