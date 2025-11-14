@extends('layouts.adminlte')

@section('title', 'Edit Daily Income')

@section('content-header', 'Edit Daily Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('daily-incomes.index') }}">Daily Income</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Daily Income</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('daily-incomes.update', $dailyIncome->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-3">
                                    <label for="date">Date</label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        id="date" name="date" value="{{ old('date', $dailyIncome->date->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="border border-grey rounded p-2 mb-4">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-3">
                                        <label for="moda_id">Moda</label>
                                        <select class="form-control @error('moda_id') is-invalid @enderror" id="moda_id"
                                            name="moda_id" required>
                                            <option value="">Select Moda</option>
                                            @foreach ($modas ?? \App\Models\Moda::all() as $moda)
                                                <option value="{{ $moda->id }}"
                                                    {{ old('moda_id', $dailyIncome->moda_id) == $moda->id ? 'selected' : '' }}>
                                                    {{ $moda->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('moda_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror

                                    <div class="col-3">
                                        <label for="colly">Colly</label>
                                        <input type="number" class="form-control @error('colly') is-invalid @enderror"
                                            id="colly" name="colly" placeholder="Enter number of colly"
                                            value="{{ old('colly', $dailyIncome->colly) }}" min="0" required>
                                        @error('colly')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-3">
                                        <label for="weight">Weight (kg)</label>
                                        <input type="text" class="form-control @error('weight') is-invalid @enderror number-format"
                                            id="weight" name="weight" placeholder="Enter total weight in kg"
                                            value="{{ old('weight', $dailyIncome->weight) ? number_format(old('weight', $dailyIncome->weight), 0, '.', '') : '' }}" min="0" required>
                                        @error('weight')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-3">
                                        <label for="income">Income (Rp)</label>
                                        <input type="text" class="form-control @error('income') is-invalid @enderror number-format"
                                            id="income" name="income" placeholder="Enter income amount"
                                            value="{{ old('income', $dailyIncome->income) ? number_format(old('income', $dailyIncome->income), 0, '.', '') : '' }}" min="0" required>
                                        @error('income')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" onclick="prepareFormForSubmit()">Update Income</button>
                        <a href="{{ route('daily-incomes.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Format number with thousand separator (no decimals)
        function formatNumber(num) {
            if (num === '' || num === null || num === undefined) {
                return '';
            }

            // Remove any existing formatting
            num = parseInt(num.toString().replace(/[^\d]/g, ''));

            if (isNaN(num)) {
                return '';
            }

            // Format with thousand separators (no decimals)
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Parse formatted number back to integer
        function parseNumber(str) {
            if (str === '' || str === null || str === undefined) {
                return 0;
            }

            // Remove thousand separators and convert to integer
            return parseInt(str.toString().replace(/[^\d]/g, '')) || 0;
        }

        // Format all number inputs with thousand separators
        function formatNumberInputs() {
            document.querySelectorAll('.number-format').forEach(input => {
                // Store the current cursor position before formatting
                const start = input.selectionStart;
                const end = input.selectionEnd;

                // Format the value if it's not already formatted
                const rawValue = input.value;
                if (rawValue && !rawValue.includes('.')) {
                    input.value = formatNumber(rawValue);
                }

                // Restore cursor position
                input.setSelectionRange(start, end);
            });
        }

        // Add event listeners for formatting
        function addNumberFormatListeners() {
            document.querySelectorAll('.number-format').forEach(input => {
                // Format on blur (when user leaves the field)
                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = formatNumber(this.value);
                    }
                });

                // Remove formatting when user focuses on field for editing
                input.addEventListener('focus', function() {
                    const rawValue = parseNumber(this.value);
                    this.value = rawValue;
                });

                // Format while typing, but allow valid inputs and maintain cursor position
                input.addEventListener('input', function() {
                    // Store the current cursor position
                    let start = this.selectionStart;
                    let end = this.selectionEnd;

                    // Keep only digits
                    let value = this.value;
                    const rawValue = value.replace(/[^\d]/g, '');

                    // Calculate how many separators were added before the cursor
                    const valueBeforeCursor = rawValue.substring(0, start);
                    const formattedValueBeforeCursor = formatNumber(valueBeforeCursor);

                    // Format the value
                    const formattedValue = rawValue === '' ? '' : formatNumber(rawValue);
                    this.value = formattedValue;

                    // Adjust cursor position to account for added separators
                    if (formattedValueBeforeCursor) {
                        // Calculate the new cursor position
                        const expectedPosition = formattedValue.indexOf(formattedValueBeforeCursor) + formattedValueBeforeCursor.length;
                        if (expectedPosition <= formattedValue.length) {
                            this.setSelectionRange(expectedPosition, expectedPosition);
                        }
                    } else if (formattedValue) {
                        this.setSelectionRange(formattedValue.length, formattedValue.length);
                    }
                });
            });
        }

        // Prepare form for submission by removing formatting
        function prepareFormForSubmit() {
            document.querySelectorAll('.number-format').forEach(input => {
                // Remove thousand separators before submitting
                const rawValue = parseNumber(input.value);
                input.value = rawValue;
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add formatting to existing inputs
            addNumberFormatListeners();

            // Format initial values if they exist
            formatNumberInputs();
        });
    </script>
@endsection