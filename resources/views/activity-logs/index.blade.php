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
                {{ $activityLogs->links() }}
            </div>
        </div>
    </div>
@endsection