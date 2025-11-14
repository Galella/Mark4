@extends('layouts.adminlte')

@section('title', 'Activity Log Details')

@section('content-header', 'Activity Log Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('activity-logs.index') }}">Activity Logs</a></li>
    <li class="breadcrumb-item active">Log Details</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activity Log Information</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Log ID:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->id }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>User:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">
                                {{ $activityLog->user ? $activityLog->user->name : 'N/A' }} 
                                ({{ $activityLog->user ? $activityLog->user->email : 'N/A' }})
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Action:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">
                                <span class="badge badge-{{ $activityLog->action === 'login' || $activityLog->action === 'logout' ? 'info' : ($activityLog->action === 'create' ? 'success' : ($activityLog->action === 'update' ? 'warning' : ($activityLog->action === 'delete' ? 'danger' : 'secondary'))) }}">
                                    {{ ucfirst($activityLog->action) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Module:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->module ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Description:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->description ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Old Values:</h6>
                        </div>
                        <div class="col-sm-8">
                            <pre class="text-muted bg-light p-2 rounded">{{ $activityLog->old_values ? json_encode($activityLog->old_values, JSON_PRETTY_PRINT) : '-' }}</pre>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>New Values:</h6>
                        </div>
                        <div class="col-sm-8">
                            <pre class="text-muted bg-light p-2 rounded">{{ $activityLog->new_values ? json_encode($activityLog->new_values, JSON_PRETTY_PRINT) : '-' }}</pre>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>IP Address:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->ip_address ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>User Agent:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->user_agent ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Logged At:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $activityLog->logged_at->format('d M Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                
                <div class="card-footer">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-default">Back to Activity Logs</a>
                </div>
            </div>
        </div>
    </div>
@endsection