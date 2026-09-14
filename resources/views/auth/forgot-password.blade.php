<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi &bull; {{ config('app.name', 'Ikhlas Solusi') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
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
                        fadeIn: 'fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        slideUp: 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(8px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(14px)' },
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
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-tealBrand selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row w-full">
        
        <!-- SISI KIRI: Branding & Informasi Sistem (Dark Navy Solid) -->
        <div class="w-full lg:w-1/2 bg-navy-900 text-white flex flex-col justify-between px-6 py-6 sm:px-10 sm:py-8 lg:p-16 relative overflow-hidden shrink-0 animate-fadeIn">
            
            <!-- Top: Brand Header -->
            <div class="flex items-center justify-between lg:justify-start space-x-3">
                <a href="{{ route('login') }}" class="flex items-center space-x-3 group cursor-pointer">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Ikhlas Solusi" 
                        class="w-9 h-9 sm:w-10 sm:h-10 object-contain shrink-0 transition-transform duration-300 group-hover:scale-105"
                    >
                    <span class="font-bold text-sm sm:text-base tracking-tight text-white">Ikhlas Solusi</span>
                </a>

                <div class="lg:hidden text-[11px] font-bold text-tealBrand uppercase tracking-wider">
                    Pemulihan Akun
                </div>
            </div>

            <!-- Middle: Main Headline & Statistics -->
            <div class="py-4 sm:py-6 lg:py-0">
                <h1 class="text-2xl sm:text-3xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight mt-3 sm:mt-5 lg:mt-0 animate-slideUp">
                    Pemulihan Akun. <br class="hidden sm:inline">
                    <span class="text-tealBrand">Cepat & Aman.</span>
                </h1>

                <p class="text-slate-400 text-xs sm:text-sm max-w-md leading-relaxed mt-2 sm:mt-4 hidden sm:block">
                    Masukkan alamat email yang terdaftar untuk menerima tautan instruksi reset kata sandi akun Anda.
                </p>

                <!-- Statistics Section -->
                <div class="grid grid-cols-3 gap-2 sm:gap-6 lg:flex lg:items-center lg:gap-12 mt-4 sm:mt-8 lg:mt-12 pt-4 sm:pt-6 lg:pt-8 border-t border-navy-800 text-center lg:text-left">
                    <div class="group cursor-default">
                        <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white">
                            {{ $activeBranchesCount ?? 3 }}
                        </div>
                        <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1 font-medium flex items-center justify-center lg:justify-start gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Cabang</span>
                        </div>
                    </div>
                    <div class="group cursor-default">
                        <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white">
                            {{ $totalTransactionsCount ?? 0 }}
                        </div>
                        <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1 font-medium flex items-center justify-center lg:justify-start gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <span>Transaksi</span>
                        </div>
                    </div>
                    <div class="group cursor-default">
                        <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white">
                            100%
                        </div>
                        <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1 font-medium flex items-center justify-center lg:justify-start gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-tealBrand"></span>
                            <span>Terpusat</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom: Slider Indicator (Desktop Only) -->
            <div class="hidden lg:flex items-center space-x-2 pt-4">
                <div class="w-8 h-1 bg-tealBrand transition-all duration-300"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
            </div>

        </div>

        <!-- SISI KANAN: Form Lupa Password (Clean White) -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center px-6 py-8 sm:px-10 sm:py-12 lg:p-20 flex-1">
            
            <div class="w-full max-w-md space-y-6 sm:space-y-8 animate-slideUp">
                
                <!-- Title & Subtitle -->
                <div>
                    <div class="mb-3 lg:hidden">
                        <img src="{{ asset('images/logo.png') }}" alt="Ikhlas Solusi" class="w-10 h-10 object-contain">
                    </div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 tracking-tight">
                        Lupa Kata Sandi?
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-1.5">
                        Ketik alamat email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
                    </p>
                </div>

                <!-- Forgot Password Form -->
                <form action="{{ route('password.email') }}" method="POST" class="space-y-4 sm:space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-[10.5px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Email Terdaftar
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="nama@email.com" 
                            class="w-full px-3.5 py-2.5 sm:px-4 sm:py-3 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:border-tealBrand transition-all"
                        >
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-3 sm:py-3.5 px-4 bg-tealBrand hover:bg-tealBrand-hover text-white font-bold text-xs sm:text-sm tracking-wide transition-all duration-200 flex items-center justify-center space-x-2 active:bg-tealBrand-dark shadow-sm hover:shadow cursor-pointer"
                        >
                            <span>Kirim Tautan Reset Password</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Back to Login -->
                    <div class="pt-2 text-center">
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-600 hover:text-tealBrand transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Halaman Masuk</span>
                        </a>
                    </div>
                </form>

                <!-- Footer Text -->
                <div class="pt-2 sm:pt-4 text-center text-[11px] sm:text-xs text-slate-400">
                    &copy; {{ date('Y') }} Ikhlas Solusi &bull; Seluruh hak cipta dilindungi.
                </div>

            </div>

        </div>

    </div>

    <!-- Script Toast Notification -->
    <script>
        const IkhlasToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            customClass: {
                popup: 'ikhlas-toast'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        @if (session('success'))
            IkhlasToast.fire({
                icon: 'success',
                iconColor: '#0A97B0',
                title: "{{ session('success') }}"
            });
        @endif

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
