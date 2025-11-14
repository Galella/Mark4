@extends('layouts.adminlte')

@section('title', 'Create Income Target')

@section('content-header', 'Create Income Target')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('income-targets.index') }}">Income Targets</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Income Target(s)</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form method="POST" action="{{ route('income-targets.store') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div id="entries-container">
                    <div class="row border border-grey rounded p-3 mb-2 position-relative entry-row">
                        <div class="entry-header d-flex justify-content-between align-items-center mb-2">
                            <h5>Entry #<span class="entry-number">1</span></h5>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{-- <label for="entries[0][outlet_id]">Outlet *</label> --}}
                                    <select name="entries[0][outlet_id]" id="outlet_id_0"
                                        class="form-control @error('entries.0.outlet_id') is-invalid @enderror entry-outlet"
                                        required>
                                        <option value="">Select Outlet</option>
                                        @foreach ($outlets as $outlet)
                                            <option value="{{ $outlet->id }}"
                                                {{ old('entries.0.outlet_id') == $outlet->id ? 'selected' : '' }}>
                                                {{ $outlet->name }} ({{ $outlet->office->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('entries.0.outlet_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="entries[0][moda_id]" id="moda_id_0"
                                        class="form-control @error('entries.0.moda_id') is-invalid @enderror entry-moda"
                                        required>
                                        <option value="">Select Moda</option>
                                        @foreach ($modas as $moda)
                                            <option value="{{ $moda->id }}"
                                                {{ old('entries.0.moda_id') == $moda->id ? 'selected' : '' }}>
                                                {{ $moda->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('entries.0.moda_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="number" name="entries[0][target_year]" id="target_year_0"
                                        class="form-control @error('entries.0.target_year') is-invalid @enderror entry-year"
                                        value="{{ old('entries.0.target_year', date('Y')) }}" min="2000" max="2100"
                                        required>
                                    @error('entries.0.target_year')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="entries[0][target_month]" id="target_month_0"
                                        class="form-control @error('entries.0.target_month') is-invalid @enderror entry-month"
                                        required>
                                        <option value="">Select Month</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ old('entries.0.target_month') == $i ? 'selected' : '' }}>
                                                {{ \DateTime::createFromFormat('!m', $i)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('entries.0.target_month')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="entries[0][target_amount]" id="target_amount_0"
                                    class="form-control @error('entries.0.target_amount') is-invalid @enderror entry-amount"
                                    value="{{ old('entries.0.target_amount') ? number_format(old('entries.0.target_amount'), 2, ',', '.') : '' }}" placeholder="0,00"
                                    required>
                                @error('entries.0.target_amount')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger remove-entry" style="margin-bottom: 2px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="button" class="btn btn-success" id="add-entry">Add Another Entry</button>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Create Target(s)</button>
                    <a href="{{ route('income-targets.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card-body -->
    </div>

    <!-- Hidden template for additional entries -->
    <template id="entry-template">
        <div class="row border border-grey rounded p-3 mb-2 position-relative entry-row">
            <div class="entry-header d-flex justify-content-between align-items-center mb-2">
                <h5>Entry #<span class="entry-number">1</span></h5>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="entries[INDEX][outlet_id]" class="form-control entry-outlet" required>
                            <option value="">Select Outlet</option>
                            @foreach ($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}
                                    ({{ $outlet->office->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="entries[INDEX][moda_id]" class="form-control entry-moda" required>
                            <option value="">Select Moda</option>
                            @foreach ($modas as $moda)
                                <option value="{{ $moda->id }}">{{ $moda->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="number" name="entries[INDEX][target_year]" class="form-control entry-year"
                            value="{{ date('Y') }}" min="2000" max="2100" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="entries[INDEX][target_month]" class="form-control entry-month" required>
                            <option value="">Select Month</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">
                                    {{ \DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="text" name="entries[INDEX][target_amount]"
                        class="form-control entry-amount" placeholder="0,00" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-entry" style="margin-bottom: 2px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('entries-container');
            const template = document.getElementById('entry-template');
            const addButton = document.getElementById('add-entry');
            let entryCount = 1;

            // Format input as user types for amount fields (Indonesian format: dots for thousands, comma for decimal)
            function setupAmountFormatting(inputElement) {
                // Format input as user types
                inputElement.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^\d,]/g, ''); // Only numbers and comma (Indonesian decimal)
                    if (value === '') {
                        e.target.value = '';
                        return;
                    }
                    
                    // Handle decimal (comma in Indonesian format)
                    const decimalIndex = value.indexOf(',');
                    let integerPart, decimalPart = '';
                    
                    if (decimalIndex !== -1) {
                        integerPart = value.substring(0, decimalIndex);
                        decimalPart = value.substring(decimalIndex);
                        // Limit decimal to 2 places
                        decimalPart = decimalPart.substring(0, 3);
                    } else {
                        integerPart = value;
                    }
                    
                    // Format integer part with dots (for thousands)
                    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    
                    e.target.value = integerPart + decimalPart;
                });
                
                // When form is submitted, convert to proper number format (comma to dot for decimal, remove dots)
                const form = inputElement.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        let value = inputElement.value;
                        // Remove dots (thousands separator)
                        value = value.replace(/\./g, '');
                        // Convert comma to dot (for decimal)
                        value = value.replace(',', '.');
                        inputElement.value = value; // Set the cleaned value before submitting
                    });
                }
            }

            // Initialize formatting for the first entry
            const firstAmountInput = document.getElementById('target_amount_0');
            if (firstAmountInput) {
                setupAmountFormatting(firstAmountInput);
            }

            // Function to update IDs, names, and entry numbers for new entries
            function updateEntryFields(entryRow) {
                const inputs = entryRow.querySelectorAll('input, select');
                const labels = entryRow.querySelectorAll('label');
                const entryNumberSpan = entryRow.querySelector('.entry-number');

                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace('INDEX', entryCount);
                    }

                    if (input.id) {
                        input.id = input.id.replace('INDEX', entryCount);
                    }
                });

                labels.forEach(label => {
                    if (label.htmlFor) {
                        label.htmlFor = label.htmlFor.replace('INDEX', entryCount);
                    }
                });

                // Update the entry number in the header
                if (entryNumberSpan) {
                    entryNumberSpan.textContent = entryCount + 1;
                }

                // Setup formatting for the new amount input
                const amountInput = entryRow.querySelector('.entry-amount');
                if (amountInput) {
                    setupAmountFormatting(amountInput);
                }
            }

            // Add new entry
            addButton.addEventListener('click', function() {
                const newEntry = template.content.cloneNode(true);
                const newEntryDiv = document.createElement('div');
                newEntryDiv.appendChild(newEntry);

                // Update fields for this new entry
                updateEntryFields(newEntryDiv);

                container.appendChild(newEntryDiv.firstElementChild);
                entryCount++;
            });

            // Remove entry
            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-entry')) {
                    const row = e.target.closest('.entry-row');
                    if (container.children.length > 1) {
                        row.remove();
                    } else {
                        alert('You need at least one entry.');
                    }
                }
            });
        });
    </script>
@endsection