<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

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
            <img class="animation__wobble" src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png"
                alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Logout button only -->
                <li class="nav-item">
                    <a href="{{ route('logout') }}" class="nav-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        title="Sign out">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">

            <!-- Sidebar -->
            <div class="sidebar d-flex flex-column">
                <!-- Sidebar Menu -->
                <nav class="mt-2 flex-grow-1">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item mb-1">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Divider -->
                        <li class="nav-header mt-3 mb-1 text-uppercase" style="font-size: 0.75em;">Master Data</li>

                        @can('viewAny', \App\Models\User::class)
                            <li class="nav-item mb-1">
                                <a href="{{ route('users.index') }}"
                                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Users</p>
                                </a>
                            </li>
                        @endcan

                        @can('viewAny', \App\Models\Office::class)
                            <li class="nav-item mb-1">
                                <a href="{{ route('offices.index') }}"
                                    class="nav-link {{ request()->routeIs('offices.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Offices</p>
                                </a>
                            </li>
                        @endcan

                        @can('viewAny', \App\Models\Outlet::class)
                            <li class="nav-item mb-1">
                                <a href="{{ route('outlets.index') }}"
                                    class="nav-link {{ request()->routeIs('outlets.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-store"></i>
                                    <p>Outlets</p>
                                </a>
                            </li>
                        @endcan

                        @can('viewAny', \App\Models\OutletType::class)
                            <li class="nav-item mb-1">
                                <a href="{{ route('outlet-types.index') }}"
                                    class="nav-link {{ request()->routeIs('outlet-types.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Outlet Types</p>
                                </a>
                            </li>
                        @endcan

                        @can('viewAny', \App\Models\IncomeTarget::class)
                            <li class="nav-item mb-1">
                                <a href="{{ route('income-targets.index') }}"
                                    class="nav-link {{ request()->routeIs('income-targets.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bullseye"></i>
                                    <p>Income Targets</p>
                                </a>
                            </li>
                        @endcan

                        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() || Auth::user()->isAdminArea())
                            <li class="nav-item mb-1">
                                <a href="{{ route('modas.index') }}"
                                    class="nav-link {{ request()->routeIs('modas.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-road"></i>
                                    <p>Modas</p>
                                </a>
                            </li>
                        @endif

                        @if (Auth::user()->isAdminOutlet())
                            <li class="nav-item mb-1">
                                <a href="{{ route('daily-incomes.index') }}"
                                    class="nav-link {{ request()->routeIs('daily-incomes.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-money-bill-wave"></i>
                                    <p>Daily Income</p>
                                </a>
                            </li>
                        @endif

                        <!-- Divider -->
                        <li class="nav-header mt-3 mb-1 text-uppercase" style="font-size: 0.75em;">Laporan</li>

                        <li class="nav-item mb-1">
                            <a href="{{ route('reports.daily-income.index') }}"
                                class="nav-link {{ request()->routeIs('reports.daily-income.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Daily Income Report</p>
                            </a>
                        </li>

                        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminWilayah() || Auth::user()->isAdminArea())
                            <li class="nav-item mb-1">
                                <a href="{{ route('reports.target-realization.index') }}"
                                    class="nav-link {{ request()->routeIs('reports.target-realization.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Target vs Realisasi</p>
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
            <div class="float-right d-none d-sm-inline-block">
                <div class="info">
                    <a href="{{ route('dashboard') }}" class="d-block"
                        style="font-weight: 600; font-size: 0.9em;">{{ Auth::user()->name ?? 'Guest' }}
                    </a>
                </div>
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; {{ date('Y') }} <a
                    href="#">{{ config('app.name', 'Laravel') }}</a>.</strong> All rights reserved.
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

    <script>
        // Fungsi untuk konfirmasi logout
        function confirmLogout(event) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form logout
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('scripts')

    @if (session('success'))
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

    @if (session('error'))
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

        /* Custom pagination styles */
        .pagination {
            margin-bottom: 0;
            margin-top: 1rem;
        }

        .pagination .page-item .page-link {
            color: #007bff;
            border: 1px solid #ced4da;
            padding: 0.5rem 0.75rem;
            line-height: 1.25;
            background-color: #fff;
            border-radius: 0.25rem;
            margin: 0 0.1rem;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: 0.25rem;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.6;
        }

        .pagination .page-item:not(.active) .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #0056b3;
        }

        .pagination-wrapper {
            margin: 0;
        }

        /* Additional styles for table info text */
        .table-info-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</body>

</html>
