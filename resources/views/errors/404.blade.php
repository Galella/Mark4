@extends('layouts.adminlte')

@section('title', 'Page Not Found')

@section('content-header', 'Page Not Found')

@section('content')
<div class="error-page">
    <div class="error-content" style="margin-left: auto; margin-right: auto; max-width: 600px; text-align: center;">
        <h2 class="headline text-warning">404</h2>
        <div class="error-message">
            <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>
            <p>
                We could not find the page you were looking for.
                Meanwhile, you may <a href="{{ route('dashboard') }}">return to dashboard</a> or try using the search form.
            </p>
        </div>
    </div>
</div>
@endsection