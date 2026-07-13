<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->app_name ?? 'App' }} | {{ $title ?? 'Login' }}</title>

    <!-- Favicon -->
    <link href="{{ $setting->logo ? asset('storage/' . $setting->logo) : asset('niceadmin/img/laravel.png') }}"
        rel="icon">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Matches the modern blue theme
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Theme Initialization to prevent FOUC -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Logo Formation Animations (Faster) */
        @media (prefers-reduced-motion: no-preference) {
            .play-anim #anim-dot-1 { animation: scatterDot1 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0s both; }
            .play-anim #anim-dot-2 { animation: scatterDot2 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; }
            .play-anim #anim-dot-3 { animation: scatterDot3 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both; }
            
            .play-anim #anim-line-1 {
                stroke-dasharray: 40; stroke-dashoffset: 40;
                animation: drawLine1 0.25s ease-out 0.7s both;
            }
            .play-anim #anim-line-2 {
                stroke-dasharray: 65; stroke-dashoffset: 65;
                animation: drawLine2 0.35s ease-out 0.9s both;
            }
            
            .play-anim #anim-bg {
                animation: fadeInBg 0.4s ease-out 1.2s both;
            }

            .play-anim .logo-shadow {
                animation: fadeShadow 0.6s ease-out 1.2s both;
            }

            /* Subtle float after formation */
            .play-anim.logo-wrapper {
                animation: floatLogo 4s ease-in-out 1.6s infinite;
            }

            #title-anim {
                animation: titleEntrance 0.8s cubic-bezier(0.4, 0, 0.2, 1) 1.2s both, textGlowSweep 2s ease-in-out 1.5s both;
                transform-origin: bottom center;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #anim-dot-1, #anim-dot-2, #anim-dot-3, #anim-line-1, #anim-line-2, #anim-bg, .logo-shadow, #title-anim, .play-anim.logo-wrapper {
                animation: none !important;
                stroke-dashoffset: 0 !important;
                opacity: 1 !important;
                transform: none !important;
                text-shadow: none !important;
            }
        }

        .tagline-item {
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            visibility: hidden;
        }
        .tagline-item.active {
            opacity: 1;
            transform: translateY(0);
            position: relative;
            visibility: visible;
        }
        .tagline-item.exit {
            opacity: 0;
            transform: translateY(-15px);
            position: absolute;
            visibility: hidden;
        }

        @keyframes scatterDot1 {
            0% { transform: translate(-100px, -100px) scale(0) rotate(-180deg); opacity: 0; }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes scatterDot2 {
            0% { transform: translate(0px, 150px) scale(0) rotate(90deg); opacity: 0; }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes scatterDot3 {
            0% { transform: translate(150px, -50px) scale(0) rotate(180deg); opacity: 0; }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes drawLine1 { to { stroke-dashoffset: 0; } }
        @keyframes drawLine2 { to { stroke-dashoffset: 0; } }

        @keyframes fadeInBg {
            0% { opacity: 0; transform: scale(0.9); transform-origin: 50px 50px; }
            100% { opacity: 1; transform: scale(1); transform-origin: 50px 50px; }
        }
        @keyframes fadeShadow {
            0% { opacity: 0; transform: translateY(4px) scale(0.85); }
            100% { opacity: 1; transform: translateY(16px) scale(0.95); }
        }

        @keyframes titleEntrance {
            0% { opacity: 0; transform: translateY(30px) rotateX(-45deg); }
            100% { opacity: 1; transform: translateY(0) rotateX(0deg); }
        }

        @keyframes textGlowSweep {
            0% { text-shadow: 0 0 0px rgba(255,255,255,0); }
            50% { text-shadow: 0 0 15px rgba(255,255,255,0.7), 0 0 25px rgba(255,255,255,0.5); }
            100% { text-shadow: 0 0 0px rgba(255,255,255,0); }
        }

        @keyframes floatLogo {
            0% { transform: translateY(0) rotateY(0deg); }
            50% { transform: translateY(-8px) rotateY(10deg); }
            100% { transform: translateY(0) rotateY(0deg); }
        }

    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased selection:bg-brand-500 selection:text-white transition-colors duration-300">

    <div class="min-h-screen flex">
        <!-- Left Side: Visual / Branding (Hidden on mobile) -->
        <div id="left-panel" class="hidden lg:flex lg:w-1/2 relative bg-brand-900 overflow-hidden items-center justify-center">
            <!-- Abstract Background Image -->
            <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
                class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Background">

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600/80 to-brand-900/90"></div>

            <!-- Branding Content -->
            <div class="relative z-10 p-12 text-center text-white max-w-lg" style="perspective: 1000px;">

                <!-- Animated SVG Logo Formation -->
                <div class="mb-10 relative inline-block perspective-1000">
                    <div class="logo-wrapper relative w-32 h-32 mx-auto play-anim" id="logo-anim-container" style="transform-style: preserve-3d;">
                        
                        <!-- Static shadow on the wall (fades in) -->
                        <div class="absolute inset-0 bg-black/20 rounded-[1.25rem] blur-xl logo-shadow" style="z-index: 0;"></div>

                        <!-- Logo SVG -->
                        <div class="absolute inset-0 z-10 drop-shadow-2xl">
                            <svg viewBox="0 0 100 100" class="w-full h-full overflow-visible">
                                <!-- Navy Background -->
                                <rect id="anim-bg" x="0" y="0" width="100" height="100" rx="20" fill="#000080" />
                                
                                <!-- Checkmark Lines -->
                                <path id="anim-line-1" d="M 28 58 L 45 75" stroke="#ffffff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                <path id="anim-line-2" d="M 45 75 L 72 32" stroke="#ffffff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                
                                <!-- Checkmark Dots -->
                                <circle id="anim-dot-1" cx="28" cy="58" r="6" fill="#ffffff" />
                                <circle id="anim-dot-2" cx="45" cy="75" r="6" fill="#ffffff" />
                                <circle id="anim-dot-3" cx="72" cy="32" r="6" fill="#ffffff" />
                            </svg>
                        </div>
                    </div>
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight mb-4" id="title-anim">{{ $setting->app_name ?? 'HRIS Payroll' }}</h1>
                
                <!-- Tagline Carousel -->
                <div class="relative w-full overflow-hidden mt-4 min-h-[5rem]" id="desc-anim" style="animation: titleEntrance 0.8s cubic-bezier(0.4, 0, 0.2, 1) 1.5s both;">
                    <div class="tagline-item active" id="tagline-0">
                        <p class="text-brand-100 text-lg font-light leading-relaxed">Sistem Informasi SDM (HRIS) terintegrasi berbasis Laravel.</p>
                    </div>
                    <div class="tagline-item" id="tagline-1">
                        <p class="text-brand-100 text-lg font-light leading-relaxed">Kelola data pegawai, kehadiran, dan cuti dengan mudah.</p>
                    </div>
                    <div class="tagline-item" id="tagline-2">
                        <p class="text-brand-100 text-lg font-light leading-relaxed">Penggajian otomatis, akurat, cepat, dan terpusat.</p>
                    </div>
                    <div class="tagline-item" id="tagline-3">
                        <p class="text-brand-100 text-lg font-light leading-relaxed">Pinjaman karyawan terpantau secara transparan.</p>
                    </div>
                </div>

                <!-- Carousel Indicators -->
                <div class="flex justify-center space-x-2 mt-6" style="animation: titleEntrance 0.8s cubic-bezier(0.4, 0, 0.2, 1) 1.5s both;">
                    <button class="carousel-dot w-2 h-2 rounded-full bg-white opacity-100 transition-opacity duration-300" data-index="0"></button>
                    <button class="carousel-dot w-2 h-2 rounded-full bg-white opacity-30 transition-opacity duration-300" data-index="1"></button>
                    <button class="carousel-dot w-2 h-2 rounded-full bg-white opacity-30 transition-opacity duration-300" data-index="2"></button>
                    <button class="carousel-dot w-2 h-2 rounded-full bg-white opacity-30 transition-opacity duration-300" data-index="3"></button>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 md:p-16 bg-white dark:bg-gray-800 shadow-2xl lg:shadow-none z-10 relative transition-colors duration-300">
            
            <!-- Top Right Action Buttons (Theme & Fullscreen) -->
            <div class="absolute top-8 right-8 flex items-center space-x-3 z-50">
                <!-- Fullscreen Toggle -->
                <button id="login-fullscreen-toggle" class="p-2.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none transition-colors" title="Toggle Fullscreen">
                    <svg id="fullscreen-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <svg id="exit-fullscreen-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h4v4m0-4l-5 5m17-5h-4v4m0-4l5 5M4 10h4V6m0 4l-5-5m17 5h-4V6m0 4l5-5" />
                    </svg>
                </button>

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" class="p-2.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none transition-colors" title="Toggle Dark/Light Mode">
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 4.22a1 1 0 011.415 0l.708.708a1 1 0 01-1.414 1.414l-.708-.708a1 1 0 010-1.414zM16 10a1 1 0 011 1h1a1 1 0 110-2h-1a1 1 0 01-1 1zm-4.22 4.22a1 1 0 010 1.415l-.708.708a1 1 0 01-1.414-1.414l.708-.708a1 1 0 011.414 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4.22-4.22a1 1 0 010-1.415l-.708-.708a1 1 0 011.414-1.414l.708.708a1 1 0 01-1.414 1.414zM4 10a1 1 0 01-1-1H2a1 1 0 110 2h1a1 1 0 011-1zm4.22-4.22a1 1 0 01-1.415 0l-.708-.708a1 1 0 011.414-1.414l.708.708a1 1 0 010 1.414z"></path>
                        <path d="M10 14a4 4 0 100-8 4 4 0 000 8z" fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Logo (Visible only on small screens) -->
            <div class="absolute top-8 left-8 lg:hidden flex items-center gap-3">
                @if ($setting->logo)
                    <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="h-8">
                @else
                    <div
                        class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        {{ substr($setting->app_name ?? 'A', 0, 1) }}
                    </div>
                @endif
                <span class="font-bold text-gray-800">{{ $setting->app_name }}</span>
            </div>

            <div class="w-full max-w-md mt-10 lg:mt-0">

                <div class="mb-10 text-left">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $setting->login_title ?? 'Welcome Back' }} 👋
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400">Please enter your credentials to continue.</p>
                </div>

                <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" required
                                value="{{ old('email') ?? ($email ?? '') }}"
                                class="pl-11 w-full px-4 py-3.5 bg-gray-50/50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 dark:focus:border-brand-500 dark:text-white focus:bg-white dark:focus:bg-gray-800 transition-all outline-none"
                                placeholder="admin@example.com">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" required
                                value="{{ old('password') ?? ($password ?? '') }}"
                                class="pl-11 pr-11 w-full px-4 py-3.5 bg-gray-50/50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 dark:focus:border-brand-500 dark:text-white focus:bg-white dark:focus:bg-gray-800 transition-all outline-none"
                                placeholder="••••••••">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" id="togglePassword"
                                    class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none rounded-lg transition-colors">
                                    <svg id="eyeIcon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" value="on"
                                {{ old('remember') ? 'checked' : (isset($remember) && $remember ? 'checked' : '') }}
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded cursor-pointer">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-brand-500/30 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all transform hover:-translate-y-0.5 duration-200">
                        Sign In to Dashboard
                    </button>

                    <div class="text-center mt-8">
                        <p class="text-xs text-gray-400 font-medium">
                            {{ $setting->copyright ?? '© ' . date('Y') . ' All rights reserved.' }}
                        </p>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle Password Visibility Logic
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            if (isPassword) {
                // Eye Slash Icon (Hide)
                eyeIcon.innerHTML =
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
            } else {
                // Eye Icon (Show)
                eyeIcon.innerHTML =
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        });

        // SweetAlert Notifications Logic
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        let flashSuccess = "{{ session('success') ?? '' }}";
        if (flashSuccess) {
            Toast.fire({
                icon: "success",
                title: flashSuccess
            });
        }

        let flashError = "{{ session('error') ?? '' }}";
        let errors = @json($errors->all());

        if (flashError) {
            Toast.fire({
                icon: "error",
                title: flashError
            });
        } else if (errors.length > 0) {
            Toast.fire({
                icon: "error",
                title: errors[0]
            });
        }

        // Dark Mode Toggle Logic
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                // if NOT set via local storage previously
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });

        // Tagline Carousel Logic
        document.addEventListener('DOMContentLoaded', () => {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return; // Disable loop if reduced motion is preferred

            // Continuous Logo Formation Loop (repeats every 6 seconds)
            const logoContainer = document.getElementById('logo-anim-container');
            if (logoContainer) {
                setInterval(() => {
                    logoContainer.classList.remove('play-anim');
                    void logoContainer.offsetWidth; // trigger reflow
                    logoContainer.classList.add('play-anim');
                }, 6000); 
            }

            const items = document.querySelectorAll('.tagline-item');
            const dots = document.querySelectorAll('.carousel-dot');
            const totalItems = items.length;
            if(totalItems === 0) return;
            
            let currentIndex = 0;

            // Wait 1.5s before starting carousel to let entrance animation finish
            setTimeout(() => {
                setInterval(() => {
                    const prevIndex = currentIndex;
                    currentIndex = (currentIndex + 1) % totalItems;

                    // Animate out previous
                    items[prevIndex].classList.remove('active');
                    items[prevIndex].classList.add('exit');
                    dots[prevIndex].classList.remove('opacity-100');
                    dots[prevIndex].classList.add('opacity-30');

                    // Clean up previous item's exit class after transition ends
                    setTimeout(() => {
                        items[prevIndex].classList.remove('exit');
                    }, 600); // 600ms matches css transition duration

                    // Animate in current
                    items[currentIndex].classList.add('active');
                    dots[currentIndex].classList.remove('opacity-30');
                    dots[currentIndex].classList.add('opacity-100');
                }, 3500); // Switch every 3.5 seconds
            }, 1500); 
            
            // Fullscreen Logic
            const fsBtn = document.getElementById('login-fullscreen-toggle');
            const fsIcon = document.getElementById('fullscreen-icon');
            const exitFsIcon = document.getElementById('exit-fullscreen-icon');
            
            if (fsBtn) {
                fsBtn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error(`Error attempting to enable fullscreen: ${err.message}`);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    if (document.fullscreenElement) {
                        fsIcon.classList.add('hidden');
                        exitFsIcon.classList.remove('hidden');
                    } else {
                        fsIcon.classList.remove('hidden');
                        exitFsIcon.classList.add('hidden');
                    }
                });
            }
        });

    </script>
</body>

</html>
