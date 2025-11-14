<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__wobble" src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Logout button -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                       title="Sign out">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="brand-link">
                <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        @php
                            $name = Auth::user()->name ?? 'Guest';
                            $initials = '';
                            $words = explode(' ', $name);
                            if (count($words) >= 2) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($name, 0, 2));
                            }
                        @endphp
                        <div class="initials-avatar img-circle elevation-2 d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold;">
                            {{ $initials }}
                        </div>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name ?? 'Guest' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item menu-open">
                            <a href="{{ route('dashboard') }}" class="nav-link active">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah()))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    User Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Users</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('users.create') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create User</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah()))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-building"></i>
                                <p>
                                    Office Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('offices.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Offices</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('offices.create') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create Office</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-store"></i>
                                <p>
                                    Outlet Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('outlets.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Outlets</p>
                                    </a>
                                </li>
                                @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah()))
                                <li class="nav-item">
                                    <a href="{{ route('outlets.create') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create Outlet</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                        @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah()))
                        <li class="nav-item">
                            <a href="{{ route('outlet-types.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Outlet Types</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('activity-logs.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Activity Logs</p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content-header')</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div><!--/. container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
            <div class="p-3">
                <h5>Title</h5>
                <p>Sidebar content</p>
            </div>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ config('app.name', 'Laravel') }}</a>.</strong> All rights reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('scripts')

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif

    <script>
        // Fungsi untuk mengganti tema
        function setTheme(themeName) {
            // Simpan tema yang dipilih ke localStorage
            localStorage.setItem('theme', themeName);

            // Hapus kelas tema sebelumnya
            document.body.classList.remove('theme-dark', 'theme-light', 'sidebar-dark-primary', 'sidebar-light-primary');

            if (themeName === 'dark') {
                // Terapkan tema gelap
                document.body.classList.add('theme-dark', 'sidebar-dark-primary');
            } else {
                // Terapkan tema terang
                document.body.classList.add('theme-light', 'sidebar-light-primary');
            }
        }

        // Terapkan tema saat halaman dimuat
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                setTheme('dark');
            } else {
                setTheme('light');
            }
        })();
    </script>

    <script>
        // Fungsi untuk mengganti tema dari dropdown
        function setTheme(themeName) {
            localStorage.setItem('theme', themeName);

            if (themeName === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-mode');
                document.body.classList.remove('light-mode');
            } else if (themeName === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
                document.body.classList.add('light-mode');
                document.body.classList.remove('dark-mode');
            } else if (themeName === 'auto') {
                // Gunakan tema otomatis berdasarkan preferensi sistem
                const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = isDarkMode ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', theme);
                if (isDarkMode) {
                    document.body.classList.add('dark-mode');
                    document.body.classList.remove('light-mode');
                } else {
                    document.body.classList.add('light-mode');
                    document.body.classList.remove('dark-mode');
                }
            }

            // Update ikon tema di dropdown
            const themeSwitcherIcon = document.querySelector('[data-toggle="dropdown"]');
            if (themeName === 'dark') {
                themeSwitcherIcon.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                themeSwitcherIcon.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }

        // Terapkan tema yang disimpan saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
        });
    </script>

    <style>
        /* Custom theme styles */
        .theme-dark {
            background-color: #222d32 !important;
            color: #ffffff;
        }

        .theme-light {
            background-color: #ffffff !important;
            color: #222d32;
        }

        /* Override warna navbar untuk tema gelap */
        .theme-dark.navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }

        .theme-dark.navbar-dark .navbar-nav .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }

        /* Tambahkan transisi halus untuk perubahan tema */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</body>
</html>