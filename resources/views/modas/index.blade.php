@extends('layouts.adminlte')

@section('title', 'Modas')

@section('content-header', 'Moda Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Modas</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Modas</h3>
            <div class="card-tools">
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() || Auth::user()->isAdminArea())
                    <a href="{{ route('modas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Moda
                    </a>
                @endif
            </div>
        </div>
        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('modas.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search modas..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->has('search'))
                    <a href="{{ route('modas.index') }}" class="btn btn-default mb-2 ml-2">
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
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modas as $index => $moda)
                            <tr>
                                <td>{{ $modas->firstItem() + $index }}</td>
                                <td>{{ $moda->name }}</td>
                                <td>{{ $moda->description ?: '-' }}</td>
                                <td>{{ $moda->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() || Auth::user()->isAdminArea())
                                        <a href="{{ route('modas.edit', $moda) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-moda-id="{{ $moda->id }}"
                                            data-moda-name="{{ $moda->name }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No modas found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $modas->firstItem() }} to {{ $modas->lastItem() }} of {{ $modas->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($modas->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $modas->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    @php
                        $start = max(1, $modas->currentPage() - 2);
                        $end = min($modas->lastPage(), $modas->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $modas->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $modas->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $modas->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $modas->lastPage())
                        @if ($end < $modas->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $modas->url($modas->lastPage()) }}">{{ $modas->lastPage() }}</a></li>
                    @endif

                    @if ($modas->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $modas->nextPageUrl() }}">&raquo;</a></li>
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
                    <p>Are you sure you want to delete <strong id="modaName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently remove the moda from the system.</p>
                    <p>Please note:</p>
                    <ul>
                        <li>All daily income records using this moda will be affected</li>
                        <li>This action is irreversible</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Moda</button>
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
                const modaId = $(this).data('moda-id');
                const modaName = $(this).data('moda-name');

                // Update the modal content
                $('#modaName').text(modaName);

                // Update the form action URL
                const deleteUrl = '{{ route("modas.destroy", ":id") }}'.replace(':id', modaId);
                $('#deleteForm').attr('action', deleteUrl);
            });

            // Reset form when modal is closed
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteForm').attr('action', '');
            });
        });
    </script>
@endsection