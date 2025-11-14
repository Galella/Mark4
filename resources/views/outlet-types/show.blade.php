@extends('layouts.adminlte')

@section('title', 'Outlet Type Details')

@section('content-header', 'Outlet Type Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('outlet-types.index') }}">Outlet Types</a></li>
    <li class="breadcrumb-item active">Outlet Type Details</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outlet Type Information: {{ $outletType->name }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Name:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $outletType->name }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Description:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $outletType->description ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Created At:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $outletType->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <h6>Last Updated:</h6>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted">{{ $outletType->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                
                <div class="card-footer">
                    <a href="{{ route('outlet-types.index') }}" class="btn btn-default">Back to Outlet Types</a>
                    @can('update', $outletType)
                        <a href="{{ route('outlet-types.edit', $outletType->id) }}" class="btn btn-primary">Edit</a>
                    @endcan
                    @can('delete', $outletType)
                        <form action="{{ route('outlet-types.destroy', $outletType->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this outlet type? This cannot be undone if there are outlets using this type.')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection