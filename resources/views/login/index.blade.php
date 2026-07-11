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

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased selection:bg-brand-500 selection:text-white">

    <div class="min-h-screen flex">
        <!-- Left Side: Visual / Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-brand-900 overflow-hidden items-center justify-center">
            <!-- Abstract Background Image -->
            <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
                class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Background">

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600/80 to-brand-900/90"></div>

            <!-- Branding Content -->
            <div class="relative z-10 p-12 text-center text-white max-w-lg">
                <div class="mb-8 flex justify-center">
                    @if ($setting->logo)
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="h-24 drop-shadow-2xl">
                    @else
                        <div
                            class="w-24 h-24 bg-white rounded-3xl flex items-center justify-center text-brand-600 font-bold text-4xl shadow-2xl">
                            {{ substr($setting->app_name ?? 'A', 0, 1) }}
                        </div>
                    @endif
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight mb-4">{{ $setting->app_name ?? 'Admin Panel' }}</h1>
                <p class="text-brand-100 text-lg font-light leading-relaxed">
                    {{ $setting->description ?? 'Welcome to our modern administrative dashboard. Log in to access your workspace and manage your system seamlessly.' }}
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 md:p-16 bg-white shadow-2xl lg:shadow-none z-10 relative">

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
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $setting->login_title ?? 'Welcome Back' }} 👋
                    </h2>
                    <p class="text-gray-500">Please enter your credentials to continue.</p>
                </div>

                <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
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
                                class="pl-11 w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white transition-all outline-none"
                                placeholder="admin@example.com">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
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
                                class="pl-11 pr-11 w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white transition-all outline-none"
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
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded cursor-pointer">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer">
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
    </script>
</body>

</html>
