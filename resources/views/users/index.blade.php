@extends('layouts.adminlte')

@section('title', 'Users')

@section('content-header', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Users</h3>
        </div>

        <!-- Filter and Search Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('users.index') }}" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search users..."
                        value="{{ request('search') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="role" id="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="admin_wilayah" {{ request('role') === 'admin_wilayah' ? 'selected' : '' }}>Admin
                            Wilayah</option>
                        <option value="admin_area" {{ request('role') === 'admin_area' ? 'selected' : '' }}>Admin Area
                        </option>
                        <option value="admin_outlet" {{ request('role') === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if (request()->has('search') || request()->has('role'))
                    <a href="{{ route('users.index') }}" class="btn btn-default mb-2 ml-2">
                        Clear
                    </a>
                @endif
                 <div class="form-group ml-2 mb-2 mr-2">
                    <a href="{{ route('users.export') }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    @can('create', App\Models\User::class)
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New User
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
                            <th>Email</th>
                            <th>Role</th>
                            <th>Office</th>
                            <th>Outlet</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin_wilayah' ? 'primary' : ($user->role === 'admin_area' ? 'info' : 'success')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td>{{ $user->office ? $user->office->name : '-' }}</td>
                                <td>{{ $user->outlet ? $user->outlet->name : '-' }}</td>
                                <td>
                                    @can('update', $user)
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @endcan

                                    @can('delete', $user)
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                            data-toggle="modal" data-target="#deleteModal">
                                            Delete
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="table-info-text">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                </div>
                <ul class="pagination pagination-sm m-0 float-right">
                    @if ($users->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    @php
                        $start = max(1, $users->currentPage() - 2);
                        $end = min($users->lastPage(), $users->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $users->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $users->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $users->lastPage())
                        @if ($end < $users->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $users->url($users->lastPage()) }}">{{ $users->lastPage() }}</a></li>
                    @endif

                    @if ($users->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- /.card-body -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete <strong id="userName"></strong>?</p>
                                        <p class="text-danger">This action cannot be undone and will permanently remove the
                                            user from the system.</p>
                                        <p>Please note:</p>
                                        <ul>
                                            <li>All related data and activities will be preserved with this user as
                                                reference</li>
                                            <li>Access rights will be revoked immediately</li>
                                            <li>This action is irreversible</li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancel</button>
                                        <form id="deleteForm" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete User</button>
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
                                    const userId = $(this).data('user-id');
                                    const userName = $(this).data('user-name');

                                    // Update the modal content
                                    $('#userName').text(userName);

                                    // Update the form action URL
                                    const deleteUrl = '{{ route('users.destroy', ':id') }}'.replace(':id', userId);
                                    $('#deleteForm').attr('action', deleteUrl);
                                });

                                // Reset form when modal is closed
                                $('#deleteModal').on('hidden.bs.modal', function() {
                                    $('#deleteForm').attr('action', '');
                                });
                            });
                        </script>
                    @endsection
