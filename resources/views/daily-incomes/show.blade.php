@extends('layouts.adminlte')

@section('title', 'View Daily Income')

@section('content-header', 'View Daily Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('daily-incomes.index') }}">Daily Income</a></li>
    <li class="breadcrumb-item active">View</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Daily Income Details</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Date:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->date->format('d M Y') }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Moda:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->moda->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Colly:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->colly }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Weight:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ number_format($dailyIncome->weight, 2) }} kg
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Income:</strong>
                        </div>
                        <div class="col-sm-8">
                            Rp {{ number_format($dailyIncome->income, 2) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Outlet:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->outlet->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Recorded by:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->user->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Created at:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $dailyIncome->created_at->format('d M Y H:i:s') }}
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <a href="{{ route('daily-incomes.index') }}" class="btn btn-default">Back to List</a>
                    @if(auth()->user()->isAdminOutlet() && $dailyIncome->outlet_id === auth()->user()->outlet_id)
                        <a href="{{ route('daily-incomes.edit', $dailyIncome->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('daily-incomes.destroy', $dailyIncome->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection