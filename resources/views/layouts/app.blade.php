<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ikhlas Solusi') - Sales Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <!-- Zero-Latency Sidebar State to Prevent Flicker -->
    <script>
        (function() {
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem('ikhlas_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
            } catch(e) {}
        })();
    </script>
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
                        fadeIn: 'fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(6px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Flatpickr Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr Datepicker JS & Indonesian Locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <style>
        * {
            -webkit-font-smoothing: antialiased;
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

        /* Zero-Latency Initial & Collapsed Sidebar Styles */
        html.sidebar-is-collapsed #sidebar, .sidebar-collapsed {
            width: 5rem !important;
        }
        html.sidebar-is-collapsed #mainWrapper {
            padding-left: 5rem !important;
        }
        html.sidebar-is-collapsed .sidebar-hide-on-collapse, .sidebar-collapsed .sidebar-hide-on-collapse {
            display: none !important;
        }
        html.sidebar-is-collapsed .sidebar-nav-item, .sidebar-collapsed .sidebar-nav-item {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            margin-left: 0.5rem !important;
            margin-right: 0.5rem !important;
        }
        html.sidebar-is-collapsed .brand-container, .sidebar-collapsed .brand-container {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        html.sidebar-is-collapsed .sidebar-profile-container, .sidebar-collapsed .sidebar-profile-container {
            justify-content: center !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100/90 text-slate-800 min-h-screen flex font-sans antialiased overflow-x-hidden selection:bg-tealBrand selection:text-white">

    <!-- Mobile Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebarMobile()" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Desktop & Drawer Mobile -->
    <aside 
        id="sidebar" 
        class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full lg:translate-x-0 bg-navy-900 text-white flex flex-col justify-between border-r border-navy-800 transition-all duration-300 shadow-2xl lg:shadow-none"
    >
        
        <!-- Top Section -->
        <div>
            <!-- Brand Header & Desktop Collapse Toggle -->
            <div class="h-20 flex items-center justify-between px-4 sm:px-5 border-b border-navy-800/80 brand-container">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group cursor-pointer overflow-hidden">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Ikhlas Solusi" 
                        class="w-9 h-9 object-contain shrink-0 transition-transform duration-200 group-hover:scale-105"
                    >
                    <div class="sidebar-hide-on-collapse overflow-hidden">
                        <div class="font-extrabold text-sm tracking-tight text-white leading-tight truncate">Ikhlas Solusi</div>
                        <div class="text-[10px] text-teal-400/90 font-medium truncate">Sales Management</div>
                    </div>
                </a>

                <!-- Mobile Close Button -->
                <button 
                    type="button" 
                    onclick="toggleSidebarMobile()" 
                    class="lg:hidden p-1 text-slate-400 hover:text-white rounded-lg cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Navigation Menu -->
            <div class="py-5 space-y-1">
                <div class="px-5 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider sidebar-hide-on-collapse">
                    Menu
                </div>

                <!-- Dashboard Tab -->
                <a 
                    href="{{ route('dashboard') }}" 
                    title="Dashboard"
                    class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse">Dashboard</span>
                </a>

                <!-- Transaksi Tab -->
                <a 
                    href="{{ route('transactions.index') }}" 
                    title="Transaksi"
                    class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-all duration-150 {{ request()->routeIs('transactions.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse">Transaksi</span>
                </a>

                <!-- Menu Super Admin & Kepala Cabang: Kelola Pengguna -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang())
                    <a 
                        href="{{ route('users.index') }}" 
                        title="Kelola Pengguna"
                        class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="sidebar-hide-on-collapse">Kelola Pengguna</span>
                    </a>
                @endif

                <!-- Menu Khusus Super Admin: Cabang & Sampah -->
                @if(auth()->user()->isSuperAdmin())
                    <a 
                        href="{{ route('branches.index') }}" 
                        title="Kelola Cabang"
                        class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('branches.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="sidebar-hide-on-collapse">Kelola Cabang</span>
                    </a>

                    <a 
                        href="{{ route('trash.index') }}" 
                        title="Sampah Transaksi"
                        class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('trash.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="sidebar-hide-on-collapse">Sampah Transaksi</span>
                    </a>
                @endif

                <!-- Menu Kelola Profil -->
                <a 
                    href="{{ route('profile.edit') }}" 
                    title="Profil Saya"
                    class="sidebar-nav-item mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-all duration-150 {{ request()->routeIs('profile.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white hover:translate-x-0.5' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse">Profil Saya</span>
                </a>
            </div>
        </div>

        <!-- Bottom User Profile Section with Avatar -->
        <div class="p-3 sm:p-4 border-t border-navy-800/80">
            <a 
                href="{{ route('profile.edit') }}" 
                class="flex items-center space-x-3 mb-3 p-1.5 rounded-xl hover:bg-navy-800/80 transition-colors cursor-pointer group sidebar-profile-container"
                title="Kelola Profil Akun"
            >
                @if(auth()->user()->avatar_url)
                    <img 
                        src="{{ auth()->user()->avatar_url }}" 
                        alt="{{ auth()->user()->name }}" 
                        class="w-9 h-9 rounded-xl object-cover border border-slate-700 shrink-0 shadow-sm"
                    >
                @else
                    <div class="w-9 h-9 rounded-xl {{ auth()->user()->role === 'kepala_cabang' ? 'bg-tealBrand text-white' : (auth()->user()->role === 'superadmin' ? 'bg-purple-700 text-white' : 'bg-navy-800 text-white border border-slate-700') }} flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
                <div class="overflow-hidden sidebar-hide-on-collapse">
                    <div class="text-xs font-bold text-white truncate group-hover:text-tealBrand transition-colors">{{ auth()->user()->name }}</div>
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
            </a>

            <!-- Logout Button with SweetAlert2 Confirmation -->
            <form action="{{ route('logout') }}" method="POST" onsubmit="event.preventDefault(); confirmLogout(this);">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-2 text-xs font-semibold text-slate-400 hover:text-rose-400 py-1.5 px-2 transition-colors group cursor-pointer sidebar-nav-item" title="Keluar dari Sistem">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="sidebar-hide-on-collapse">Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div id="mainWrapper" class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-300">
        
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200 h-14 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-xs">
            <!-- Left: Toggle Sidebar on Mobile / Info on Desktop -->
            <div class="flex items-center space-x-3">
                <!-- Mobile Sidebar Toggle -->
                <button 
                    type="button" 
                    onclick="toggleSidebarMobile()" 
                    class="lg:hidden p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg cursor-pointer transition-colors"
                >
                    <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop Sidebar Toggle Button (Selalu ada di Topbar untuk Buka/Tutup) -->
                <button 
                    type="button" 
                    onclick="toggleSidebarDesktop()" 
                    class="hidden lg:flex p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg cursor-pointer transition-colors"
                    title="Buka / Tutup Sidebar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>
                
                <div class="flex items-center space-x-2 lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="Ikhlas Solusi" class="w-6 h-6 object-contain rounded">
                    <span class="font-extrabold text-sm text-slate-900 tracking-tight">Ikhlas Solusi</span>
                </div>
            </div>
            
            <!-- Right: User Avatar Link to Profile -->
            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2.5 hover:opacity-90 transition-opacity p-1 rounded-full cursor-pointer">
                <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full truncate max-w-[140px] hidden sm:inline-block">
                    {{ auth()->user()->name }}
                </span>
                @if(auth()->user()->avatar_url)
                    <img 
                        src="{{ auth()->user()->avatar_url }}" 
                        alt="{{ auth()->user()->name }}" 
                        class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs"
                    >
                @else
                    <div class="w-8 h-8 rounded-full {{ auth()->user()->role === 'kepala_cabang' ? 'bg-tealBrand text-white' : (auth()->user()->role === 'superadmin' ? 'bg-purple-700 text-white' : 'bg-navy-900 text-white') }} flex items-center justify-center text-[11px] font-bold shrink-0 shadow-xs">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </a>
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
        @endif

        <!-- Profil Saya Item Mobile -->
        <a 
            href="{{ route('profile.edit') }}" 
            class="flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('profile.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                @if(request()->routeIs('profile.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none">Profil</span>
        </a>

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

    <!-- Global Scripts: Sidebar Toggle & Toast -->
    <script>
        // Sidebar Collapse/Expand Desktop & Mobile
        const sidebarEl = document.getElementById('sidebar');
        const mainWrapperEl = document.getElementById('mainWrapper');
        const mobileBackdropEl = document.getElementById('mobileBackdrop');
        const desktopToggleIcon = document.getElementById('sidebarDesktopToggleIcon');

        // Check stored desktop state
        const isCollapsedInitial = localStorage.getItem('ikhlas_sidebar_collapsed') === 'true';
        if (window.innerWidth >= 1024 && isCollapsedInitial) {
            applyDesktopSidebarCollapse(true);
        }

        function toggleSidebarDesktop() {
            const isCurrentlyCollapsed = sidebarEl.classList.contains('sidebar-collapsed');
            const targetState = !isCurrentlyCollapsed;
            applyDesktopSidebarCollapse(targetState);
            localStorage.setItem('ikhlas_sidebar_collapsed', targetState);
        }

        function applyDesktopSidebarCollapse(collapse) {
            if (collapse) {
                document.documentElement.classList.add('sidebar-is-collapsed');
                sidebarEl.classList.add('sidebar-collapsed');
                mainWrapperEl.classList.remove('lg:pl-64');
                mainWrapperEl.classList.add('lg:pl-20');
                if (desktopToggleIcon) desktopToggleIcon.classList.add('rotate-180');
            } else {
                document.documentElement.classList.remove('sidebar-is-collapsed');
                sidebarEl.classList.remove('sidebar-collapsed');
                mainWrapperEl.classList.remove('lg:pl-20');
                mainWrapperEl.classList.add('lg:pl-64');
                if (desktopToggleIcon) desktopToggleIcon.classList.remove('rotate-180');
            }
        }

        function toggleSidebarMobile() {
            const isClosed = sidebarEl.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebarEl.classList.remove('-translate-x-full');
                mobileBackdropEl.classList.remove('hidden');
            } else {
                sidebarEl.classList.add('-translate-x-full');
                mobileBackdropEl.classList.add('hidden');
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

        // Custom SweetAlert2 Action Confirmation
        function confirmCustomAction(options) {
            const {
                title = 'Konfirmasi Tindakan',
                text = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                icon = 'warning',
                confirmButtonText = 'Ya, Lanjutkan',
                cancelButtonText = 'Batal',
                danger = false,
                form = null,
                onConfirm = null
            } = options;

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                iconColor: danger ? '#f43f5e' : (icon === 'question' ? '#0A97B0' : '#f59e0b'),
                showCancelButton: true,
                confirmButtonColor: danger ? '#f43f5e' : '#0A97B0',
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                reverseButtons: true,
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-2xl border border-slate-200 shadow-2xl p-6 animate-fadeIn',
                    title: 'text-base sm:text-lg font-extrabold text-slate-900 tracking-tight',
                    htmlContainer: 'text-xs text-slate-500 font-medium leading-relaxed mt-1',
                    confirmButton: 'px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-transform active:scale-95 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-bold transition-colors cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) {
                        form.submit();
                    } else if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                }
            });
        }

        // Konfirmasi Logout Desktop
        function confirmLogout(form) {
            confirmCustomAction({
                title: 'Keluar dari Sistem?',
                text: 'Sesi akun Anda akan diakhiri dan dialihkan ke halaman login.',
                icon: 'question',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                form: form
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
