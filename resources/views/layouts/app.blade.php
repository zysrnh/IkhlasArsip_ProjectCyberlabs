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
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col font-sans">

    <!-- Top Header & Navbar (Flat Solid) -->
    <header class="bg-slate-900 text-white border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Brand & Role Badge -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="font-bold text-base tracking-tight text-white flex items-center space-x-2">
                        <span class="bg-white text-slate-900 px-1.5 py-0.5 text-xs font-black">IA</span>
                        <span>Ikhlas Arsip</span>
                    </a>
                    <span class="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 uppercase tracking-widest font-bold border border-slate-700">
                        {{ auth()->user()->role ?? 'User' }}
                    </span>
                    @if(auth()->user()->branch)
                        <span class="text-xs text-slate-400 font-medium">
                            ({{ auth()->user()->branch->name }})
                        </span>
                    @endif
                </div>

                <!-- User Info & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-semibold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400">{{ auth()->user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Navigation Tabs (Flat) -->
            <nav class="flex space-x-1 border-t border-slate-800 pt-1 pb-2">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }} transition-colors">
                    Dashboard
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="px-3 py-1.5 text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }} transition-colors">
                        Manajemen User
                    </a>
                @endif
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
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
    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'Ikhlas Arsip') }} &bull; Sistem Pengarsipan & Rekap Transaksi Cabang
    </footer>

    @stack('scripts')
</body>
</html>
