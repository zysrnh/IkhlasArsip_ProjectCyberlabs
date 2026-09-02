<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &bull; {{ config('app.name', 'Ikhlas Arsip') }}</title>
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
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <!-- Top Navbar (Flat Solid) -->
    <nav class="bg-slate-900 text-white px-6 py-3.5 flex items-center justify-between border-b border-slate-800">
        <div class="flex items-center space-x-3">
            <span class="font-bold text-lg tracking-tight">Ikhlas Arsip</span>
            <span class="text-xs bg-slate-800 text-slate-300 px-2 py-0.5 uppercase tracking-wider font-semibold border border-slate-700">
                {{ auth()->user()->role ?? 'User' }}
            </span>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right">
                <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="text-xs text-slate-400">{{ auth()->user()->email }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto p-6">
        <div class="bg-white border border-slate-300 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-slate-600">
                Sistem autentikasi berhasil disiapkan. Halaman ini adalah placeholder sementara sebelum kita pasang modul Resume Cabang, Arsip Menu, Filtering, dan Export.
            </p>
        </div>
    </main>
</body>
</html>
