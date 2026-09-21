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
        $query = User::with(['branch', 'managedBranches'])->latest();

        // Scoping jika login sebagai Kepala Cabang
        if ($currentUser->isKepalaCabang()) {
            $managedBranchIds = $currentUser->getAccessibleBranchIds();
            $query->whereIn('role', [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR, User::ROLE_VIEWER])
                  ->whereIn('branch_id', $managedBranchIds);
            $branches = $currentUser->getAccessibleBranches();
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
            ? [User::ROLE_SUPERADMIN, User::ROLE_KEPALA_CABANG, User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR, User::ROLE_VIEWER]
            : [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR, User::ROLE_VIEWER];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        // Khusus Kepala Cabang & Viewer: wajib memilih minimal 1 cabang (multi-select)
        if (in_array($request->role, [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER])) {
            $rules['branch_ids'] = ['required', 'array', 'min:1'];
            $rules['branch_ids.*'] = ['exists:branches,id'];
        } elseif (in_array($request->role, [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR])) {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        } else {
            $rules['branch_id'] = ['nullable'];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak diizinkan untuk akun Anda.',
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'branch_ids.required' => 'Pilih minimal satu cabang untuk pengguna ini.',
            'branch_ids.min' => 'Pilih minimal satu cabang untuk pengguna ini.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $rawPassword = $request->password;
        $validated['password'] = Hash::make($rawPassword);

        // Jika Kepala Cabang membuat user admin cabang / viewer, pastikan cabangnya adalah wilayah yang dia pegang
        if ($currentUser->isKepalaCabang()) {
            $allowedBranchIds = $currentUser->getAccessibleBranchIds();
            $targetBranchIds = in_array($validated['role'], [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER]) 
                ? ($request->branch_ids ?? []) 
                : [$validated['branch_id'] ?? null];
            
            foreach ($targetBranchIds as $tId) {
                if (!in_array($tId, $allowedBranchIds)) {
                    return back()->withErrors(['branch_ids' => 'Anda hanya dapat menempatkan user pada wilayah cabang Anda.']);
                }
            }
        }

        // Tentukan penempatan branch_id
        if (in_array($validated['role'], [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER])) {
            $validated['branch_id'] = $request->branch_ids[0] ?? null;
        } elseif (!in_array($validated['role'], [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR])) {
            $validated['branch_id'] = null;
        }

        $newUser = User::create($validated);

        // Simpan multi-cabang jika kepala cabang / viewer
        if (in_array($newUser->role, [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER]) && !empty($request->branch_ids)) {
            $newUser->managedBranches()->sync($request->branch_ids);
        }

        // Kirim email kredensial akun baru
        try {
            Mail::to($newUser->email)->send(new UserCredentialsMail($newUser, $rawPassword));
        } catch (\Exception $e) {
            // Log error jika email gagal terkirim tanpa menggagalkan pembuatan user
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email kredensial ke {$newUser->email}: " . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan dan email kredensial telah dikirim.');
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

            $accessibleBranchIds = $currentUser->getAccessibleBranchIds();
            $targetUserBranchIds = $user->getAccessibleBranchIds();
            if (empty(array_intersect($targetUserBranchIds, $accessibleBranchIds)) && $user->id !== $currentUser->id) {
                abort(403, 'Anda hanya dapat mengelola pengguna di cabang wilayah Anda sendiri.');
            }
        }

        $allowedRoles = $currentUser->isSuperAdmin()
            ? [User::ROLE_SUPERADMIN, User::ROLE_KEPALA_CABANG, User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR, User::ROLE_VIEWER]
            : [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR, User::ROLE_VIEWER];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        // Khusus Kepala Cabang & Viewer: wajib memilih minimal 1 cabang (multi-select)
        if (in_array($request->role, [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER])) {
            $rules['branch_ids'] = ['required', 'array', 'min:1'];
            $rules['branch_ids.*'] = ['exists:branches,id'];
        } elseif (in_array($request->role, [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR])) {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        } else {
            $rules['branch_id'] = ['nullable'];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.min' => 'Password baru minimal 6 karakter jika diisi.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak diizinkan untuk akun Anda.',
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'branch_ids.required' => 'Pilih minimal satu cabang untuk pengguna ini.',
            'branch_ids.min' => 'Pilih minimal satu cabang untuk pengguna ini.',
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

        // Tentukan penempatan branch_id
        if (in_array($validated['role'], [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER])) {
            $validated['branch_id'] = $request->branch_ids[0] ?? null;
        } elseif (!in_array($validated['role'], [User::ROLE_ADMIN_CABANG, User::ROLE_ADMIN_DAPUR])) {
            $validated['branch_id'] = null;
        }

        $user->update($validated);

        // Sinkronisasi Multi-Cabang untuk Kepala Cabang & Viewer
        if (in_array($user->role, [User::ROLE_KEPALA_CABANG, User::ROLE_VIEWER])) {
            $user->managedBranches()->sync($request->branch_ids ?? []);
        } else {
            $user->managedBranches()->detach();
        }

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

            $accessibleBranchIds = $currentUser->getAccessibleBranchIds();
            if (!in_array($user->branch_id, $accessibleBranchIds)) {
                abort(403, 'Anda hanya dapat menghapus pengguna di cabang wilayah Anda sendiri.');
            }
        }

        $user->managedBranches()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
