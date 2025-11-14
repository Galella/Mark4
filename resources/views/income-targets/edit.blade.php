@extends('layouts.adminlte')

@section('title', 'Edit Income Target')

@section('content-header', 'Edit Income Target')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('income-targets.index') }}">Income Targets</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Income Target</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form method="POST" action="{{ route('income-targets.update', $incomeTarget) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="outlet_id">Outlet *</label>
                            <select name="outlet_id" id="outlet_id" class="form-control @error('outlet_id') is-invalid @enderror" required>
                                <option value="">Select Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id', $incomeTarget->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }} ({{ $outlet->office->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('outlet_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="moda_id">Moda *</label>
                            <select name="moda_id" id="moda_id" class="form-control @error('moda_id') is-invalid @enderror" required>
                                <option value="">Select Moda</option>
                                @foreach($modas as $moda)
                                    <option value="{{ $moda->id }}" {{ old('moda_id', $incomeTarget->moda_id) == $moda->id ? 'selected' : '' }}>
                                        {{ $moda->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('moda_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_year">Year *</label>
                            <input type="number" name="target_year" id="target_year" class="form-control @error('target_year') is-invalid @enderror"
                                   value="{{ old('target_year', $incomeTarget->target_year) }}" min="2000" max="2100" required>
                            @error('target_year')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_month">Month *</label>
                            <select name="target_month" id="target_month" class="form-control @error('target_month') is-invalid @enderror" required>
                                <option value="">Select Month</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('target_month', $incomeTarget->target_month) == $i ? 'selected' : '' }}>
                                        {{ \DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            @error('target_month')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="target_amount">Target Amount (Rp) *</label>
                    <input type="text" name="target_amount" id="target_amount" class="form-control @error('target_amount') is-invalid @enderror"
                           value="{{ number_format(old('target_amount', $incomeTarget->target_amount), 0, ',', '.') }}" placeholder="0,00" required>
                    @error('target_amount')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3" placeholder="Enter description">{{ old('description', $incomeTarget->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Target</button>
                    <a href="{{ route('income-targets.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card-body -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetAmountInput = document.getElementById('target_amount');
            
            // Format input as user types (Indonesian format: dots for thousands, comma for decimal)
            if (targetAmountInput) {
                targetAmountInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^\d,]/g, ''); // Only numbers and comma
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
                    
                    // Format integer part with dots
                    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    
                    e.target.value = integerPart + decimalPart;
                });
                
                // When form is submitted, convert to proper number format (comma to dot for decimal, remove dots)
                const form = targetAmountInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        let value = targetAmountInput.value;
                        // Remove dots (thousands separator)
                        value = value.replace(/\./g, '');
                        // Convert comma to dot (for decimal)
                        value = value.replace(',', '.');
                        targetAmountInput.value = value; // Set the cleaned value before submitting
                    });
                }
            }
        });
    </script>
@endsection