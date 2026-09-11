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
                    }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>
<body class="bg-white text-slate-800 min-h-screen flex flex-col font-sans antialiased">

    <div class="min-h-screen flex flex-col lg:flex-row w-full">
        
        <!-- SISI KIRI: Branding & Informasi Sistem (Dark Navy Solid) -->
        <div class="w-full lg:w-1/2 bg-navy-900 text-white flex flex-col justify-between p-8 sm:p-12 lg:p-16 relative overflow-hidden">
            
            <!-- Top: Brand Header -->
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="font-bold text-base tracking-tight text-white">Ikhlas Solusi</span>
            </div>

            <!-- Middle: Main Headline & Statistics -->
            <div class="py-12 lg:py-0">
                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight tracking-tight">
                    Satu Sistem.<br>
                    Semua Cabang.
                </h1>

                <!-- Tagline -->
                <p class="text-slate-400 text-sm max-w-md leading-relaxed mt-5">
                    Platform manajemen resume penjualan terpusat untuk kepala cabang dan admin cabang Anda.
                </p>

                <!-- Statistics Section -->
                <div class="flex items-center gap-8 sm:gap-12 mt-12 pt-8 border-t border-navy-800">
                    <div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-white">3</div>
                        <div class="text-xs text-slate-400 mt-1 font-medium">Cabang Aktif</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-white">1.2K+</div>
                        <div class="text-xs text-slate-400 mt-1 font-medium">Transaksi/Bln</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-white">100%</div>
                        <div class="text-xs text-slate-400 mt-1 font-medium">Data Terpusat</div>
                    </div>
                </div>
            </div>

            <!-- Bottom: Slider Indicator (Flat) -->
            <div class="flex items-center space-x-2 pt-4">
                <div class="w-8 h-1 bg-tealBrand"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
            </div>

        </div>

        <!-- SISI KANAN: Form Login (Clean White) -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-8 sm:p-12 lg:p-20">
            
            <div class="w-full max-w-md space-y-8">
                
                <!-- Title & Subtitle -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        Masuk ke Sistem
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5">
                        Masukkan akun dan kata sandi Anda untuk melanjutkan.
                    </p>
                </div>

                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="p-3.5 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-3.5 bg-rose-50 border-l-4 border-rose-600 text-rose-800 text-xs font-medium">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-6">
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
                            class="w-full px-4 py-3 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-tealBrand transition-colors"
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
                                class="w-full px-4 py-3 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-tealBrand transition-colors pr-14"
                            >
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 text-xs font-semibold"
                                onclick="togglePasswordVisibility()"
                            >
                                Lihat
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember" 
                                class="w-4 h-4 text-tealBrand border-slate-300 rounded-none focus:ring-0"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2 text-xs font-medium text-slate-600">Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit Button (Solid Teal) -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full py-3.5 px-4 bg-tealBrand hover:bg-tealBrand-hover text-white font-bold text-sm tracking-wide transition-colors flex items-center justify-center space-x-2 active:bg-tealBrand-dark"
                        >
                            <span>Masuk Sekarang</span>
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
    </script>

</body>
</html>
