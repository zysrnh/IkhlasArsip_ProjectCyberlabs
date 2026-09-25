<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $activeBranchesCount = Branch::where('status', 'active')->count();
        $totalTransactionsCount = Transaction::count();
        $totalIncome = Transaction::sum('amount');

        return view('auth.login', compact('activeBranchesCount', 'totalTransactionsCount', 'totalIncome'));
    }

    /**
     * Proses autentikasi login (Mendukung Email maupun Nama/Username)
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('email'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Tentukan apakah loginInput adalah format email atau nama/username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Cek status keaktifan user
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Super Admin.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Single Device Session: Logout sesi aktif di perangkat/browser lain
            try {
                Auth::logoutOtherDevices($password);
            } catch (\Throwable $e) {
                // Ignore jika session driver tidak mendukung logoutOtherDevices
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Username/email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan halaman Lupa Password
     */
    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $activeBranchesCount = Branch::where('status', 'active')->count();
        $totalTransactionsCount = Transaction::count();

        return view('auth.forgot-password', compact('activeBranchesCount', 'totalTransactionsCount'));
    }

    /**
     * Kirim link reset password ke email user
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Alamat email tidak ditemukan dalam sistem.',
            ])->onlyInput('email');
        }

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Akun dengan email ini sedang dinonaktifkan. Silakan hubungi Super Admin.',
            ])->onlyInput('email');
        }

        // Generate token baru
        $token = Str::random(64);

        // Simpan / update ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email reset password: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Gagal mengirim email reset password. Pastikan konfigurasi email sistem sudah benar.',
            ])->onlyInput('email');
        }

        return back()->with('success', 'Tautan untuk mengatur ulang kata sandi telah dikirimkan ke email Anda.');
    }

    /**
     * Tampilkan halaman form reset password
     */
    public function showResetPassword(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $email = $request->query('email');
        $activeBranchesCount = Branch::where('status', 'active')->count();
        $totalTransactionsCount = Transaction::count();

        return view('auth.reset-password', compact('token', 'email', 'activeBranchesCount', 'totalTransactionsCount'));
    }

    /**
     * Proses ubah password baru berdasarkan token
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'token.required' => 'Token reset tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors([
                'email' => 'Permintaan reset kata sandi tidak ditemukan atau sudah kadaluarsa.',
            ]);
        }

        // Cek masa berlaku token (60 menit)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('password.request')->withErrors([
                'email' => 'Tautan reset kata sandi telah kadaluarsa. Silakan ajukan permintaan baru.',
            ]);
        }

        // Verifikasi token hash
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors([
                'email' => 'Token reset kata sandi tidak valid.',
            ]);
        }

        // Update password user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'Pengguna tidak ditemukan.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token setelah digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
    }

    /**
     * Proses logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
