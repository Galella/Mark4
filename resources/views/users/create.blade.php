@extends('layouts.adminlte')

@section('title', 'Create User')

@section('content-header', 'Create New User')

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <!-- Custom Select2 styles to match AdminLTE theme -->
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #d2d6de;
            border-radius: 0;
            padding: 0.375rem 0.75rem;
            height: calc(2.25rem + 2px);
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #444;
            line-height: calc(2.25rem - 6px);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem - 6px);
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-dropdown {
            border: 1px solid #d2d6de;
            border-radius: 0;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }
    </style>
@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Menunggu semua script dimuat sebelum inisialisasi Select2
        $(document).ready(function() {
            // Pastikan select2 library sudah dimuat
            if (typeof $.fn.select2 === 'function') {
                // Inisialisasi Select2 untuk outlet field
                $('.select2').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select an option',
                    allowClear: true
                });
            } else {
                // Jika belum dimuat, coba lagi setelah 500ms
                setTimeout(function() {
                    if (typeof $.fn.select2 === 'function') {
                        $('.select2').select2({
                            theme: 'bootstrap4',
                            placeholder: 'Select an option',
                            allowClear: true
                        });
                    }
                }, 500);
            }
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Create New User</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" placeholder="Enter full name"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" placeholder="Enter email" value="{{ old('email') }}"
                                        required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Password" required>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Retype password" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role"
                                required>
                                <option value="">Select Role</option>
                                @if (Auth::user()->isSuperAdmin())
                                    <option value="admin_wilayah" {{ old('role') === 'admin_wilayah' ? 'selected' : '' }}>
                                        Admin Wilayah</option>
                                    <option value="admin_area" {{ old('role') === 'admin_area' ? 'selected' : '' }}>
                                        Admin
                                        Area</option>
                                    <option value="admin_outlet" {{ old('role') === 'admin_outlet' ? 'selected' : '' }}>
                                        Admin Outlet</option>
                                @elseif(Auth::user()->isAdminWilayah())
                                    <option value="admin_area" {{ old('role') === 'admin_area' ? 'selected' : '' }}>
                                        Admin
                                        Area</option>
                                    <option value="admin_outlet" {{ old('role') === 'admin_outlet' ? 'selected' : '' }}>
                                        Admin Outlet</option>
                                @elseif(Auth::user()->isAdminArea())
                                    <option value="admin_outlet" {{ old('role') === 'admin_outlet' ? 'selected' : '' }}>
                                        Admin Outlet</option>
                                @endif
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="office-field" style="display: none;">
                            <label for="office_id">Office</label>
                            <select class="form-control @error('office_id') is-invalid @enderror" id="office_id"
                                name="office_id">
                                <option value="">Select Office</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}"
                                        {{ old('office_id') == $office->id ? 'selected' : '' }}>
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
                            <select class="form-control select2 @error('outlet_id') is-invalid @enderror" id="outlet_id"
                                name="outlet_id" style="width: 100%;">
                                <option value="">Select Outlet</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}"
                                        {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
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

    <!-- Include Select2 script and initialize it -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 after a delay to ensure the library is loaded
            setTimeout(function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({
                        theme: 'bootstrap4',
                        placeholder: 'Select an outlet',
                        allowClear: true,
                        width: 'resolve' // Biarkan Select2 menentukan lebar otomatis sesuai dengan container
                    });

                    console.log('Select2 initialized successfully');
                } else {
                    console.error('Select2 library is not available');
                }
            }, 300);
        });
    </script>
@endsection
