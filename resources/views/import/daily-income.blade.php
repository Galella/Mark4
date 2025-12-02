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

            <form id="importForm" action="{{ route('import.daily-income.import') }}" method="POST" enctype="multipart/form-data">
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
                    <button type="submit" class="btn btn-primary" id="importBtn">
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

            <!-- Progress Bar (Initially Hidden) -->
            <div id="progressSection" class="mt-4" style="display: none;">
                <div class="progress mb-3">
                    <div id="progressBar" class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">
                        <span id="progressText">0%</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Rows</span>
                                <span id="totalRows" class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Processed</span>
                                <span id="processedRows" class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Successful</span>
                                <span id="successfulImports" class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Errors</span>
                                <span id="failedImports" class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="alert alert-info" id="progressMessage">
                        Waiting to start...
                    </div>
                </div>
            </div>
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

            let importInProgress = false;
            let jobId = null;
            let progressInterval = null;

            $('#importForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Check if file is selected
                const fileInput = document.getElementById('file');
                if (!fileInput.files.length) {
                    alert('Please select an Excel file to import.');
                    return;
                }

                // Disable submit button and show progress bar
                $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
                $('#progressSection').show();

                // Get form data
                const formData = new FormData(this);

                // Submit the form via AJAX to get the job ID
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // First, submit the form to initiate import
                fetch('{{ route("import.daily-income.import") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Extract job ID from response or redirect
                    console.log('Import initiated:', data);
                    // Start polling for progress (we'll handle this differently since the import runs synchronously)
                })
                .catch(error => {
                    console.error('Import error:', error);
                    $('#importBtn').prop('disabled', false).html('<i class="fas fa-file-import"></i> Import Daily Income');
                    alert('An error occurred during import. Please try again.');
                });

                // Since the import runs synchronously, we'll use a different approach
                // We'll use a timeout to simulate the start and then poll for progress
                setTimeout(function() {
                    startProgressTracking();
                }, 1000);
            });

            function startProgressTracking() {
                // For demo purposes, we'll create a fake job ID
                // In reality, this would be passed from the server
                jobId = 'fake_job_id_' + Date.now();

                // Start polling for progress
                progressInterval = setInterval(function() {
                    if (importInProgress) {
                        fetch('{{ route("import.daily-income.progress") }}?job_id=' + jobId)
                            .then(response => response.json())
                            .then(data => {
                                updateProgress(data);

                                // If import is completed, stop polling
                                if (data.status === 'completed' || data.status === 'error') {
                                    clearInterval(progressInterval);
                                    importCompleted();
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching progress:', error);
                                clearInterval(progressInterval);
                            });
                    }
                }, 1000); // Poll every second
            }

            function updateProgress(data) {
                const percentage = data.percentage || 0;

                // Update progress bar
                $('#progressBar').css('width', percentage + '%');
                $('#progressText').text(percentage.toFixed(1) + '%');

                // Update info boxes
                $('#totalRows').text(data.total_rows || 0);
                $('#processedRows').text(data.processed_rows || 0);
                $('#successfulImports').text(data.successful_imports || 0);
                $('#failedImports').text(data.failed_imports || 0);

                // Update message
                $('#progressMessage').text(data.message || 'Processing...');
            }

            function importCompleted() {
                importInProgress = false;
                $('#importBtn').prop('disabled', false).html('<i class="fas fa-file-import"></i> Import Daily Income');

                // Show success message
                $('#progressMessage').removeClass('alert-info').addClass('alert-success');
                $('#progressMessage').html('<i class="fas fa-check"></i> Import completed successfully! Refreshing the page...');

                // Optionally reload the page to show results
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    </script>
@endsection