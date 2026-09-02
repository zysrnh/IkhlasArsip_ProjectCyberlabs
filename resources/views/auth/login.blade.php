<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &bull; {{ config('app.name', 'Ikhlas Arsip') }}</title>
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
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
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
<body class="bg-slate-100 text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Title Header -->
        <div class="mb-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-900 text-white font-bold text-xl mb-3">
                IA
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sistem Arsip & Transaksi</h1>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk dengan akun cabang atau super admin</p>
        </div>

        <!-- Card Container (Flat, No Gradient, Solid Border) -->
        <div class="bg-white border border-slate-300 p-8 shadow-sm">
            
            <!-- Session Status / Alert -->
            @if (session('success'))
                <div class="mb-5 p-3 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3 bg-rose-50 border-l-4 border-rose-600 text-rose-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        autocomplete="email"
                        placeholder="nama@cabang.com" 
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-slate-900 transition-colors duration-150"
                    >
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="••••••••" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-slate-900 transition-colors duration-150 pr-10"
                        >
                        <button 
                            type="button" 
                            id="togglePassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 text-xs font-medium"
                            onclick="togglePasswordVisibility()"
                        >
                            Lihat
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember" 
                            class="w-4 h-4 text-slate-900 border-slate-300 rounded-none focus:ring-0"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span class="ml-2 text-xs font-medium text-slate-600">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm tracking-wide transition-colors duration-150 flex items-center justify-center space-x-2 active:bg-slate-950"
                    >
                        <span>Masuk ke Sistem</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Ikhlas Arsip') }}. Seluruh hak cipta dilindungi.
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                btn.textContent = 'Sembunyikan';
            } else {
                pwd.type = 'password';
                btn.textContent = 'Lihat';
            }
        }
    </script>
</body>
</html>
