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
     * Tampilkan daftar user
     */
    public function index(Request $request): View
    {
        $query = User::with('branch')->latest();

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
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('users.index', compact('users', 'branches'));
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in([User::ROLE_SUPERADMIN, User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER])],
            'branch_id' => ['nullable', 'required_if:role,' . User::ROLE_ADMIN_CABANG, 'exists:branches,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'branch_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Jika superadmin, branch_id dinullkan
        if ($validated['role'] === User::ROLE_SUPERADMIN) {
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in([User::ROLE_SUPERADMIN, User::ROLE_ADMIN_CABANG, User::ROLE_VIEWER])],
            'branch_id' => ['nullable', 'required_if:role,' . User::ROLE_ADMIN_CABANG, 'exists:branches,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.min' => 'Password baru minimal 6 karakter jika diisi.',
            'role.required' => 'Role wajib dipilih.',
            'branch_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        // Jangan izinkan superadmin menonaktifkan dirinya sendiri
        if ($user->id === auth()->id() && $validated['status'] === 'inactive') {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan saat ini.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] === User::ROLE_SUPERADMIN) {
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
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
