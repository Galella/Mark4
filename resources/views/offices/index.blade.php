@extends('layouts.adminlte')

@section('title', 'Offices')

@section('content-header', 'Office Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Offices</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Offices</h3>

        </div>
        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('offices.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search offices..."
                        value="{{ request('search') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="type" id="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="pusat" {{ request('type') === 'pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                        <option value="wilayah" {{ request('type') === 'wilayah' ? 'selected' : '' }}>Kantor Wilayah
                        </option>
                        <option value="area" {{ request('type') === 'area' ? 'selected' : '' }}>Kantor Area</option>
                    </select>
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if (request()->has('search') || request()->has('type') || request()->has('status'))
                    <a href="{{ route('offices.index') }}" class="btn btn-default mb-2 ml-2">
                        Clear
                    </a>
                @endif

                <div class="form-group ml-2 mb-2 mr-2">
                    <a href="{{ route('offices.export') }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    @can('create', App\Models\Office::class)
                        <a href="{{ route('offices.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Office
                        </a>
                    @endcan
                </div>
            </form>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Parent Office</th>
                            {{-- <th>Address</th> --}}
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offices as $index => $office)
                            <tr>
                                <td>{{ $offices->firstItem() + $index }}</td>
                                <td>{{ $office->name }}</td>
                                <td>{{ $office->code }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $office->type === 'pusat' ? 'primary' : ($office->type === 'wilayah' ? 'info' : 'success') }}">
                                        {{ ucfirst($office->type) }}
                                    </span>
                                </td>
                                <td>{{ $office->parent ? $office->parent->name : '-' }}</td>
                                {{-- <td>{{ $office->address ?: '-' }}</td> --}}
                                <td>
                                    @if ($office->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @can('update', $office)
                                        <a href="{{ route('offices.edit', $office) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @endcan

                                    @can('delete', $office)
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-office-id="{{ $office->id }}" data-office-name="{{ $office->name }}"
                                            data-toggle="modal" data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No offices found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $offices->firstItem() }} to {{ $offices->lastItem() }} of {{ $offices->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($offices->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $offices->previousPageUrl() }}">&laquo;</a>
                        </li>
                    @endif

                    @php
                        $start = max(1, $offices->currentPage() - 2);
                        $end = min($offices->lastPage(), $offices->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $offices->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $offices->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $offices->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $offices->lastPage())
                        @if ($end < $offices->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $offices->url($offices->lastPage()) }}">{{ $offices->lastPage() }}</a></li>
                    @endif

                    @if ($offices->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $offices->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- /.card-body -->
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="officeName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently remove the office from the
                        system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>All outlets under this office will be affected</li>
                        <li>All users assigned to this office may need to be reassigned</li>
                        <li>If this is a parent office, all child offices may be impacted</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Office</button>
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
                const officeId = $(this).data('office-id');
                const officeName = $(this).data('office-name');

                // Update the modal content
                $('#officeName').text(officeName);

                // Update the form action URL
                const deleteUrl = '{{ route('offices.destroy', ':id') }}'.replace(':id', officeId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function() {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection
