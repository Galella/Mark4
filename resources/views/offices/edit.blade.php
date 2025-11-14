@extends('layouts.adminlte')

@section('title', 'Edit Office')

@section('content-header', 'Edit Office')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('offices.index') }}">Offices</a></li>
    <li class="breadcrumb-item active">Edit Office</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Office</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('offices.update', $office) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Office Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter office name" value="{{ old('name', $office->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Office Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" placeholder="Enter office code" value="{{ old('code', $office->code) }}" required>
                            @error('code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type">Office Type</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="pusat" {{ old('type', $office->type) === 'pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                                <option value="wilayah" {{ old('type', $office->type) === 'wilayah' ? 'selected' : '' }}>Kantor Wilayah</option>
                                <option value="area" {{ old('type', $office->type) === 'area' ? 'selected' : '' }}>Kantor Area</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="parent-field">
                            <label for="parent_id">Parent Office</label>
                            <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">Select Parent Office (if applicable)</option>
                                @foreach($offices as $officeOption)
                                    <option value="{{ $officeOption->id }}" {{ old('parent_id', $office->parent_id) == $officeOption->id ? 'selected' : '' }}>
                                        {{ $officeOption->name }} ({{ ucfirst($officeOption->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Enter description" rows="3">{{ old('description', $office->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Enter address" rows="3">{{ old('address', $office->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Enter phone number" value="{{ old('phone', $office->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email" value="{{ old('email', $office->email) }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pic_name">PIC Name</label>
                            <input type="text" class="form-control @error('pic_name') is-invalid @enderror" id="pic_name" name="pic_name" placeholder="Enter PIC name" value="{{ old('pic_name', $office->pic_name) }}">
                            @error('pic_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pic_phone">PIC Phone</label>
                            <input type="text" class="form-control @error('pic_phone') is-invalid @enderror" id="pic_phone" name="pic_phone" placeholder="Enter PIC phone" value="{{ old('pic_phone', $office->pic_phone) }}">
                            @error('pic_phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $office->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Office</button>
                        <a href="{{ route('offices.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Tampilkan atau sembunyikan field parent tergantung tipe kantor
            $('#type').change(function() {
                const selectedType = $(this).val();

                // Kantor pusat tidak punya parent, yang lain bisa punya
                if (selectedType === 'pusat') {
                    $('#parent-field').hide();
                } else {
                    $('#parent-field').show();
                }
            });

            // Trigger change saat halaman dimuat untuk menangani nilai old
            $('#type').trigger('change');
        });
    </script>
@endsection