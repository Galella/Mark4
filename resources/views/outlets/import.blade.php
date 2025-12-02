@extends('layouts.adminlte')

@section('title', 'Import Outlets')

@section('content-header', 'Import Outlets')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('outlets.index') }}">Outlet Management</a></li>
    <li class="breadcrumb-item active">Import Excel</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import Outlets from Excel</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Instructions:</h5>
                <ul>
                    <li>Download the <a href="{{ route('outlets.import.template') }}" target="_blank">template file (.csv format - can be opened and saved as .xlsx in Microsoft Excel)</a> to see the required format</li>
                    <li>Ensure your Excel file contains the following columns: <strong>name</strong>, <strong>code</strong>, <strong>office_code</strong>, <strong>outlet_type_name</strong>, and optional <strong>description</strong>, <strong>address</strong>, <strong>phone</strong>, <strong>email</strong>, <strong>pic_name</strong>, <strong>pic_phone</strong>, <strong>is_active</strong></li>
                    <li>Office codes must match existing office codes in the system</li>
                    <li>Outlet type names must match existing outlet type names in the system</li>
                    <li>Outlet codes must be unique</li>
                    <li>Recommended file format is .xlsx (Microsoft Excel format), but .xls and .csv are also supported</li>
                    <li>Maximum file size is 10MB</li>
                </ul>
            </div>

            <form action="{{ route('outlets.import') }}" method="POST" enctype="multipart/form-data">
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
                        <i class="fas fa-file-import"></i> Import Outlets
                    </button>
                    <a href="{{ route('outlets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        <!-- /.card-body -->

        @if(session('error_count'))
            <div class="card-footer">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Import Completed with Errors</h5>
                    <p>Successfully imported {{ session('success_count') }} records.</p>
                    <p>Encountered {{ session('error_count') }} errors:</p>
                    <ul>
                        @foreach(session('errors') as $error)
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