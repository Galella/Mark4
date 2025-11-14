@extends('layouts.adminlte')

@section('title', 'Register')

@section('content-header', 'Create New User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Create New User</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter full name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Retype password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin_wilayah" {{ old('role') === 'admin_wilayah' ? 'selected' : '' }}>Admin Wilayah</option>
                                <option value="admin_area" {{ old('role') === 'admin_area' ? 'selected' : '' }}>Admin Area</option>
                                <option value="admin_outlet" {{ old('role') === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet</option>
                                <!-- Super admin hanya bisa dibuat secara manual di database -->
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group" id="office-field" style="display: none;">
                            <label for="office_id">Office</label>
                            <select class="form-control @error('office_id') is-invalid @enderror" id="office_id" name="office_id">
                                <option value="">Select Office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }} ({{ $office->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('office_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group" id="outlet-field" style="display: none;">
                            <label for="outlet_id">Outlet</label>
                            <select class="form-control @error('outlet_id') is-invalid @enderror" id="outlet_id" name="outlet_id">
                                <option value="">Select Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('outlet_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="{{ route('users.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Tampilkan field office atau outlet tergantung role yang dipilih
            $('#role').change(function() {
                const selectedRole = $(this).val();
                
                // Sembunyikan semua field dulu
                $('#office-field').hide();
                $('#outlet-field').hide();
                
                // Tampilkan field yang sesuai
                if (selectedRole === 'admin_wilayah' || selectedRole === 'admin_area') {
                    $('#office-field').show();
                } else if (selectedRole === 'admin_outlet') {
                    $('#outlet-field').show();
                }
            });
            
            // Trigger change saat halaman dimuat untuk menangani nilai old
            $('#role').trigger('change');
        });
    </script>
@endsection