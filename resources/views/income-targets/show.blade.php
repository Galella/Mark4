@extends('layouts.adminlte')

@section('title', 'View Income Target')

@section('content-header', 'View Income Target')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('income-targets.index') }}">Income Targets</a></li>
    <li class="breadcrumb-item active">View</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Income Target Details</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Outlet</label>
                        <p class="form-control-static">{{ $incomeTarget->outlet->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Moda</label>
                        <p class="form-control-static">{{ $incomeTarget->moda->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Target Period</label>
                        <p class="form-control-static">
                            {{ \DateTime::createFromFormat('!m', $incomeTarget->target_month)->format('F') }} {{ $incomeTarget->target_year }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Target Amount</label>
                        <p class="form-control-static">Rp {{ number_format($incomeTarget->target_amount, 2, ',', '.') }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Assigned By</label>
                        <p class="form-control-static">{{ $incomeTarget->assignedBy->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Assigned At</label>
                        <p class="form-control-static">{{ $incomeTarget->assigned_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
            
            @if($incomeTarget->description)
                <div class="form-group">
                    <label>Description</label>
                    <p class="form-control-static">{{ $incomeTarget->description }}</p>
                </div>
            @endif
            
            <div class="form-group">
                <a href="{{ route('income-targets.index') }}" class="btn btn-default">Back to List</a>
                @can('update', $incomeTarget)
                    <a href="{{ route('income-targets.edit', $incomeTarget) }}" class="btn btn-primary">Edit</a>
                @endcan
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection