<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Sistem &bull; {{ config('app.name', 'Ikhlas Solusi') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            950: '#071220',
                            900: '#0B192C',
                            850: '#0F223D',
                            800: '#142B4D',
                        },
                        tealBrand: {
                            DEFAULT: '#0A97B0',
                            hover: '#088395',
                            dark: '#056371',
                            light: '#E0F7FA',
                        }
                    },
                    animation: {
                        fadeIn: 'fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        slideUp: 'slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        pulseSlow: 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(18px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-font-smoothing: antialiased;
        }
        /* Custom SweetAlert2 Theme Ikhlas */
        .swal2-popup.ikhlas-toast {
            background-color: #0B192C !important;
            color: #ffffff !important;
            border: 1px solid #1e293b !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
            padding: 12px 18px !important;
        }
        .swal2-popup.ikhlas-toast .swal2-title {
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 600 !important;
        }
        .swal2-popup.ikhlas-toast .swal2-timer-progress-bar {
            background: #0A97B0 !important;
        }
    </style>
</head>
<body class="bg-white text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-tealBrand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row w-full">
        
        <!-- SISI KIRI: Branding & Informasi Sistem (Dark Navy Solid) -->
        <div class="w-full lg:w-1/2 bg-navy-900 text-white flex flex-col justify-between p-8 sm:p-12 lg:p-16 relative overflow-hidden animate-fadeIn">
            
            <!-- Top: Brand Header -->
            <div class="flex items-center space-x-3 group cursor-default">
                <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-white shrink-0 shadow-sm transition-transform duration-300 group-hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="font-bold text-base tracking-tight text-white">Ikhlas Solusi</span>
            </div>

            <!-- Middle: Main Headline & Statistics -->
            <div class="py-12 lg:py-0">
                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight tracking-tight animate-slideUp">
                    Satu Sistem.<br>
                    <span class="text-tealBrand">Semua Cabang.</span>
                </h1>

                <!-- Tagline -->
                <p class="text-slate-400 text-sm max-w-md leading-relaxed mt-5">
                    Platform manajemen resume penjualan terpusat untuk kepala cabang dan admin cabang Anda.
                </p>

                <!-- Statistics Section (Dinamis dari Database) -->
                <div class="flex items-center gap-8 sm:gap-12 mt-12 pt-8 border-t border-navy-800">
                    <div class="group cursor-default">
                        <div class="text-2xl sm:text-3xl font-extrabold text-white transition-transform group-hover:scale-105">
                            {{ $activeBranchesCount ?? 3 }}
                        </div>
                        <div class="text-xs text-slate-400 mt-1 font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Cabang Aktif</span>
                        </div>
                    </div>
                    <div class="group cursor-default">
                        <div class="text-2xl sm:text-3xl font-extrabold text-white transition-transform group-hover:scale-105">
                            {{ $totalTransactionsCount ?? 0 }}
                        </div>
                        <div class="text-xs text-slate-400 mt-1 font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <span>Transaksi Terinput</span>
                        </div>
                    </div>
                    <div class="group cursor-default">
                        <div class="text-2xl sm:text-3xl font-extrabold text-white transition-transform group-hover:scale-105">
                            100%
                        </div>
                        <div class="text-xs text-slate-400 mt-1 font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-tealBrand"></span>
                            <span>Data Terpusat</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom: Slider Indicator (Flat) -->
            <div class="flex items-center space-x-2 pt-4">
                <div class="w-8 h-1 bg-tealBrand transition-all duration-300"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full hover:bg-white transition-colors cursor-pointer"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full hover:bg-white transition-colors cursor-pointer"></div>
            </div>

        </div>

        <!-- SISI KANAN: Form Login (Clean White) -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-8 sm:p-12 lg:p-20">
            
            <div class="w-full max-w-md space-y-8 animate-slideUp">
                
                <!-- Title & Subtitle -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        Masuk ke Sistem
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5">
                        Masukkan akun dan kata sandi Anda untuk melanjutkan.
                    </p>
                </div>

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Username / Email Input -->
                    <div>
                        <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Username / Email
                        </label>
                        <input 
                            type="text" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan Username atau Email" 
                            class="w-full px-4 py-3 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-tealBrand transition-all"
                        >
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Masukan Password" 
                                class="w-full px-4 py-3 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-tealBrand transition-all pr-14"
                            >
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 text-xs font-semibold select-none"
                                onclick="togglePasswordVisibility()"
                            >
                                Lihat
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember" 
                                class="w-4 h-4 text-tealBrand border-slate-300 rounded-none focus:ring-0 cursor-pointer"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2 text-xs font-medium text-slate-600">Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit Button (Solid Teal) -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full py-3.5 px-4 bg-tealBrand hover:bg-tealBrand-hover text-white font-bold text-sm tracking-wide transition-all duration-200 flex items-center justify-center space-x-2 active:bg-tealBrand-dark shadow-sm hover:shadow"
                        >
                            <span>Masuk Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Footer Text -->
                <div class="pt-4 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Ikhlas Solusi &bull; Seluruh hak cipta dilindungi.
                </div>

            </div>

        </div>

    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                btn.textContent = 'Tutup';
            } else {
                pwd.type = 'password';
                btn.textContent = 'Lihat';
            }
        }

        // Custom SweetAlert2 Toast Khas Ikhlas Solusi
        const IkhlasToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: {
                popup: 'ikhlas-toast'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // Tampilkan Toast Sukses jika ada session 'success'
        @if (session('success'))
            IkhlasToast.fire({
                icon: 'success',
                iconColor: '#0A97B0',
                title: "{{ session('success') }}"
            });
        @endif

        // Tampilkan Toast Error jika ada validation errors
        @if ($errors->any())
            IkhlasToast.fire({
                icon: 'error',
                iconColor: '#f43f5e',
                title: "{{ $errors->first() }}"
            });
        @endif
    </script>

</body>
</html>
