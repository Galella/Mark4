@extends('layouts.adminlte')

@section('title', 'Moda Detail')

@section('content-header', 'Moda Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('modas.index') }}">Modas</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Moda Detail</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text">Name</span>
                                    <span class="info-box-number">{{ $moda->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text">Created At</span>
                                    <span class="info-box-number">{{ $moda->created_at->format('d F Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text">Description</span>
                                    <span class="info-box-number">{{ $moda->description ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <a href="{{ route('modas.index') }}" class="btn btn-default">Back to List</a>
                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() || Auth::user()->isAdminArea())
                        <a href="{{ route('modas.edit', $moda) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('modas.destroy', $moda) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this moda?')">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection