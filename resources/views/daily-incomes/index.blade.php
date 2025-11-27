@extends('layouts.adminlte')

@section('title', 'Daily Income')

@section('content-header', 'Daily Income Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Daily Income</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Daily Income</h3>
            <div class="card-tools">
                @if(Auth::user()->isAdminOutlet())
                    <a href="{{ route('daily-incomes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Record New Income
                    </a>
                @endif

                <a href="{{ route('reports.daily-income.export') }}" class="btn btn-info">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </div>
        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('daily-incomes.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search income..." value="{{ request('search') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="start_date" class="sr-only">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="end_date" class="sr-only">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->has('search') || request()->has('start_date') || request()->has('end_date'))
                    <a href="{{ route('daily-incomes.index') }}" class="btn btn-default mb-2 ml-2">
                        Clear
                    </a>
                @endif
            </form>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Moda</th>
                            <th>Colly</th>
                            <th>Weight (kg)</th>
                            <th>Income (Rp)</th>
                            <th>Outlet</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyIncomes as $index => $dailyIncome)
                            <tr>
                                <td>{{ $dailyIncomes->firstItem() + $index }}</td>
                                <td>{{ $dailyIncome->date->format('d M Y') }}</td>
                                <td>{{ $dailyIncome->moda ? $dailyIncome->moda->name : '-' }}</td>
                                <td>{{ number_format($dailyIncome->colly) }}</td>
                                <td>{{ number_format($dailyIncome->weight, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($dailyIncome->income, 0, ',', '.') }}</td>
                                <td>{{ $dailyIncome->outlet->name }}</td>
                                <td>
                                    @if(Auth::user()->isAdminOutlet() && Auth::user()->outlet_id == $dailyIncome->outlet_id)
                                        <a href="{{ route('daily-incomes.edit', $dailyIncome) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-income-id="{{ $dailyIncome->id }}"
                                            data-income-date="{{ $dailyIncome->date->format('d M Y') }}"
                                            data-income-amount="Rp {{ number_format($dailyIncome->income, 0, ',', '.') }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @else
                                        <a href="{{ route('daily-incomes.show', $dailyIncome) }}" class="btn btn-sm btn-info">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No daily income records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $dailyIncomes->firstItem() }} to {{ $dailyIncomes->lastItem() }} of {{ $dailyIncomes->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($dailyIncomes->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo; Previous</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $dailyIncomes->previousPageUrl() }}">&laquo; Previous</a></li>
                    @endif

                    @php
                        $start = max(1, $dailyIncomes->currentPage() - 2);
                        $end = min($dailyIncomes->lastPage(), $dailyIncomes->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $dailyIncomes->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $dailyIncomes->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $dailyIncomes->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $dailyIncomes->lastPage())
                        @if ($end < $dailyIncomes->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $dailyIncomes->url($dailyIncomes->lastPage()) }}">{{ $dailyIncomes->lastPage() }}</a></li>
                    @endif

                    @if ($dailyIncomes->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $dailyIncomes->nextPageUrl() }}">Next &raquo;</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#">Next &raquo;</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- /.card-body -->
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this daily income record?</p>
                    <p><strong>Date:</strong> <span id="incomeDate"></span></p>
                    <p><strong>Amount:</strong> <span id="incomeAmount"></span></p>
                    <p class="text-danger">This action cannot be undone and will permanently remove this income record from the system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>This will affect your daily, monthly and overall income statistics</li>
                        <li>The change will be logged in the activity logs</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Additional custom styles for Daily Income Management */
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Handle delete button click
            $('.delete-btn').on('click', function() {
                const incomeId = $(this).data('income-id');
                const incomeDate = $(this).data('income-date');
                const incomeAmount = $(this).data('income-amount');

                // Update the modal content
                $('#incomeDate').text(incomeDate);
                $('#incomeAmount').text(incomeAmount);

                // Update the form action URL
                const deleteUrl = '{{ route("daily-incomes.destroy", ":id") }}'.replace(':id', incomeId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection