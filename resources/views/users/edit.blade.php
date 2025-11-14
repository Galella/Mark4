@extends('layouts.adminlte')

@section('title', 'Edit User')

@section('content-header', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit User: {{ $user->name }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter full name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Leave blank to keep current password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Retype new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select Role</option>
                                @if(Auth::user()->isSuperAdmin() && $user->role !== 'super_admin')
                                    <option value="admin_wilayah" {{ old('role', $user->role) === 'admin_wilayah' ? 'selected' : '' }}>Admin Wilayah</option>
                                    <option value="admin_area" {{ old('role', $user->role) === 'admin_area' ? 'selected' : '' }}>Admin Area</option>
                                    <option value="admin_outlet" {{ old('role', $user->role) === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet</option>
                                @elseif(Auth::user()->isAdminWilayah() && $user->role !== 'super_admin')
                                    @if($user->role !== 'admin_wilayah')
                                        <option value="admin_area" {{ old('role', $user->role) === 'admin_area' ? 'selected' : '' }}>Admin Area</option>
                                        <option value="admin_outlet" {{ old('role', $user->role) === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet</option>
                                    @else
                                        <option value="admin_wilayah" {{ old('role', $user->role) === 'admin_wilayah' ? 'selected' : '' }}>Admin Wilayah</option>
                                    @endif
                                @elseif(Auth::user()->isAdminArea() && $user->role !== 'super_admin' && $user->role !== 'admin_wilayah' && $user->role !== 'admin_area')
                                    <option value="admin_outlet" {{ old('role', $user->role) === 'admin_outlet' ? 'selected' : '' }}>Admin Outlet</option>
                                @endif
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group" id="office-field" style="{{ in_array(old('role', $user->role), ['admin_wilayah', 'admin_area']) ? 'display: block;' : 'display: none;' }}">
                            <label for="office_id">Office</label>
                            <select class="form-control @error('office_id') is-invalid @enderror" id="office_id" name="office_id">
                                <option value="">Select Office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id', $user->office_id) == $office->id ? 'selected' : '' }}>
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
                        
                        <div class="form-group" id="outlet-field" style="{{ old('role', $user->role) === 'admin_outlet' ? 'display: block;' : 'display: none;' }}">
                            <label for="outlet_id">Outlet</label>
                            <select class="form-control @error('outlet_id') is-invalid @enderror" id="outlet_id" name="outlet_id">
                                <option value="">Select Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id', $user->outlet_id) == $outlet->id ? 'selected' : '' }}>
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
                        <button type="submit" class="btn btn-primary">Update User</button>
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