@extends('layouts.adminlte')

@section('title', 'User Details')

@section('content-header', 'User Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">User Details</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Information: {{ $user->name }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Full Name:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->name }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Email:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Role:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">
                                <span class="badge badge-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin_wilayah' ? 'primary' : ($user->role === 'admin_area' ? 'info' : 'success')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    @if($user->office)
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Office:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->office->name }} ({{ ucfirst($user->office->type) }})</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($user->outlet)
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Outlet:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->outlet->name }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Created At:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Last Updated:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $user->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                
                <div class="card-footer">
                    <a href="{{ route('users.index') }}" class="btn btn-default">Back to Users</a>
                    @can('update', $user)
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Edit</a>
                    @endcan
                    @can('delete', $user)
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection