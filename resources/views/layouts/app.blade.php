<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') &bull; {{ config('app.name', 'Ikhlas Solusi') }}</title>
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
                            950: '#06101c',
                            900: '#0B192C',
                            850: '#0E223D',
                            800: '#142C4E',
                        },
                        tealBrand: {
                            DEFAULT: '#0A97B0',
                            hover: '#088395',
                            dark: '#066F7F',
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
        #sidebar {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100/90 text-slate-800 min-h-screen flex font-sans antialiased overflow-x-hidden">

    <!-- Mobile Overlay Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebarMobile()" class="fixed inset-0 bg-navy-950/70 z-40 hidden lg:hidden"></div>

    <!-- Sidebar (Dark Navy Solid Sesuai Mockup) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-white flex flex-col justify-between -translate-x-full lg:translate-x-0 border-r border-navy-800">
        
        <!-- Top Section -->
        <div>
            <!-- Brand Header -->
            <div class="h-20 flex items-center justify-between px-5 border-b border-navy-800/80">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-5a1 1 0 011-1h2a1 1 0 011 1v5h4a1 1 0 001-1V10" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-extrabold text-sm tracking-tight text-white leading-tight">Ikhlas Solusi</div>
                        <div class="text-[10px] text-teal-400/90 font-medium">Sales Management</div>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebarMobile()" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu -->
            <div class="py-5 space-y-1">
                <div class="px-5 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Menu
                </div>

                <!-- Dashboard Tab -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-colors {{ request()->routeIs('dashboard') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Transaksi Tab -->
                <a 
                    href="{{ route('transactions.index') }}" 
                    class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-bold transition-colors {{ request()->routeIs('transactions.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Transaksi</span>
                </a>

                <!-- Manajemen User (Hanya Superadmin) -->
                @if(auth()->user()->isSuperAdmin())
                    <a 
                        href="{{ route('users.index') }}" 
                        class="mx-3 px-3.5 py-2.5 rounded-lg flex items-center space-x-3 text-xs font-semibold transition-colors {{ request()->routeIs('users.*') ? 'bg-tealBrand text-white shadow-sm' : 'text-slate-300 hover:bg-navy-800 hover:text-white' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Kelola Pengguna</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Bottom User Profile Section (Sesuai Mockup) -->
        <div class="p-4 border-t border-navy-800/80">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-9 h-9 bg-tealBrand flex items-center justify-center text-xs font-bold text-white shrink-0">
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

            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-2 text-xs font-semibold text-slate-400 hover:text-rose-400 py-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        
        <!-- Top Mobile Header -->
        <header class="lg:hidden bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sticky top-0 z-30">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="toggleSidebarMobile()" class="p-2 text-slate-700 hover:bg-slate-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="font-bold text-sm text-slate-900">Ikhlas Solusi</span>
            </div>
            <div class="text-xs font-semibold text-slate-600">
                {{ auth()->user()->name }}
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

    </div>

    <!-- Toggle Mobile Script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mobileBackdrop = document.getElementById('mobileBackdrop');

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
