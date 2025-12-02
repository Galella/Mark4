@extends('layouts.adminlte')

@section('title', 'Income Targets')

@section('content-header', 'Income Targets Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Income Targets</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Income Targets List</h3>
            <div class="card-tools">
                @can('create', \App\Models\IncomeTarget::class)
                    <a href="{{ route('income-targets.create') }}" class="btn btn-primary mr-2">
                        <i class="fas fa-plus"></i> Create New Target
                    </a>
                    <a href="{{ route('income-targets.import.form') }}" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Import Excel
                    </a>
                @endcan
            </div>
        </div>
        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('income-targets.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <select name="outlet_id" id="outlet_id" class="form-control">
                        <option value="">All Outlets</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->name }} ({{ $outlet->office?->name ?? 'No Office' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="moda_id" id="moda_id" class="form-control">
                        <option value="">All Modas</option>
                        @foreach($modas as $moda)
                            <option value="{{ $moda->id }}" {{ request('moda_id') == $moda->id ? 'selected' : '' }}>
                                {{ $moda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2 mr-2">
                    <input type="number" class="form-control" name="year" placeholder="Year" min="2000" max="2100" value="{{ request('year') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="month" id="month" class="form-control">
                        <option value="">All Months</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ \DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->has('outlet_id') || request()->has('year') || request()->has('month'))
                    <a href="{{ route('income-targets.index') }}" class="btn btn-default mb-2 ml-2">
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
                            <th>Outlet</th>
                            <th>Moda</th>
                            <th>Target Period</th>
                            <th>Target Amount</th>
                            <th>Assigned By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $index => $target)
                            <tr>
                                <td>{{ $targets->firstItem() + $index }}</td>
                                <td>{{ $target->outlet->name ?? 'N/A' }}</td>
                                <td>{{ $target->moda->name ?? 'N/A' }}</td>
                                <td>
                                    {{ \DateTime::createFromFormat('!m', $target->target_month)->format('F') }} {{ $target->target_year }}
                                </td>
                                <td>Rp {{ number_format($target->target_amount, 0, ',', '.') }}</td>
                                <td>{{ $target->assignedBy->name ?? 'N/A' }}</td>
                                <td>
                                    @can('update', $target)
                                        <a href="{{ route('income-targets.edit', $target) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @endcan

                                    @can('delete', $target)
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-target-id="{{ $target->id }}"
                                            data-target-outlet="{{ $target->outlet->name ?? 'N/A' }}"
                                            data-target-period="{{ \DateTime::createFromFormat('!m', $target->target_month)->format('F') . ' ' . $target->target_year }}"
                                            data-target-amount="Rp {{ number_format($target->target_amount, 0, ',', '.') }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No income targets found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $targets->firstItem() }} to {{ $targets->lastItem() }} of {{ $targets->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($targets->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $targets->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    @php
                        $start = max(1, $targets->currentPage() - 2);
                        $end = min($targets->lastPage(), $targets->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $targets->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $targets->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $targets->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $targets->lastPage())
                        @if ($end < $targets->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $targets->url($targets->lastPage()) }}">{{ $targets->lastPage() }}</a></li>
                    @endif

                    @if ($targets->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $targets->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
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
                    <p>Are you sure you want to delete this income target?</p>
                    <p><strong>Outlet:</strong> <span id="targetOutlet"></span></p>
                    <p><strong>Period:</strong> <span id="targetPeriod"></span></p>
                    <p><strong>Amount:</strong> <span id="targetAmount"></span></p>
                    <p class="text-danger">This action cannot be undone and will permanently remove this target from the system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>This will affect your target vs actual income reports</li>
                        <li>The target achievement statistics will be recalculated</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Target</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Handle delete button click
            $('.delete-btn').on('click', function() {
                const targetId = $(this).data('target-id');
                const targetOutlet = $(this).data('target-outlet');
                const targetPeriod = $(this).data('target-period');
                const targetAmount = $(this).data('target-amount');

                // Update the modal content
                $('#targetOutlet').text(targetOutlet);
                $('#targetPeriod').text(targetPeriod);
                $('#targetAmount').text(targetAmount);

                // Update the form action URL
                const deleteUrl = '{{ route("income-targets.destroy", ":id") }}'.replace(':id', targetId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection