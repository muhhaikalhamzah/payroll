<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="noindex, nofollow">
    <meta name="google" content="notranslate">

    <title>{{ $setting->app_name }} | {{ $title }}</title>
    <meta content="{{ $setting->description }}" name="description">
    <meta content="{{ $setting->keywords }}" name="keywords">
    <meta content="Tamus Tahir" name="author">

    <!-- Favicons -->
    <link href="{{ $setting->logo ? asset('storage/' . $setting->logo) : asset('niceadmin/img/laravel.png') }}"
        rel="icon">


    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('niceadmin/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('niceadmin/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('niceadmin/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('niceadmin/vendor/remixicon/remixicon.css') }}" rel="stylesheet">

    <!-- add on -->
    <link rel="stylesheet" href="{{ asset('niceadmin/vendor/dataTables/css/dataTables.bootstrap5.css') }}">
    <link href="{{ asset('niceadmin/vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('niceadmin/vendor/select2/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />

    <!-- Theme Initialization to prevent FOUC -->
    <script>
        const getPreferredTheme = () => {
            if (localStorage.getItem('theme')) {
                return localStorage.getItem('theme');
            }
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', getPreferredTheme());
    </script>

    <!-- Template Main CSS File -->
    <link href="{{ asset('niceadmin/css/style.css') }}" rel="stylesheet">

    <style>
        :root {
            /* ====== UBAH WARNA TEMA DI SINI ====== */
            --theme-bg: #000080;
            --theme-hover: #020260;
            --theme-text: #ffffff;
            --main-bg: #eeeeee;
        }

        [data-bs-theme="dark"] {
            --main-bg: #121416;
            --bs-body-color: #dee2e6;
            --bs-body-bg: #121416;
        }

        [data-bs-theme="dark"] .card, 
        [data-bs-theme="dark"] .modal-content {
            background-color: #1a1d20 !important;
            border-color: #2b3035 !important;
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .sidebar {
            background-color: #1a1d20 !important;
        }
        
        [data-bs-theme="dark"] .sidebar-nav .nav-link {
            background-color: transparent !important;
            color: #dee2e6;
        }

        [data-bs-theme="dark"] .sidebar-nav .nav-link:hover,
        [data-bs-theme="dark"] .sidebar-nav .nav-link:hover i,
        [data-bs-theme="dark"] .sidebar-nav .nav-link:not(.collapsed),
        [data-bs-theme="dark"] .sidebar-nav .nav-link:not(.collapsed) i {
            color: #6ea8fe !important;
        }

        [data-bs-theme="dark"] .sidebar-nav .nav-content a:hover,
        [data-bs-theme="dark"] .sidebar-nav .nav-content a.active {
            color: #6ea8fe !important;
        }

        [data-bs-theme="dark"] table.dataTable tbody tr,
        [data-bs-theme="dark"] table.dataTable tbody td {
            background-color: transparent !important;
            color: #dee2e6 !important;
            border-color: #2b3035;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .select2-container .select2-selection--single {
            background-color: #212529 !important;
            border-color: #343a40 !important;
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .select2-container .select2-selection--single .select2-selection__rendered {
            color: #dee2e6 !important;
        }

        label.required::after {
            content: " *";
            color: red;
            font-weight: bold;
        }

        table.dataTable thead th {
            background-color: var(--theme-bg) !important;
            color: var(--theme-text) !important;
            text-align: center !important;
        }

        #data-table td {
            text-align: center;
            vertical-align: middle;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            background-color: var(--main-bg) !important;
            overflow-x: hidden;
        }

        #main {
            flex: 1;
        }

        .footer {
            text-align: center !important;
            padding: 15px 0;
            background: var(--theme-bg) !important;
            color: var(--theme-text) !important;
        }

        .header {
            background-color: var(--theme-bg) !important;
        }

        .header a,
        .header i,
        .header span,
        .header h6 {
            color: var(--theme-text) !important;
        }

        .header .dropdown-menu {
            background-color: var(--theme-bg) !important;
        }

        .header .dropdown-menu .dropdown-item:hover {
            background-color: var(--theme-hover) !important;
        }

        .header .dropdown-divider {
            border-top-color: rgba(255, 255, 255, 0.2) !important;
        }

        .footer .copyright,
        .footer .credits,
        .footer a {
            color: var(--theme-text) !important;
        }

        .page-header-card {
            background-color: var(--theme-bg) !important;
            color: var(--theme-text) !important;
        }

        .page-header-card h1,
        .page-header-card h2,
        .page-header-card h3,
        .page-header-card h4,
        .page-header-card h5,
        .page-header-card h6 {
            color: var(--theme-text) !important;
            margin-bottom: 0;
        }

        /* === Tampilan Tombol btn-primary === */
        .btn-primary {
            background-color: var(--theme-bg) !important;
            border-color: var(--theme-bg) !important;
            color: var(--theme-text) !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: var(--theme-hover) !important;
            border-color: var(--theme-hover) !important;
            color: var(--theme-text) !important;
        }

        /* === Tampilan Sidebar (Hover & Active) === */
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link:hover i,
        .sidebar-nav .nav-link:not(.collapsed),
        .sidebar-nav .nav-link:not(.collapsed) i {
            color: var(--theme-bg) !important;
        }

        .sidebar-nav .nav-content a:hover,
        .sidebar-nav .nav-content a.active {
            color: var(--theme-bg) !important;
        }

        .sidebar-nav .nav-content a.active i {
            background-color: var(--theme-bg) !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #212529 !important;
        }
    </style>

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard.index') }}" class="logo d-flex align-items-center" id="header-logo-container" style="perspective: 600px;">
                <div id="header-logo-tilt" style="transform-style: preserve-3d; transition: transform 0.15s ease-out; display: flex; align-items: center;">
                    <img src="{{ $setting->logo ? asset('storage/' . $setting->logo) : asset('niceadmin/img/laravel.png') }}"
                        alt="" style="transform: translateZ(15px);">
                    <span class="d-none d-lg-block ms-2 notranslate" style="transform: translateZ(5px);">{{ $setting->app_name }}</span>
                </div>
            </a>
            <!-- Nice icon for dashboard toggle as requested -->
            <div class="d-flex align-items-center">
                <i class="bi bi-menu-button-wide toggle-sidebar-btn ms-3 text-primary fs-5" title="@lang('common.dashboard')" style="cursor: pointer;"></i>
                <i class="bi bi-arrows-fullscreen ms-3 text-secondary fs-5" id="fullscreen-btn" title="@lang('common.fullscreen')" style="cursor: pointer;" onclick="toggleFullScreen()"></i>
            </div>
        </div><!-- End Logo -->

        <form id="switch-user-form" action="{{ route('login.switch_user') }}" method="POST" class="w-auto mx-3 d-none d-md-flex align-items-center" style="min-width: 250px;">
            @csrf
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted" title="Switch Account">
                    <i class="bi bi-people"></i>
                </span>
                <select name="user_id" class="form-select form-select-sm border-start-0 shadow-none bg-light" id="switch-user-select" style="cursor: pointer;">
                    @foreach (\App\Models\User::all() as $u)
                        <option value="{{ $u->id }}" {{ Auth::id() == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role ? (Lang::has('roles.' . strtolower(str_replace(' ', '_', $u->role->name))) ? trans('roles.' . strtolower(str_replace(' ', '_', $u->role->name))) : $u->role->name) : '' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Global Search Component -->
        <x-global-search />

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <!-- Mobile Search Toggle -->
                <li class="nav-item d-md-none">
                    <a class="nav-link nav-icon search-bar-toggle" href="#" onclick="document.getElementById('global-search-wrapper').classList.toggle('d-none'); document.getElementById('global-search-wrapper').classList.toggle('position-absolute'); document.getElementById('global-search-wrapper').style.top = '60px'; document.getElementById('global-search-wrapper').style.left = '0'; document.getElementById('global-search-wrapper').style.width = '100%'; document.getElementById('global-search-wrapper').style.zIndex = '9999'; document.getElementById('global-search-wrapper').style.backgroundColor = 'var(--bs-body-bg)'; document.getElementById('global-search-wrapper').style.padding = '10px';">
                        <i class="bi bi-search"></i>
                    </a>
                </li>

                <!-- Translate Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon d-flex align-items-center" href="#" data-bs-toggle="dropdown" title="@lang('common.translate')">
                        <i class="bi bi-translate fs-5"></i>
                        <span class="d-none d-md-inline ms-1 text-muted" style="font-size: 0.75rem; font-weight: 600;">{{ strtoupper(App::getLocale()) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-sm" style="min-width: 140px; border-radius: 10px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2 mb-1 {{ App::getLocale() == 'id' ? 'active bg-primary text-white rounded' : 'rounded' }}" href="{{ route('language.switch', 'id') }}">
                                <span class="me-2 badge {{ App::getLocale() == 'id' ? 'bg-light text-primary' : 'bg-secondary' }}">ID</span> Indonesia
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2 {{ App::getLocale() == 'en' ? 'active bg-primary text-white rounded' : 'rounded' }}" href="{{ route('language.switch', 'en') }}">
                                <span class="me-2 badge {{ App::getLocale() == 'en' ? 'bg-light text-primary' : 'bg-secondary' }}">EN</span> English
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Theme Toggle Button -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" id="dashboard-theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="bi bi-moon-stars" id="dashboard-theme-icon"></i>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge bg-primary badge-number">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <li class="dropdown-header">
                            You have {{ auth()->user()->unreadNotifications->count() }} new notifications
                            <a href="{{ route('notifications.index') }}"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        @foreach(auth()->user()->unreadNotifications->take(3) as $notification)
                        <li class="notification-item">
                            <i class="bi bi-info-circle text-primary"></i>
                            <div>
                                <h4>{{ $notification->data['title'] ?? 'Notification' }}</h4>
                                <p>{{ $notification->data['message'] ?? '' }}</p>
                                <p>{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        @endforeach
                        <li class="dropdown-footer">
                            <a href="{{ route('notifications.index') }}">Show all notifications</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('niceadmin/img/noprofil.png') }}"
                            alt="Profile" class="rounded-circle">
                        <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>{{ Auth::user()->name }}</h6>
                            <span>{{ Auth::user()->role ? (Lang::has('roles.' . strtolower(str_replace(' ', '_', Auth::user()->role->name))) ? trans('roles.' . strtolower(str_replace(' ', '_', Auth::user()->role->name))) : Auth::user()->role->name) : '' }}</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard.show') }}">
                                <i class="bi bi-person"></i>
                                <span>@lang('common.profile')</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard.edit') }}">
                                <i class="bi bi-gear"></i>
                                <span>@lang('common.settings')</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard.index') }}">
                                <i class="bi bi-question-circle"></i>
                                <span>@lang('common.help')</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                                data-bs-target="#logoutModal">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>@lang('common.sign_out')</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard.*') ? '' : 'collapsed' }}"
                    href="{{ route('dashboard.index') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @can('view-payroll-runs')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payroll-runs.*') ? '' : 'collapsed' }}" href="{{ route('payroll-runs.index') }}">
                  <i class="bi bi-cash-stack"></i>
                  <span>Payroll Runs</span>
                </a>
            </li>
            @endcan

            @can('view-thr-runs')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('thr-runs.*') ? '' : 'collapsed' }}" href="{{ route('thr-runs.index') }}">
                    <i class="bi bi-gift"></i>
                    <span>THR Runs</span>
                </a>
            </li>
            @endcan

            @if(Auth::user()->role?->slug === 'super-admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setting.*') ? '' : 'collapsed' }}"
                    href="{{ route('setting.index') }}">
                    <i class='bx bx-cog'></i>
                    <span>Setting</span>
                </a>
            </li>
            @endif

            @can('view-attendances')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('attendance-records.*') ? '' : 'collapsed' }}"
                        href="{{ route('attendance-records.index') }}">
                        <i class='bx bx-check-circle'></i>
                        <span>Attendance</span>
                    </a>
                </li>
            @endcan

            @can('view-overtime-requests')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('overtime-requests.*') ? '' : 'collapsed' }}"
                        href="{{ route('overtime-requests.index') }}">
                        <i class='bx bx-time'></i>
                        <span>Overtime</span>
                    </a>
                </li>
            @endcan

            @can('view-leave-requests')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave-requests.*') ? '' : 'collapsed' }}"
                        href="{{ route('leave-requests.index') }}">
                        <i class='bx bx-calendar-event'></i>
                        <span>Leave Requests</span>
                    </a>
                </li>
            @endcan

            @can('view-employee-loans-own')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('employee-loans.*') ? '' : 'collapsed' }}"
                        href="{{ route('employee-loans.index') }}">
                        <i class='bx bx-wallet'></i>
                        <span>Employee Loans</span>
                    </a>
                </li>
            @endcan

            @can('manage-leave-balances')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave-balances.*') ? '' : 'collapsed' }}"
                        href="{{ route('leave-balances.index') }}">
                        <i class='bx bx-calendar-check'></i>
                        <span>Leave Balances</span>
                    </a>
                </li>
            @endcan

            @can('manage-leave-types')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave-types.*') ? '' : 'collapsed' }}"
                        href="{{ route('leave-types.index') }}">
                        <i class='bx bx-calendar-star'></i>
                        <span>Leave Types</span>
                    </a>
                </li>
            @endcan



            @can('view-departments')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('departments.*') ? '' : 'collapsed' }}"
                        href="{{ route('departments.index') }}">
                        <i class='bx bx-building'></i>
                        <span>Departments</span>
                    </a>
                </li>
            @endcan

            @can('view-positions')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('positions.*') ? '' : 'collapsed' }}"
                        href="{{ route('positions.index') }}">
                        <i class='bx bx-briefcase'></i>
                        <span>Positions</span>
                    </a>
                </li>
            @endcan

            @can('view-salary-components')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('salary-components.*') ? '' : 'collapsed' }}"
                        href="{{ route('salary-components.index') }}">
                        <i class='bx bx-list-ul'></i>
                        <span>Salary Components</span>
                    </a>
                </li>
            @endcan

            @can('view-salary-structures')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('salary-structures.*') ? '' : 'collapsed' }}"
                        href="{{ route('salary-structures.index') }}">
                        <i class='bx bx-money'></i>
                        <span>Salary Structures</span>
                    </a>
                </li>
            @endcan

            @can('view-employees')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('employees.*') ? '' : 'collapsed' }}"
                        href="{{ route('employees.index') }}">
                        <i class='bx bx-group'></i>
                        <span>Employees</span>
                    </a>
                </li>
            @endcan

            @can('view-users')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.*') ? '' : 'collapsed' }}"
                        href="{{ route('user.index') }}">
                        <i class='bx bx-user-pin'></i>
                        <span>User</span>
                    </a>
                </li>
            @endcan

            @if(Auth::user()->role?->slug === 'super-admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit-logs.*') ? '' : 'collapsed' }}"
                        href="{{ route('audit-logs.index') }}">
                        <i class='bx bx-shield-quarter'></i>
                        <span>Audit Logs</span>
                    </a>
                </li>
            @endif

        </ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main flex-grow-1">

        <div class="card shadow p-3 page-header-card">
            <h5 class="fw-bold m-0">{{ $title }}</h5>
        </div>

        {{ $slot }}

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            {{ $setting->copyright }}
        </div>
        <div class="credits">
            <!-- All the links in the footer should remain intact. -->
            <!-- You can delete the links only if you purchased the pro version. -->
            <!-- Licensing information: https://bootstrapmade.com/license/ -->
            <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
            Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
        </div>
    </footer>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    @stack('modals')

    {{-- modal delete --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">

            <form action="" method="post" id="form-delete">
                @csrf
                @method('delete')

                <div class="modal-content">
                    <div class="modal-body">
                        Apakah anda ingin menghapus data?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ya, hapus data</button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    {{-- modal logout --}}
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    Anda yakin ingin logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <a href="{{ route('login.logout') }}" class="btn btn-primary">Ya, logout!</a>
                </div>
            </div>
        </div>
    </div>

    <!-- add on -->
    <script src="{{ asset('niceadmin/vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/parsley/parsley.min.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/sweetalert2/sweetalert2@11') }}"></script>
    <script src="{{ asset('niceadmin/vendor/dataTables/js/dataTables.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/dataTables/js/dataTables.bootstrap5.js') }}"></script>

    <!-- Vendor JS Files -->
    <script src="{{ asset('niceadmin/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('niceadmin/vendor/select2/js/select2.min.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('niceadmin/js/main.js') }}"></script>

    <script>
        new DataTable('#data-table', {
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50, 100]
        });

        $('.form').parsley({
            errorClass: 'is-invalid text-red',
            successClass: 'is-valid',
            errorsWrapper: '<span class="invalid-feedback"></span>',
            errorTemplate: '<span></span>',
            trigger: 'change'
        });

        $('#upload').on('change', function(event) {
            $('#preview').attr('src', URL.createObjectURL(event.target.files[0]));
        })

        $('#upload-2').on('change', function(event) {
            $('#preview-2').attr('src', URL.createObjectURL(event.target.files[0]));
        })

        $('.select2-default').select2({
            theme: 'bootstrap-5',
            width: "100%",
        })

        $('#switch-user-select').on('change', function() {
            $('#switch-user-form').submit();
        });

        let flashSuccess = "{{ session('success') ?? '' }}";
        if (flashSuccess) {
            Swal.fire({
                title: "Mantap",
                text: flashSuccess,
                icon: "success",
                timer: 1000,
                timerProgressBar: true
            });
        }

        let flashError = "{{ session('error') ?? '' }}";
        if (flashError) {
            Swal.fire({
                title: "Waduh...",
                text: flashError,
                icon: "error"
            });
        }
    </script>

    @stack('scripts')

    <!-- Theme Toggle Logic -->
    <script>
        function toggleFullScreen() {
            const btn = document.getElementById('fullscreen-btn');
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    if(btn) {
                        btn.classList.remove('bi-arrows-fullscreen');
                        btn.classList.add('bi-fullscreen-exit');
                    }
                }).catch(err => {
                    console.log(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().then(() => {
                        if(btn) {
                            btn.classList.remove('bi-fullscreen-exit');
                            btn.classList.add('bi-arrows-fullscreen');
                        }
                    });
                }
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeBtn = document.getElementById('dashboard-theme-toggle');
            const themeIcon = document.getElementById('dashboard-theme-icon');

            const setTheme = (theme) => {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                if (theme === 'dark') {
                    themeIcon.classList.remove('bi-moon-stars');
                    themeIcon.classList.add('bi-sun');
                } else {
                    themeIcon.classList.remove('bi-sun');
                    themeIcon.classList.add('bi-moon-stars');
                }
            };

            // Set initial icon
            setTheme(document.documentElement.getAttribute('data-bs-theme'));

            themeBtn.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        });
    </script>

    <!-- Header Logo 3D Parallax Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const headerLogoContainer = document.getElementById('header-logo-container');
            const headerLogoTilt = document.getElementById('header-logo-tilt');

            if (headerLogoContainer && headerLogoTilt) {
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const isFinePointer = window.matchMedia('(pointer: fine)').matches;

                if (!prefersReducedMotion && isFinePointer) {
                    headerLogoContainer.addEventListener('mousemove', (e) => {
                        const rect = headerLogoContainer.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        
                        const centerX = rect.width / 2;
                        const centerY = rect.height / 2;
                        
                        const percentX = (x - centerX) / centerX;
                        const percentY = (y - centerY) / centerY;
                        
                        const maxRotation = 12; // tilt degree
                        const rotateX = percentY * -maxRotation; 
                        const rotateY = percentX * maxRotation;
                        
                        headerLogoTilt.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                    });

                    headerLogoContainer.addEventListener('mouseleave', () => {
                        headerLogoTilt.style.transform = `rotateX(0deg) rotateY(0deg)`;
                    });
                }
            }
        });
    </script>
    
    {{-- <x-ticker /> Temporarily hidden as per user request --}}

</body>

</html>
