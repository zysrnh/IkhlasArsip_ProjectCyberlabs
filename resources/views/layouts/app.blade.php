<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ikhlas Solusi') - Sales Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Flatpickr Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr Datepicker JS & Indonesian Locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <style>
        /* Custom Keyframe Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Hide scrollbar for clean UI */
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Custom SweetAlert2 Toast Theme */
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

        /* Custom Flatpickr Ikhlas Theme */
        .flatpickr-calendar {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            box-shadow: 0 15px 30px -5px rgba(11, 25, 44, 0.15) !important;
            font-family: inherit !important;
            padding: 8px !important;
        }
        .flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after {
            border-bottom-color: #ffffff !important;
        }
        .flatpickr-months {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .flatpickr-current-month {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #0B192C !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 800 !important;
        }
        span.flatpickr-weekday {
            color: #94a3b8 !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
        }
        .flatpickr-day {
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            height: 34px !important;
            line-height: 34px !important;
        }
        .flatpickr-day:hover {
            background: #f1f5f9 !important;
            border-color: #f1f5f9 !important;
        }
        .flatpickr-day.today {
            border-color: #0A97B0 !important;
            color: #0A97B0 !important;
            background: #f0fdfa !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #0A97B0 !important;
            border-color: #0A97B0 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100/90 text-slate-800 min-h-screen flex font-sans antialiased overflow-x-hidden selection:bg-tealBrand selection:text-white">

    <!-- Sidebar Desktop (Dark Navy Solid Sesuai Mockup) -->
    <aside id="sidebar" class="hidden lg:flex fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-white flex-col justify-between border-r border-navy-800">
        
        <!-- Top Section -->
        <div>
            <!-- Brand Header -->
            <div class="h-20 flex items-center justify-between px-5 border-b border-navy-800/80">
                <div class="flex items-center space-x-3 group cursor-default">
                    <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-white shrink-0 shadow-sm transition-transform duration-200 group-hover:scale-105">
                        <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-5a1 1 0 011-1h2a1 1 0 011 1v5h4a1 1 0 001-1V10" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-extrabold text-sm tracking-tight text-white leading-tight">Ikhlas Solusi</div>
                        <div class="text-[10px] text-teal-400/90 font-medium">Sales Management</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="py-5 space-y-1">
                <div class="px-5 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Menu
                </div>

                <!-- Dashboard Tab -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Transaksi Tab -->
                <a 
                    href="{{ route('transactions.index') }}" 
                    class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-all duration-150 {{ request()->routeIs('transactions.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Transaksi</span>
                </a>

                <!-- Menu Khusus Super Admin & Kepala Cabang: Kelola Pengguna -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang())
                    <a 
                        href="{{ route('users.index') }}" 
                        class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Kelola Pengguna</span>
                    </a>
                @endif

                <!-- Menu Khusus Super Admin: Cabang & Sampah -->
                @if(auth()->user()->isSuperAdmin())
                    <a 
                        href="{{ route('branches.index') }}" 
                        class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('branches.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Kelola Cabang</span>
                    </a>

                    <a 
                        href="{{ route('trash.index') }}" 
                        class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('trash.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Sampah Transaksi</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Bottom User Profile Section (Sesuai Mockup) -->
        <div class="p-4 border-t border-navy-800/80">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1) . (str_contains(auth()->user()->name, ' ') ? substr(explode(' ', auth()->user()->name)[1], 0, 1) : '')) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">
                        @if(auth()->user()->isKepalaCabang())
                            Kepala Cabang
                        @elseif(auth()->user()->isAdminCabang())
                            Admin - {{ auth()->user()->branch->name ?? 'Cabang' }}
                        @else
                            {{ ucfirst(auth()->user()->role) }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Logout Button with SweetAlert2 Confirmation -->
            <form action="{{ route('logout') }}" method="POST" onsubmit="event.preventDefault(); confirmLogout(this);">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-2 text-xs font-semibold text-slate-400 hover:text-rose-400 py-1.5 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        
        <!-- Top Mobile Header Bar -->
        <header class="lg:hidden bg-white border-b border-slate-200 h-14 flex items-center justify-between px-4 sticky top-0 z-30 shadow-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-7 h-7 bg-tealBrand flex items-center justify-center text-white shrink-0">
                    <svg class="w-4 h-4 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-5a1 1 0 011-1h2a1 1 0 011 1v5h4a1 1 0 001-1V10" />
                    </svg>
                </div>
                <span class="font-extrabold text-sm text-slate-900 tracking-tight">Ikhlas Solusi</span>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full truncate max-w-[140px]">
                    {{ auth()->user()->name }}
                </span>
            </div>
        </header>

        <!-- Main Body (With Safe Padding for Mobile Bottom Bar) -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-24 lg:pb-8">
            @yield('content')
        </main>

    </div>

    <!-- Mobile Bottom App Navigation Bar (Native Mobile App Style) -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-navy-900 border-t border-navy-800 text-white shadow-2xl px-2 py-1.5 flex items-center justify-around">
        
        <!-- Dashboard Item -->
        <a 
            href="{{ route('dashboard') }}" 
            class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('dashboard') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                @if(request()->routeIs('dashboard'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none">Dashboard</span>
        </a>

        <!-- Transaksi Item -->
        <a 
            href="{{ route('transactions.index') }}" 
            class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('transactions.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                @if(request()->routeIs('transactions.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none">Transaksi</span>
        </a>

        <!-- Pengguna Item (Super Admin & Kepala Cabang) -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang())
            <a 
                href="{{ route('users.index') }}" 
                class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('users.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    @if(request()->routeIs('users.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                    @endif
                </div>
                <span class="text-[10px] font-bold mt-1 leading-none">Pengguna</span>
            </a>
        @endif

        <!-- Menu Super Admin: Cabang -->
        @if(auth()->user()->isSuperAdmin())
            <a 
                href="{{ route('branches.index') }}" 
                class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('branches.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    @if(request()->routeIs('branches.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                    @endif
                </div>
                <span class="text-[10px] font-bold mt-1 leading-none">Cabang</span>
            </a>

            <a 
                href="{{ route('trash.index') }}" 
                class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('trash.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    @if(request()->routeIs('trash.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                    @endif
                </div>
                <span class="text-[10px] font-bold mt-1 leading-none">Sampah</span>
            </a>
        @endif

        <!-- Logout Item -->
        <button 
            type="button" 
            onclick="confirmLogoutMobile()" 
            class="flex-1 flex flex-col items-center py-1 px-1 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="text-[10px] font-bold mt-1 leading-none">Keluar</span>
        </button>

    </nav>

    <!-- Hidden Form for Mobile Logout -->
    <form id="mobileLogoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Global Toast & Modal Scripts -->
    <script>
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

        // Konfirmasi Logout Desktop
        function confirmLogout(form) {
            Swal.fire({
                title: 'Keluar dari Sistem?',
                text: 'Sesi akun Anda akan diakhiri dan dialihkan ke halaman login.',
                icon: 'question',
                iconColor: '#0A97B0',
                showCancelButton: true,
                confirmButtonColor: '#0A97B0',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-xl border border-slate-200 shadow-2xl p-6',
                    title: 'text-base font-bold text-slate-900',
                    htmlContainer: 'text-xs text-slate-500 font-medium',
                    confirmButton: 'px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm',
                    cancelButton: 'px-5 py-2.5 rounded-lg text-xs font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Konfirmasi Logout Mobile
        function confirmLogoutMobile() {
            const form = document.getElementById('mobileLogoutForm');
            confirmLogout(form);
        }

        // Tampilkan Toast Sukses jika ada session 'success'
        @if (session('success'))
            IkhlasToast.fire({
                icon: 'success',
                iconColor: '#0A97B0',
                title: "{{ session('success') }}"
            });
        @endif

        // Tampilkan Toast Error jika ada session 'error' atau error validasi
        @if (session('error'))
            IkhlasToast.fire({
                icon: 'error',
                iconColor: '#f43f5e',
                title: "{{ session('error') }}"
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

    @stack('scripts')
</body>
</html>
