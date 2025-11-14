@extends('layouts.adminlte')

@section('title', 'Daily Income Report')

@section('content-header', 'Daily Income Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Daily Income Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Income Report</h3>
                </div>
                <!-- Filter Form -->
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.daily-income.index') }}">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="outlet_id" class="form-label">Outlet</label>
                                <select name="outlet_id" id="outlet_id" class="form-control">
                                    <option value="">All Outlets</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="moda_id" class="form-label">Moda</label>
                                <select name="moda_id" id="moda_id" class="form-control">
                                    <option value="">All Modas</option>
                                    @foreach($modas as $moda)
                                        <option value="{{ $moda->id }}" {{ request('moda_id') == $moda->id ? 'selected' : '' }}>
                                            {{ $moda->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search by moda, outlet, or date..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('reports.daily-income.index') }}" class="btn btn-secondary me-2">Reset</a>
                                <a href="{{ route('reports.daily-income.export', request()->query()) }}" class="btn btn-success">Export to Excel</a>
                                <a href="{{ route('reports.daily-income.summary') }}" class="btn btn-info">Summary View</a>
                            </div>
                        </div>
                    </form>

                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Outlet</th>
                                    <th>Moda</th>
                                    <th>Colly</th>
                                    <th>Weight</th>
                                    <th>Income</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyIncomes as $income)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($income->date)->format('d M Y') }}</td>
                                    <td>{{ $income->outlet->name }}</td>
                                    <td>{{ $income->moda->name }}</td>
                                    <td>{{ $income->colly }}</td>
                                    <td>{{ number_format($income->weight, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($income->income, 0, ',', '.') }}</td>
                                    <td>{{ $income->user->name }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No daily income records found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="5" class="text-end">Total:</th>
                                    <th>
                                        Rp {{ number_format($dailyIncomes->sum(function($income) { return $income->income; }), 0, ',', '.') }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $dailyIncomes->appends(request()->query())->links() }}
                    </div>
                </div>
                <!-- /.card-footer -->
            </div>
        </div>
    </div>
</div>
@endsection