<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Arsip') &bull; {{ config('app.name', 'Ikhlas Arsip') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-font-smoothing: antialiased;
        }
        /* Sidebar transition styles */
        #sidebar {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-collapsed {
            width: 4.5rem !important; /* 72px */
        }
        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-badge {
            display: none !important;
        }
        .sidebar-collapsed .sidebar-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-collapsed .brand-title {
            display: none !important;
        }
        .sidebar-collapsed .sidebar-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-collapsed .user-info-section {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex font-sans antialiased overflow-x-hidden">

    <!-- Mobile Overlay Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebarMobile()" class="fixed inset-0 bg-slate-900/60 z-40 hidden lg:hidden"></div>

    <!-- Collapsible Sidebar (Flat Solid Slate-900) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col border-r border-slate-800 -translate-x-full lg:translate-x-0">
        
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 sidebar-header">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 overflow-hidden">
                <span class="bg-white text-slate-900 px-2 py-1 text-xs font-black shrink-0 tracking-tighter">IA</span>
                <span class="font-bold text-sm tracking-tight text-white whitespace-nowrap brand-title">Ikhlas Arsip</span>
            </a>
            <button type="button" onclick="toggleSidebarMobile()" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <div class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            
            <!-- Group: Utama -->
            <div class="px-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider sidebar-text">
                Menu Utama
            </div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" title="Dashboard" class="sidebar-item flex items-center space-x-3 px-3 py-2.5 text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white border-l-2 border-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="sidebar-text truncate">Dashboard</span>
            </a>

            <!-- User Management (Khusus Super Admin) -->
            @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 px-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider sidebar-text">
                    Administrasi
                </div>

                <a href="{{ route('users.index') }}" title="Manajemen User" class="sidebar-item flex items-center space-x-3 px-3 py-2.5 text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white border-l-2 border-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Manajemen User</span>
                </a>
            @endif

        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-t border-slate-800 user-info-section">
            <div class="bg-slate-800/60 p-2.5 flex items-center space-x-2.5">
                <div class="w-7 h-7 bg-slate-700 flex items-center justify-center text-[11px] font-bold text-white shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden flex-1">
                    <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-slate-400 uppercase font-mono tracking-wider truncate">
                        {{ auth()->user()->role }}
                    </div>
                </div>
            </div>
        </div>

    </aside>

    <!-- Main Wrapper Area (Dynamically adjusts to sidebar) -->
    <div id="mainWrapper" class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-200">
        
        <!-- Top App Header (Flat Solid) -->
        <header class="bg-white border-b border-slate-300 sticky top-0 z-30 h-16 flex items-center justify-between px-4 sm:px-6">
            
            <!-- Left: Toggle Sidebar Button & Page Title -->
            <div class="flex items-center space-x-3">
                <!-- Toggle Button for Desktop & Mobile -->
                <button 
                    type="button" 
                    id="sidebarToggleBtn"
                    onclick="toggleSidebarDesktop()" 
                    class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition-colors"
                    title="Buka / Tutup Sidebar"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center space-x-2">
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">
                        @yield('title', 'Dashboard')
                    </h2>
                    @if(auth()->user()->branch)
                        <span class="hidden sm:inline-block text-[11px] bg-slate-100 text-slate-700 px-2 py-0.5 border border-slate-200 font-medium">
                            {{ auth()->user()->branch->name }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: User Info & Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-slate-500 font-mono">{{ auth()->user()->email }}</div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 transition-colors">
                        Keluar
                    </button>
                </form>
            </div>

        </header>

        <!-- Main Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <!-- Flash Alert Success -->
            @if (session('success'))
                <div class="mb-4 p-3 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- Flash Alert Error -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-rose-50 border-l-4 border-rose-600 text-rose-800 text-xs font-medium">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer Flat -->
        <footer class="bg-white border-t border-slate-200 py-3 px-6 text-center sm:text-left text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                &copy; {{ date('Y') }} {{ config('app.name', 'Ikhlas Arsip') }} &bull; Sistem Pengarsipan & Rekap Transaksi Cabang
            </div>
            <div class="text-[11px] text-slate-400">
                Mode: <span class="font-semibold text-slate-600 uppercase">{{ auth()->user()->role }}</span>
            </div>
        </footer>

    </div>

    <!-- Sidebar Toggle Script with LocalStorage Persistence -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        const mobileBackdrop = document.getElementById('mobileBackdrop');

        // Check stored sidebar state
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (window.innerWidth >= 1024 && isCollapsed) {
            applySidebarCollapsed(true);
        }

        function toggleSidebarDesktop() {
            if (window.innerWidth < 1024) {
                // Mobile behavior: slide in/out
                toggleSidebarMobile();
            } else {
                // Desktop behavior: expand / mini-collapse
                const currentlyCollapsed = sidebar.classList.contains('sidebar-collapsed');
                applySidebarCollapsed(!currentlyCollapsed);
                localStorage.setItem('sidebar_collapsed', !currentlyCollapsed);
            }
        }

        function applySidebarCollapsed(collapse) {
            if (collapse) {
                sidebar.classList.add('sidebar-collapsed');
                mainWrapper.classList.remove('lg:pl-64');
                mainWrapper.classList.add('lg:pl-[4.5rem]');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                mainWrapper.classList.remove('lg:pl-[4.5rem]');
                mainWrapper.classList.add('lg:pl-64');
            }
        }

        function toggleSidebarMobile() {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                mobileBackdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                mobileBackdrop.classList.add('hidden');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
