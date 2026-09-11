<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Pastikan hanya Super Admin dan Kepala Cabang yang berhak mengakses
     */
    private function authorizeUserManagement(): void
    {
        if (!auth()->check() || (!auth()->user()->isSuperAdmin() && !auth()->user()->isKepalaCabang())) {
            abort(403, 'Akses Terbatas: Anda tidak memiliki izin untuk mengelola pengguna.');
        }
    }

    /**
     * Tampilkan daftar user
     */
    public function index(Request $request): View
    {
        $this->authorizeUserManagement();

        $currentUser = auth()->user();
        $query = User::with('branch')->latest();

        // Scoping jika login sebagai Kepala Cabang
        if ($currentUser->isKepalaCabang()) {
            // Hanya menampilkan role Admin Cabang dan Viewer
            $query->whereIn('role', [User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER]);

            // Jika kepala cabang memegang cabang tertentu
            if ($currentUser->branch_id) {
                $query->where('branch_id', $currentUser->branch_id);
                $branches = Branch::where('id', $currentUser->branch_id)->where('status', 'active')->get();
            } else {
                $branches = Branch::where('status', 'active')->orderBy('name')->get();
            }
        } else {
            // Super Admin melihat semua cabang aktif
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter cabang
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users', 'branches'));
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUserManagement();

        $currentUser = auth()->user();

        // Aturan role yang diizinkan untuk dibuat
        $allowedRoles = $currentUser->isSuperAdmin()
            ? [User::ROLE_SUPERADMIN, User::ROLE_KEPALA_CABANG, User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER]
            : [User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'branch_id' => ['nullable', 'required_if:role,' . User::ROLE_ADMIN_CABANG, 'exists:branches,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak diizinkan untuk akun Anda.',
            'branch_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Jika Kepala Cabang memiliki penempatan cabang tertentu, kunci branch_id
        if ($currentUser->isKepalaCabang() && $currentUser->branch_id) {
            $validated['branch_id'] = $currentUser->branch_id;
        }

        // Jika bukan admin cabang, branch_id null
        if ($validated['role'] !== User::ROLE_ADMIN_CABANG) {
            $validated['branch_id'] = null;
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update data user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUserManagement();

        $currentUser = auth()->user();

        // Proteksi: Kepala Cabang tidak boleh mengedit akun Super Admin atau sesama Kepala Cabang
        if ($currentUser->isKepalaCabang()) {
            if ($user->isSuperAdmin() || ($user->isKepalaCabang() && $user->id !== $currentUser->id)) {
                abort(403, 'Anda tidak memiliki hak untuk mengedit user ini.');
            }

            if ($currentUser->branch_id && $user->branch_id !== $currentUser->branch_id && $user->id !== $currentUser->id) {
                abort(403, 'Anda hanya dapat mengelola pengguna di cabang Anda sendiri.');
            }
        }

        $allowedRoles = $currentUser->isSuperAdmin()
            ? [User::ROLE_SUPERADMIN, User::ROLE_KEPALA_CABANG, User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER]
            : [User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'branch_id' => ['nullable', 'required_if:role,' . User::ROLE_ADMIN_CABANG, 'exists:branches,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.min' => 'Password baru minimal 6 karakter jika diisi.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak diizinkan untuk akun Anda.',
            'branch_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        // Jangan izinkan user menonaktifkan dirinya sendiri jika sedang login
        if ($user->id === auth()->id() && $validated['status'] === 'inactive') {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan saat ini.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($currentUser->isKepalaCabang() && $currentUser->branch_id) {
            $validated['branch_id'] = $currentUser->branch_id;
        }

        if ($validated['role'] !== User::ROLE_ADMIN_CABANG) {
            $validated['branch_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUserManagement();

        $currentUser = auth()->user();

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.']);
        }

        // Proteksi: Kepala Cabang tidak boleh menghapus akun Super Admin atau sesama Kepala Cabang
        if ($currentUser->isKepalaCabang()) {
            if ($user->isSuperAdmin() || $user->isKepalaCabang()) {
                abort(403, 'Anda tidak memiliki hak untuk menghapus user ini.');
            }

            if ($currentUser->branch_id && $user->branch_id !== $currentUser->branch_id) {
                abort(403, 'Anda hanya dapat menghapus pengguna di cabang Anda sendiri.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
