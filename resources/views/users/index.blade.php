@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun, hak akses role cabang, dan status pengguna sistem.</p>
        </div>
        <button 
            type="button" 
            onclick="openCreateModal()" 
            class="inline-flex items-center justify-center px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold tracking-wide transition-colors"
        >
            + Tambah User Baru
        </button>
    </div>

    <!-- Role Capabilities Info (Flat Solid Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white border border-slate-300 p-3.5 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Super Administrator</span>
                <span class="text-[10px] bg-purple-100 text-purple-800 font-bold px-1.5 py-0.5 border border-purple-200">FULL ACCESS</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                Akses semua cabang, kelola user, master data outlet, resume statistik analitik global, dan export semua cabang.
            </p>
        </div>

        <div class="bg-white border border-slate-300 p-3.5 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Admin Cabang</span>
                <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.5 border border-blue-200">OUTLET LEVEL</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                Input data transaksi/arsip menu, edit data cabang miliknya, lihat riwayat cabang, dan export laporan cabang.
            </p>
        </div>

        <div class="bg-white border border-slate-300 p-3.5 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Viewer / Staf</span>
                <span class="text-[10px] bg-slate-100 text-slate-800 font-bold px-1.5 py-0.5 border border-slate-200">READ ONLY</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                Hanya memiliki hak akses untuk melihat data arsip dan mengunduh laporan berkas tanpa izin mengubah data.
            </p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-slate-300 p-4 shadow-sm">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Cari Nama / Email</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Ketik nama atau email..." 
                    class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 focus:outline-none focus:border-slate-900 transition-colors"
                >
            </div>

            <!-- Role Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Role</label>
                <select name="role" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 focus:outline-none focus:border-slate-900 transition-colors">
                    <option value="">Semua Role</option>
                    <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin_cabang" {{ request('role') === 'admin_cabang' ? 'selected' : '' }}>Admin Cabang</option>
                    <option value="viewer" {{ request('role') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                </select>
            </div>

            <!-- Branch Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Cabang</label>
                <select name="branch_id" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 focus:outline-none focus:border-slate-900 transition-colors">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status & Submit -->
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 focus:outline-none focus:border-slate-900 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-[34px] transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role', 'branch_id', 'status']))
                    <a href="{{ route('users.index') }}" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold h-[34px] flex items-center justify-center transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table (Flat, Clean) -->
    <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama User</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role Akses</th>
                        <th class="py-3 px-4">Penempatan Cabang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($users as $index => $user)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-medium text-slate-500">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 font-semibold">Anda</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="py-3 px-4">
                                @if($user->role === 'superadmin')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200 uppercase">
                                        Super Admin
                                    </span>
                                @elseif($user->role === 'admin_cabang')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-200 uppercase">
                                        Admin Cabang
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 uppercase">
                                        Viewer
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                @if($user->branch)
                                    <span class="font-semibold text-slate-900">{{ $user->branch->name }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">{{ $user->branch->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">Semua Cabang (Global)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($user->status === 'active')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode($user) }})"
                                        class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-800 text-[11px] font-semibold transition-colors"
                                    >
                                        Edit
                                    </button>

                                    <!-- Delete Button (Disabled for current self user) -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dipulihkan.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-semibold transition-colors"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data pengguna yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah User (Flat) -->
<div id="createModal" class="fixed inset-0 z-50 bg-slate-900/60 hidden items-center justify-center p-4">
    <div class="bg-white border border-slate-300 w-full max-w-lg shadow-xl">
        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm">Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white font-bold text-lg leading-none">&times;</button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-5 space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Email</label>
                <input type="email" name="email" required placeholder="budi@cabang.com" class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Role & Branch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Hak Akses (Role)</label>
                    <select name="role" id="createRole" onchange="toggleBranchField('create')" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="viewer">Viewer (Read-Only)</option>
                    </select>
                </div>

                <div id="createBranchContainer">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Pilih Cabang</label>
                    <select name="branch_id" id="createBranchId" class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Status Akun</label>
                <select name="status" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors">
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User (Flat) -->
<div id="editModal" class="fixed inset-0 z-50 bg-slate-900/60 hidden items-center justify-center p-4">
    <div class="bg-white border border-slate-300 w-full max-w-lg shadow-xl">
        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm">Edit Data Pengguna</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white font-bold text-lg leading-none">&times;</button>
        </div>

        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="editName" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Email</label>
                <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Password Reset (Optional) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                    Password Baru <span class="text-slate-400 normal-case font-normal">(Kosongkan jika tidak ingin diubah)</span>
                </label>
                <input type="password" name="password" placeholder="Minimal 6 karakter..." class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
            </div>

            <!-- Role & Branch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Hak Akses (Role)</label>
                    <select name="role" id="editRole" onchange="toggleBranchField('edit')" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="viewer">Viewer (Read-Only)</option>
                    </select>
                </div>

                <div id="editBranchContainer">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Pilih Cabang</label>
                    <select name="branch_id" id="editBranchId" class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Status Akun</label>
                <select name="status" id="editStatus" required class="w-full px-3 py-2 text-xs border border-slate-300 focus:outline-none focus:border-slate-900">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleBranchField('create');
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal(user) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        form.action = `/users/${user.id}`;
        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editStatus').value = user.status;
        
        const branchSelect = document.getElementById('editBranchId');
        if (branchSelect) {
            branchSelect.value = user.branch_id || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleBranchField('edit');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleBranchField(type) {
        const role = document.getElementById(type + 'Role').value;
        const container = document.getElementById(type + 'BranchContainer');
        const branchSelect = document.getElementById(type + 'BranchId');

        if (role === 'superadmin') {
            container.style.opacity = '0.4';
            container.style.pointerEvents = 'none';
            if (branchSelect) branchSelect.value = '';
        } else {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        }
    }
</script>
@endpush
@endsection
