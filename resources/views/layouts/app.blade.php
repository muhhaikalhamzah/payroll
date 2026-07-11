<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">



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

    <!-- Template Main CSS File -->
    <link href="{{ asset('niceadmin/css/style.css') }}" rel="stylesheet">

    <style>
        :root {
            /* ====== UBAH WARNA TEMA DI SINI ====== */
            --theme-bg: #000080;
            --theme-hover: #020260;
            /* warna lebih gelap untuk efek hover */
            --theme-text: #ffffff;
            --main-bg: #eeeeee;
            /* warna background utama / halaman */
            /* ===================================== */
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
            <a href="{{ route('dashboard.index') }}" class="logo d-flex align-items-center">
                <img src="{{ $setting->logo ? asset('storage/' . $setting->logo) : asset('niceadmin/img/laravel.png') }}"
                    alt="">
                <span class="d-none d-lg-block">{{ $setting->app_name }}</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <form id="switch-user-form" action="{{ route('login.switch_user') }}" method="POST" class="w-100 mx-2">
            @csrf
            <select name="user_id" class="form-control select2-default" id="switch-user-select">
                @foreach (\App\Models\User::all() as $u)
                    <option value="{{ $u->id }}" {{ Auth::id() == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->role }})
                    </option>
                @endforeach
            </select>
        </form>

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

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
                            <span>{{ Auth::user()->role }}</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard.show') }}">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard.edit') }}">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                                data-bs-target="#logoutModal">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
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

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setting.*') ? '' : 'collapsed' }}"
                    href="{{ route('setting.index') }}">
                    <i class='bx bx-cog'></i>
                    <span>Setting</span>
                </a>
            </li>

            @if (Auth::user()->role == 'Superadmin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.*') ? '' : 'collapsed' }}"
                        href="{{ route('user.index') }}">
                        <i class='bx bx-user-pin'></i>
                        <span>User</span>
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

</body>

</html>
