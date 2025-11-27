@extends('layouts.adminlte')

@section('title', 'Outlet Types')

@section('content-header', 'Outlet Type Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Outlet Types</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Outlet Types</h3>

        </div>
        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('outlet-types.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                        placeholder="Search outlet types..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Search
                </button>
                @if (request()->has('search'))
                    <a href="{{ route('outlet-types.index') }}" class="btn btn-default mb-2 ml-2">
                        Clear
                    </a>
                @endif
                <div class="form-group ml-2 mb-2 mr-2">
                    <a href="{{ route('outlet-types.export') }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    @can('create', App\Models\OutletType::class)
                        <a href="{{ route('outlet-types.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Type
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
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outletTypes as $index => $type)
                            <tr>
                                <td>{{ $outletTypes->firstItem() + $index }}</td>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->description ?: '-' }}</td>
                                <td>{{ $type->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @can('update', $type)
                                        <a href="{{ route('outlet-types.edit', $type) }}"
                                            class="btn btn-sm btn-primary">Edit</a>
                                    @endcan

                                    @can('delete', $type)
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-type-id="{{ $type->id }}" data-type-name="{{ $type->name }}"
                                            data-toggle="modal" data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No outlet types found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $outletTypes->firstItem() }} to {{ $outletTypes->lastItem() }} of
                    {{ $outletTypes->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($outletTypes->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $outletTypes->previousPageUrl() }}">&laquo;</a>
                        </li>
                    @endif

                    @php
                        $start = max(1, $outletTypes->currentPage() - 2);
                        $end = min($outletTypes->lastPage(), $outletTypes->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $outletTypes->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $outletTypes->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $outletTypes->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $outletTypes->lastPage())
                        @if ($end < $outletTypes->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $outletTypes->url($outletTypes->lastPage()) }}">{{ $outletTypes->lastPage() }}</a></li>
                    @endif

                    @if ($outletTypes->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $outletTypes->nextPageUrl() }}">&raquo;</a>
                        </li>
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
                    <p>Are you sure you want to delete <strong id="typeName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently remove the outlet type from the
                        system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>All outlets with this type will need to be reassigned to another type</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Type</button>
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
                const typeId = $(this).data('type-id');
                const typeName = $(this).data('type-name');

                // Update the modal content
                $('#typeName').text(typeName);

                // Update the form action URL
                const deleteUrl = '{{ route('outlet-types.destroy', ':id') }}'.replace(':id', typeId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function() {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection
