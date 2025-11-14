@extends('layouts.adminlte')

@section('title', 'Error')

@section('content-header', 'Error')

@section('content')
<div class="error-page">
    <div class="error-content" style="margin-left: auto; margin-right: auto; max-width: 600px; text-align: center;">
        <h2 class="headline text-danger">Error</h2>
        <div class="error-message">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> An error occurred.</h3>
            <p>
                Something went wrong. Please try again later.
                Meanwhile, you may <a href="{{ route('dashboard') }}">return to dashboard</a>.
            </p>
        </div>
    </div>
</div>
@endsection