@extends('layouts.adminlte')

@section('title', 'Activity Logs')

@section('content-header', 'Activity Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Activity Logs</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">System Activity Logs</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                {{ $log->user ? $log->user->name : 'N/A' }}
                                @if($log->user)
                                    <br>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->action === 'login' || $log->action === 'logout' ? 'info' : ($log->action === 'create' ? 'success' : ($log->action === 'update' ? 'warning' : ($log->action === 'delete' ? 'danger' : 'secondary'))) }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>{{ $log->module ?: '-' }}</td>
                            <td>{{ $log->description ?: '-' }}</td>
                            <td>{{ $log->logged_at->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->ip_address ?: '-' }}</td>
                            <td>
                                <a href="{{ route('activity-logs.show', $log->id) }}" class="btn btn-sm btn-info">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No activity logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        
        <div class="card-footer clearfix">
            <div class="float-right">
                <ul class="pagination pagination-sm m-0">
                    @if ($activityLogs->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $activityLogs->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    @php
                        $start = max(1, $activityLogs->currentPage() - 2);
                        $end = min($activityLogs->lastPage(), $activityLogs->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $activityLogs->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $activityLogs->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $activityLogs->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $activityLogs->lastPage())
                        @if ($end < $activityLogs->lastPage() - 1)
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $activityLogs->url($activityLogs->lastPage()) }}">{{ $activityLogs->lastPage() }}</a></li>
                    @endif

                    @if ($activityLogs->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $activityLogs->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endsection