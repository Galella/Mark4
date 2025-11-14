@extends('layouts.adminlte')

@section('title', 'Record Daily Income')

@section('content-header', 'Record Daily Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('daily-incomes.index') }}">Daily Income</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Record Daily Income</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('daily-incomes.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-3">
                                    <label for="date">Date</label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="daily-income-entries">
                            <!-- Entry 1 -->
                            <div class="daily-income-entry border border-grey rounded p-3 mb-3 position-relative">
                                <div class="entry-header d-flex justify-content-between align-items-center mb-2">
                                    <h5>Entry #1</h5>
                                    {{-- <button type="button" class="btn btn-sm btn-danger remove-entry" style="display: none;"
                                        onclick="removeEntry(this)">Remove</button> --}}
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        {{-- <label>Moda</label> --}}
                                        <select class="form-control moda-select" name="entries[0][moda_id]" required>
                                            <option value="">Select Moda</option>
                                            @foreach ($modas ?? \App\Models\Moda::all() as $moda)
                                                <option value="{{ $moda->id }}">
                                                    {{ $moda->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        {{-- <label>Colly</label> --}}
                                        <input type="number" class="form-control" name="entries[0][colly]"
                                            placeholder="Enter number of colly" value="{{ old('entries.0.colly') }}"
                                            min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        {{-- <label>Weight (kg)</label> --}}
                                        <input type="text" class="form-control number-format" name="entries[0][weight]"
                                            placeholder="Enter total weight in kg" value="{{ old('entries.0.weight') ? number_format(old('entries.0.weight'), 0, '.', ',') : '' }}"
                                            min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- <label>Income (Rp)</label> --}}
                                        <input type="text" class="form-control number-format" name="entries[0][income]"
                                            placeholder="Enter income amount" value="{{ old('entries.0.income') ? number_format(old('entries.0.income'), 0, '.', ',') : '' }}"
                                            min="0" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-entry"
                                            style="display: none;" onclick="removeEntry(this)"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="button" class="btn btn-success" onclick="addEntry()">Add Another Entry</button>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" onclick="prepareFormForSubmit()">Save</button>
                        <a href="{{ route('daily-incomes.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template for new entries -->
    <div id="entry-template" style="display: none;">
        <div class="daily-income-entry border border-grey rounded p-3 mb-3 position-relative">
            <div class="entry-header d-flex justify-content-between align-items-center mb-2">
                <h5>Entry #<span class="entry-number">1</span></h5>
                {{-- <button type="button" class="btn btn-sm btn-danger remove-entry"
                    onclick="removeEntry(this)">Remove</button> --}}
            </div>

            <div class="row">
                <div class="col-md-3">
                    {{-- <label>Moda</label> --}}
                    <select class="form-control moda-select" name="entries[x][moda_id]" required>
                        <option value="">Select Moda</option>
                        @foreach ($modas ?? \App\Models\Moda::all() as $moda)
                            <option value="{{ $moda->id }}">
                                {{ $moda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    {{-- <label>Colly</label> --}}
                    <input type="number" class="form-control" name="entries[x][colly]" placeholder="Enter number of colly"
                        value="" min="0" required>
                </div>

                <div class="col-md-2">
                    {{-- <label>Weight (kg)</label> --}}
                    <input type="text" class="form-control number-format" name="entries[x][weight]"
                        placeholder="Enter total weight in kg" value="" min="0" required>
                </div>

                <div class="col-md-3">
                    {{-- <label>Income (Rp)</label> --}}
                    <input type="text" class="form-control number-format" name="entries[x][income]"
                        placeholder="Enter income amount" value="" min="0" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-entry"
                    onclick="removeEntry(this)"><i class="fas fa-trash"></i></button>
            </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let entryCount = 1;

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

        function addEntry() {
            const template = document.getElementById('entry-template');
            const newEntry = template.innerHTML.replace(/x/g, entryCount);

            const entriesDiv = document.getElementById('daily-income-entries');
            const newEntryDiv = document.createElement('div');
            newEntryDiv.innerHTML = newEntry;

            // Update the entry number in the header
            const entryNumberSpan = newEntryDiv.querySelector('.entry-number');
            entryNumberSpan.textContent = entryCount + 1;

            entriesDiv.appendChild(newEntryDiv.firstElementChild);
            entryCount++;

            updateRemoveButtons();

            // Add formatting to the newly added inputs
            addNumberFormatListeners();
        }

        function removeEntry(button) {
            const entries = document.querySelectorAll('.daily-income-entry');

            if (entries.length <= 1) {
                // Don't remove the last entry
                return;
            }

            const entryDiv = button.closest('.daily-income-entry');
            entryDiv.remove();

            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const entries = document.querySelectorAll('.daily-income-entry');
            entries.forEach((entry, index) => {
                const removeButton = entry.querySelector('.remove-entry');
                if (entries.length > 1) {
                    removeButton.style.display = 'inline-block';
                } else {
                    removeButton.style.display = 'none';
                }
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
            updateRemoveButtons();

            // Add formatting to existing inputs
            addNumberFormatListeners();

            // Format initial values if they exist
            formatNumberInputs();
        });
    </script>
@endsection
