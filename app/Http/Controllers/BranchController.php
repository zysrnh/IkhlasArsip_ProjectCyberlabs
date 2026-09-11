<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Tampilkan daftar cabang
     */
    public function index(Request $request): View
    {
        $query = Branch::withCount(['users', 'transactions'])->latest();

        // Filter pencarian (nama / kode cabang)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $branches = $query->paginate(10)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    /**
     * Simpan cabang baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'code.required' => 'Kode cabang wajib diisi.',
            'code.unique' => 'Kode cabang sudah terdaftar.',
            'name.required' => 'Nama cabang wajib diisi.',
            'status.required' => 'Status cabang wajib dipilih.',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    /**
     * Update data cabang
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'code.required' => 'Kode cabang wajib diisi.',
            'code.unique' => 'Kode cabang sudah digunakan oleh cabang lain.',
            'name.required' => 'Nama cabang wajib diisi.',
            'status.required' => 'Status cabang wajib dipilih.',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Data cabang berhasil diperbarui.');
    }

    /**
     * Hapus cabang
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        // Proteksi jika cabang masih memiliki user terdaftar
        if ($branch->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cabang tidak dapat dihapus karena masih memiliki ' . $branch->users()->count() . ' pengguna aktif. Pindahkan pengguna terlebih dahulu.']);
        }

        // Proteksi jika cabang masih memiliki arsip transaksi
        if ($branch->transactions()->count() > 0) {
            return back()->withErrors(['error' => 'Cabang tidak dapat dihapus karena masih memiliki ' . $branch->transactions()->count() . ' data transaksi terkait.']);
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
