<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Ikhlas Solusi') - Sales Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- PWA & Mobile Meta -->
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0B192C">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Ikhlas Solusi">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style.css') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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

        /* Mobile Safe Area & WebKit Touch Optimization */
        .mobile-bottom-nav {
            padding-bottom: max(0.65rem, env(safe-area-inset-bottom, 0.65rem)) !important;
            -webkit-user-select: none;
            user-select: none;
            touch-action: manipulation;
        }
        .mobile-nav-btn {
            -webkit-tap-highlight-color: transparent !important;
            touch-action: manipulation !important;
            -webkit-touch-callout: none !important;
            user-select: none !important;
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
            padding: 10px !important;
            width: 320px !important;
            box-sizing: border-box !important;
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
        .flatpickr-innerContainer {
            width: 100% !important;
            display: block !important;
        }
        .flatpickr-rContainer {
            width: 100% !important;
            display: block !important;
        }
        .flatpickr-weekdays {
            width: 100% !important;
            display: flex !important;
        }
        .flatpickr-weekdaycontainer {
            width: 100% !important;
            display: flex !important;
        }
        span.flatpickr-weekday {
            flex: 1 0 14.2857% !important;
            width: 14.2857% !important;
            max-width: 14.2857% !important;
            color: #94a3b8 !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            text-align: center !important;
        }
        .flatpickr-days {
            width: 100% !important;
        }
        .dayContainer {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
            padding: 0 !important;
        }
        .flatpickr-day {
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            height: 36px !important;
            line-height: 36px !important;
            width: 14.2857% !important;
            max-width: 14.2857% !important;
            flex-basis: 14.2857% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
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
        class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full lg:translate-x-0 bg-navy-900 text-white flex flex-col justify-between border-r border-navy-800 transition-all duration-300 shadow-2xl lg:shadow-none select-none"
    >
        
        <!-- Brand Header (Fixed at top of sidebar) -->
        <div class="h-16 flex items-center justify-between px-4 sm:px-5 border-b border-navy-800 shrink-0 brand-container">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group cursor-pointer overflow-hidden">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="Ikhlas Solusi" 
                    class="w-8 h-8 object-contain shrink-0 transition-transform duration-200 group-hover:scale-105"
                >
                <div class="sidebar-hide-on-collapse overflow-hidden">
                    <div class="font-extrabold text-sm tracking-tight text-white leading-tight truncate">Ikhlas Solusi</div>
                    <div class="text-[10px] text-teal-400 font-medium tracking-wide truncate">Sales Management</div>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button 
                type="button" 
                onclick="toggleSidebarMobile()" 
                class="lg:hidden p-1 text-slate-400 hover:text-white hover:bg-navy-800 rounded cursor-pointer transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Scrollable Navigation Menu Area -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
            
            <!-- SECTION: MENU UTAMA -->
            <div class="space-y-1">
                <div class="px-2 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-hide-on-collapse">
                    Menu Utama
                </div>

                <!-- Dashboard Tab -->
                <a 
                    href="{{ route('dashboard') }}" 
                    title="Dashboard"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Dashboard</span>
                </a>

                @if(!auth()->user()->isViewer())
                <!-- Input Masakan Hari Ini Tab -->
                <a 
                    href="{{ route('kitchen-reports.create') }}" 
                    title="Input Masakan Hari Ini"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('kitchen-reports.create') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Input Masakan Hari Ini</span>
                </a>
                @endif

                <!-- Laporan Dapur Harian Tab (List / Riwayat Laporan) -->
                <a 
                    href="{{ route('kitchen-reports.index') }}" 
                    title="Laporan Dapur Harian"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('kitchen-reports.index', 'kitchen-reports.show', 'kitchen-reports.edit') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Laporan Dapur Harian</span>
                </a>

                <!-- Belanja Harian Cabang Tab -->
                <a 
                    href="{{ route('daily-expenses.index') }}" 
                    title="Belanja Harian Cabang"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('daily-expenses.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Belanja Harian</span>
                </a>

                <!-- Biaya Bulanan / Cost Cabang Tab (Super Admin, Kepala Cabang, Admin Cabang) -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang() || auth()->user()->isAdminCabang())
                <a 
                    href="{{ route('monthly-costs.index') }}" 
                    title="Biaya Bulanan Cabang (Cost Cabang)"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('monthly-costs.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Cost Cabang</span>
                </a>
                @endif

                <!-- Summary Transaksi & Keuangan Tab -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang() || auth()->user()->isViewer())
                <a 
                    href="{{ route('transactions.index') }}" 
                    title="Summary Transaksi & Keuangan"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('transactions.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Summary Transaksi</span>
                </a>
                @endif

                <!-- Master Menu Masakan & Harga Tab (Khusus Admin Dapur, Kepala Cabang, Super Admin, Viewer) -->
                @if(auth()->user()->isAdminDapur() || auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang() || auth()->user()->isViewer())
                <a 
                    href="{{ route('menus.index') }}" 
                    title="Master Menu & Harga"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('menus.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Menu Masakan & Harga</span>
                </a>
                @endif
            </div>

            <!-- SECTION: MANAJEMEN -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang())
                <div class="space-y-1">
                    <div class="px-2 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-hide-on-collapse">
                        Manajemen
                    </div>

                    <!-- Kelola Pengguna -->
                    <a 
                        href="{{ route('users.index') }}" 
                        title="Kelola Pengguna"
                        class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="sidebar-hide-on-collapse truncate">Kelola Pengguna</span>
                    </a>

                    <!-- Kelola Cabang (Super Admin Only) -->
                    @if(auth()->user()->isSuperAdmin())
                        <a 
                            href="{{ route('branches.index') }}" 
                            title="Kelola Cabang"
                            class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('branches.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="sidebar-hide-on-collapse truncate">Kelola Cabang</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- SECTION: SISTEM & ARSIP (Super Admin Only) -->
            @if(auth()->user()->isSuperAdmin())
                <div class="space-y-1">
                    <div class="px-2 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-hide-on-collapse">
                        Sistem & Arsip
                    </div>

                    <!-- Sampah Transaksi -->
                    <a 
                        href="{{ route('trash.index') }}" 
                        title="Sampah Transaksi"
                        class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('trash.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="sidebar-hide-on-collapse truncate">Sampah Transaksi</span>
                    </a>

                    <!-- Backup Database -->
                    <a 
                        href="{{ route('backups.index') }}" 
                        title="Backup Database"
                        class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('backups.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21 3.582 4 8 4s8-1.79 8-4" />
                        </svg>
                        <span class="sidebar-hide-on-collapse truncate">Backup Database</span>
                    </a>
                </div>
            @endif

            <!-- SECTION: PENGATURAN -->
            <div class="space-y-1">
                <div class="px-2 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-hide-on-collapse">
                    Pengaturan
                </div>

                <!-- Profil Saya -->
                <a 
                    href="{{ route('profile.edit') }}" 
                    title="Profil Saya"
                    class="sidebar-nav-item flex items-center space-x-3 px-3 py-2 text-xs font-semibold rounded transition-all duration-150 {{ request()->routeIs('profile.*') ? 'bg-tealBrand text-white font-bold' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="sidebar-hide-on-collapse truncate">Profil Saya</span>
                </a>
            </div>

        </div>

        <!-- Bottom User Profile & Logout Section -->
        <div class="p-3 border-t border-navy-800 shrink-0 bg-navy-900/60">
            <div class="flex items-center justify-between gap-2">
                <a 
                    href="{{ route('profile.edit') }}" 
                    class="flex items-center space-x-2.5 min-w-0 p-1.5 rounded hover:bg-navy-800 transition-colors group sidebar-profile-container flex-1"
                    title="Lihat Profil: {{ auth()->user()->name }}"
                >
                    @if(auth()->user()->avatar_url)
                        <img 
                            src="{{ auth()->user()->avatar_url }}" 
                            alt="{{ auth()->user()->name }}" 
                            class="w-8 h-8 rounded object-cover border border-slate-700 shrink-0 shadow-xs"
                        >
                    @else
                        <div class="w-8 h-8 rounded {{ auth()->user()->role_color }} flex items-center justify-center text-xs font-bold shrink-0 shadow-xs">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                    <div class="min-w-0 overflow-hidden sidebar-hide-on-collapse">
                        <div class="text-xs font-bold text-white truncate group-hover:text-tealBrand transition-colors leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-400 truncate leading-normal">
                            {{ auth()->user()->role_label }}
                        </div>
                    </div>
                </a>

                <!-- Quick Logout Button -->
                <form action="{{ route('logout') }}" method="POST" onsubmit="event.preventDefault(); confirmLogout(this);" class="shrink-0">
                    @csrf
                    <button 
                        type="submit" 
                        class="p-2 text-slate-400 hover:text-rose-400 hover:bg-navy-800 rounded transition-colors cursor-pointer" 
                        title="Keluar dari Sistem"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
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
                    <div class="w-8 h-8 rounded-full {{ auth()->user()->role_color }} flex items-center justify-center text-[11px] font-bold shrink-0 shadow-xs">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </a>
        </header>

        <!-- Main Body (With Safe Padding for Mobile Bottom Bar) -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-28 lg:pb-8">
            @yield('content')
        </main>

    </div>

    <!-- Mobile Bottom App Navigation Bar (Native Mobile App Style & iOS Safari Optimized) -->
    <nav class="mobile-bottom-nav lg:hidden fixed bottom-0 inset-x-0 z-50 bg-navy-900 border-t border-navy-800 text-white shadow-2xl px-2 pt-2 flex items-center justify-around">
        
        <!-- Dashboard Item -->
        <a 
            href="{{ route('dashboard') }}" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('dashboard') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                @if(request()->routeIs('dashboard'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Dashboard</span>
        </a>

        <!-- Input Dapur Item (Utama untuk Seluruh Cabang & Admin) -->
        <a 
            href="{{ route('kitchen-reports.index') }}" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('kitchen-reports.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                @if(request()->routeIs('kitchen-reports.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Dapur</span>
        </a>

        <!-- Belanja Harian Item -->
        <a 
            href="{{ route('daily-expenses.index') }}" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('daily-expenses.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                @if(request()->routeIs('daily-expenses.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Belanja</span>
        </a>

        <!-- Summary Item (Super Admin, Kepala Cabang, Viewer) -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang() || auth()->user()->isViewer())
        <a 
            href="{{ route('transactions.index') }}" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('transactions.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                @if(request()->routeIs('transactions.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Summary</span>
        </a>
        @endif

        <!-- Pengguna Item (Super Admin & Kepala Cabang) -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isKepalaCabang())
            <a 
                href="{{ route('users.index') }}" 
                class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('users.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
            >
                <div class="relative pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    @if(request()->routeIs('users.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                    @endif
                </div>
                <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Pengguna</span>
            </a>
        @endif

        <!-- Menu Super Admin: Cabang -->
        @if(auth()->user()->isSuperAdmin())
            <a 
                href="{{ route('branches.index') }}" 
                class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('branches.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
            >
                <div class="relative pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    @if(request()->routeIs('branches.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                    @endif
                </div>
                <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Cabang</span>
            </a>
        @endif

        <!-- Profil Saya Item Mobile -->
        <a 
            href="{{ route('profile.edit') }}" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 transition-colors {{ request()->routeIs('profile.*') ? 'text-tealBrand' : 'text-slate-400 hover:text-slate-200' }}"
        >
            <div class="relative pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                @if(request()->routeIs('profile.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-tealBrand"></span>
                @endif
            </div>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Profil</span>
        </a>

        <!-- Logout Item -->
        <button 
            type="button" 
            onclick="confirmLogoutMobile()" 
            class="mobile-nav-btn flex-1 flex flex-col items-center py-1 px-1 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer"
        >
            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="text-[10px] font-bold mt-1 leading-none pointer-events-none">Keluar</span>
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
                buttonsStyling: false,
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                reverseButtons: true,
                background: '#ffffff',
                customClass: {
                    container: 'z-[99999]',
                    popup: 'rounded-2xl border border-slate-200 shadow-2xl p-6 bg-white',
                    title: 'text-base sm:text-lg font-extrabold text-slate-900 tracking-tight',
                    htmlContainer: 'text-xs text-slate-500 font-medium leading-relaxed mt-2',
                    actions: 'flex items-center justify-center gap-3 mt-5 w-full',
                    confirmButton: danger 
                        ? 'px-5 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition active:scale-95 cursor-pointer' 
                        : 'px-5 py-2.5 rounded-xl text-xs font-bold bg-tealBrand hover:bg-tealBrand-hover text-white shadow-sm transition active:scale-95 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) {
                        HTMLFormElement.prototype.submit.call(form);
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

        // PWA Service Worker & Install Prompt Logic
        let deferredPrompt = null;

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration error:', err);
                });
            });
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            const dismissedAt = localStorage.getItem('ikhlas_pwa_dismissed_at');
            const now = Date.now();
            // Munculkan kembali setelah 5 hari jika pernah ditutup
            if (!dismissedAt || (now - parseInt(dismissedAt)) > 5 * 24 * 60 * 60 * 1000) {
                const banner = document.getElementById('pwaInstallBanner');
                if (banner) {
                    banner.classList.remove('hidden');
                }
            }
        });

        // Trigger deteksi browser HP jika beforeinstallprompt tidak jalan langsung
        document.addEventListener('DOMContentLoaded', () => {
            const isMobile = window.innerWidth <= 768;
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            const dismissedAt = localStorage.getItem('ikhlas_pwa_dismissed_at');
            const now = Date.now();

            if (isMobile && !isStandalone) {
                if (!dismissedAt || (now - parseInt(dismissedAt)) > 5 * 24 * 60 * 60 * 1000) {
                    setTimeout(() => {
                        const banner = document.getElementById('pwaInstallBanner');
                        if (banner) {
                            banner.classList.remove('hidden');
                        }
                    }, 1500);
                }
            }
        });

        function installPwaApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                    dismissPwaBanner();
                });
            } else {
                const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase()) || 
                              (navigator.userAgent.includes("Mac") && "ontouchend" in document);
                
                dismissPwaBanner();

                if (isIos) {
                    const iosModal = document.getElementById('iosInstallModal');
                    if (iosModal) {
                        iosModal.classList.remove('hidden');
                        iosModal.classList.add('flex');
                    }
                } else {
                    const androidModal = document.getElementById('androidInstallModal');
                    if (androidModal) {
                        androidModal.classList.remove('hidden');
                        androidModal.classList.add('flex');
                    }
                }
            }
        }

        function closeIosModal() {
            const iosModal = document.getElementById('iosInstallModal');
            if (iosModal) {
                iosModal.classList.add('hidden');
                iosModal.classList.remove('flex');
            }
        }

        function closeAndroidModal() {
            const androidModal = document.getElementById('androidInstallModal');
            if (androidModal) {
                androidModal.classList.add('hidden');
                androidModal.classList.remove('flex');
            }
        }

        function dismissPwaBanner() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.classList.add('hidden');
            localStorage.setItem('ikhlas_pwa_dismissed_at', Date.now().toString());
        }
    </script>

    <!-- Mobile PWA Install Prompt Banner (Pop-up kecil melayang di HP) -->
    <div id="pwaInstallBanner" style="bottom: max(1rem, env(safe-area-inset-bottom, 1rem));" class="fixed left-3 right-3 sm:left-auto sm:right-5 sm:max-w-sm z-50 hidden animate-slideUp">
        <div class="bg-navy-900 border border-slate-700 text-white rounded-2xl p-3.5 shadow-2xl flex items-center justify-between gap-3">
            <div class="flex items-center space-x-3 min-w-0">
                <img src="{{ asset('images/logo.png') }}" alt="Ikhlas Solusi" class="w-9 h-9 object-contain shrink-0 rounded-xl bg-white p-1">
                <div class="min-w-0">
                    <div class="font-extrabold text-xs text-white tracking-tight truncate">Pasang Aplikasi Ikhlas</div>
                    <div class="text-[10.5px] text-slate-400 leading-tight truncate">Akses cepat dari layar utama HP</div>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="dismissPwaBanner()" class="px-2 py-1.5 text-xs text-slate-400 hover:text-white font-semibold cursor-pointer">
                    Nanti
                </button>
                <button type="button" onclick="installPwaApp()" class="px-3.5 py-1.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-lg transition shadow-sm cursor-pointer">
                    Pasang
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Panduan iOS Safari -->
    <div id="iosInstallModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-4">
        <div class="bg-navy-900 border border-slate-700 text-white rounded-2xl w-full max-w-sm p-5 shadow-2xl space-y-4 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-8 h-8 rounded-lg bg-white p-0.5 object-contain">
                    <span class="font-bold text-sm text-white">Pasang di iPhone / iPad</span>
                </div>
                <button type="button" onclick="closeIosModal()" class="text-slate-400 hover:text-white p-1 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-300">
                <div class="flex items-start space-x-3 bg-navy-850 p-3 rounded-xl border border-slate-800">
                    <div class="w-6 h-6 rounded-full bg-tealBrand/20 text-tealBrand font-bold flex items-center justify-center shrink-0 text-[11px]">1</div>
                    <div class="leading-relaxed">
                        Tekan tombol <strong>Bagikan</strong> (ikon kotak dengan tanda panah ke atas <svg class="w-3.5 h-3.5 inline text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>) di bagian bawah browser Safari.
                    </div>
                </div>

                <div class="flex items-start space-x-3 bg-navy-850 p-3 rounded-xl border border-slate-800">
                    <div class="w-6 h-6 rounded-full bg-tealBrand/20 text-tealBrand font-bold flex items-center justify-center shrink-0 text-[11px]">2</div>
                    <div class="leading-relaxed">
                        Gulir ke bawah pada menu yang muncul, lalu pilih <strong>Tambahkan ke Layar Utama (Add to Home Screen)</strong>.
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="button" onclick="closeIosModal()" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Panduan Android / Browser Lainnya -->
    <div id="androidInstallModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-4">
        <div class="bg-navy-900 border border-slate-700 text-white rounded-2xl w-full max-w-sm p-5 shadow-2xl space-y-4 animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-8 h-8 rounded-lg bg-white p-0.5 object-contain">
                    <span class="font-bold text-sm text-white">Pasang Aplikasi</span>
                </div>
                <button type="button" onclick="closeAndroidModal()" class="text-slate-400 hover:text-white p-1 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-300">
                <div class="flex items-start space-x-3 bg-navy-850 p-3 rounded-xl border border-slate-800">
                    <div class="w-6 h-6 rounded-full bg-tealBrand/20 text-tealBrand font-bold flex items-center justify-center shrink-0 text-[11px]">1</div>
                    <div class="leading-relaxed">
                        Tekan ikon <strong>titik tiga (Menu)</strong> di pojok kanan atas browser Anda.
                    </div>
                </div>

                <div class="flex items-start space-x-3 bg-navy-850 p-3 rounded-xl border border-slate-800">
                    <div class="w-6 h-6 rounded-full bg-tealBrand/20 text-tealBrand font-bold flex items-center justify-center shrink-0 text-[11px]">2</div>
                    <div class="leading-relaxed">
                        Pilih opsi <strong>Tambahkan ke Layar Utama</strong> atau <strong>Install Aplikasi</strong>.
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="button" onclick="closeAndroidModal()" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
