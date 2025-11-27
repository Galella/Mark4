@extends('layouts.adminlte')

@section('title', 'Import Daily Income')

@section('content-header', 'Import Daily Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Import Daily Income</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import Daily Income from Excel</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Instructions:</h5>
                <ul>
                    <li>Download the <a href="{{ route('import.daily-income.template') }}">template file (.csv format)</a> to see the required format</li>
                    @if(Auth::user()->isAdminOutlet())
                    <li>Ensure your Excel file contains the following columns: <strong>Date</strong>, <strong>Moda Name</strong>, <strong>Colly</strong>, <strong>Weight</strong>, and <strong>Income</strong></li>
                    <li>All records will be imported for your outlet: <strong>{{ Auth::user()->outlet->name ?? 'Unknown Outlet' }}</strong></li>
                    @else
                    <li>Ensure your Excel file contains the following columns: <strong>Date</strong>, <strong>Outlet Code</strong>, <strong>Moda Name</strong>, <strong>Colly</strong>, <strong>Weight</strong>, and <strong>Income</strong></li>
                    <li>Outlet codes must match existing outlet codes in the system</li>
                    @endif
                    <li>Date format should be YYYY-MM-DD (e.g. 2025-01-15)</li>
                    <li>Moda names must match existing moda names in the system</li>
                    <li>Colly should be a positive integer</li>
                    <li>Weight should be a positive number</li>
                    <li>Income should be a positive number</li>
                    <li>Recommended file format is .xlsx (Microsoft Excel format), but .xls and .csv are also supported</li>
                    <li>Maximum file size is 10MB</li>
                </ul>
            </div>

            <form action="{{ route('import.daily-income.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file">Excel File <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv">
                            <label class="custom-file-label" for="file">Choose file...</label>
                        </div>
                    </div>
                    @error('file')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-import"></i> Import Daily Income
                    </button>
                    @if(Auth::user()->isAdminOutlet())
                        <a href="{{ route('daily-incomes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <!-- /.card-body -->

        @if(session('import_errors'))
            <div class="card-footer">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Import Completed with Errors</h5>
                    <p>{{ session('success') }}</p>
                    <p>Encountered {{ count(session('import_errors')) }} errors:</p>
                    <ul>
                        @foreach(session('import_errors') as $error)
                            <li>Row {{ $error['row'] }}: {{ $error['error'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Add the name of the file to the label
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endsection