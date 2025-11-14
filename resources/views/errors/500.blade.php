@extends('layouts.adminlte')

@section('title', 'Error')

@section('content-header', 'Server Error')

@section('content')
<div class="error-page">
    <div class="error-content" style="margin-left: auto; margin-right: auto; max-width: 600px; text-align: center;">
        <h2 class="headline text-danger">500</h2>
        <div class="error-message">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Something went wrong.</h3>
            <p>
                We will work on fixing that right away. 
                Meanwhile, you may <a href="{{ route('dashboard') }}">return to dashboard</a> or try using the search form.
            </p>
        </div>
    </div>
</div>
@endsection