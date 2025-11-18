@extends('layouts.adminlte')

@section('title', 'Outlets')

@section('content-header', 'Outlet Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Outlets</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Outlets</h3>
            <div class="card-tools">
                <a href="{{ route('outlets.export') }}" class="btn btn-success mr-2">
                    <i class="fas fa-file-excel"></i> Export
                </a>
                @can('create', App\Models\Outlet::class)
                    <a href="{{ route('outlets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Outlet
                    </a>
                @endcan
            </div>
        </div>
        <!-- Outlet Type Statistics -->
        @if(count($outletTypeStats) > 0)
        <div class="card-body border-bottom">
            <div class="row">
                @foreach($outletTypeStats as $stat)
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1">
                                <i class="fas fa-store"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $stat['type']->name }}</span>
                                <span class="info-box-number">{{ $stat['count'] }} Outlet{!! $stat['count'] != 1 ? 's' : '' !!}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('outlets.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search outlets..."
                        value="{{ request('search') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="office_id" id="office_id" class="form-control">
                        <option value="">All Offices</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="outlet_type_id" id="outlet_type_id" class="form-control">
                        <option value="">All Types</option>
                        @foreach ($outletTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ request('outlet_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
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
                @if (request()->has('search') ||
                        request()->has('office_id') ||
                        request()->has('outlet_type_id') ||
                        request()->has('status'))
                    <a href="{{ route('outlets.index') }}" class="btn btn-default mb-2 ml-2">
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
                            <th>Name</th>
                            <th>Code</th>
                            <th>Office</th>
                            <th>Outlet Type</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outlets as $index => $outlet)
                            <tr>
                                <td>{{ $outlets->firstItem() + $index }}</td>
                                <td>{{ $outlet->name }}</td>
                                <td>{{ $outlet->code }}</td>
                                <td>{{ $outlet->office->name }}</td>
                                <td>{{ $outlet->outletType->name }}</td>
                                <td>{{ $outlet->address ?: '-' }}</td>
                                <td>
                                    @if ($outlet->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @can('update', $outlet)
                                        <a href="{{ route('outlets.edit', $outlet) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @endcan

                                    @can('delete', $outlet)
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-outlet-id="{{ $outlet->id }}"
                                            data-outlet-name="{{ $outlet->name }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No outlets found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $outlets->firstItem() }} to {{ $outlets->lastItem() }} of {{ $outlets->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($outlets->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $outlets->previousPageUrl() }}">&laquo;</a></li>
                    @endif
                    
                    @for ($i = 1; $i <= $outlets->lastPage(); $i++)
                        <li class="page-item {{ $i == $outlets->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $outlets->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    
                    @if ($outlets->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $outlets->nextPageUrl() }}">&raquo;</a></li>
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
                    <p>Are you sure you want to delete <strong id="outletName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently remove the outlet from the system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>All related daily income records will be affected</li>
                        <li>Any users assigned to this outlet may need to be reassigned</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Outlet</button>
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
                const outletId = $(this).data('outlet-id');
                const outletName = $(this).data('outlet-name');

                // Update the modal content
                $('#outletName').text(outletName);

                // Update the form action URL
                const deleteUrl = '{{ route("outlets.destroy", ":id") }}'.replace(':id', outletId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection