@extends('layouts.app')

@section('title', 'Master Menu Masakan & Harga Cabang')

@section('content')
<div class="space-y-5 animate-fadeIn">
    <!-- Top Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Master Menu Masakan</h1>
                @if(auth()->user()->isSuperAdmin())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200 uppercase tracking-wider">
                        Master Data
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Daftar menu masakan dapur, pengaturan sifat lauk (cepat basi / tahan lama), dan penetapan harga per cabang.
            </p>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="openCreateModal()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Menu Baru
            </button>
        </div>
        @endif
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4.5 shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Menu</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_menus']) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-0.5">Item masakan terdaftar</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4.5 shadow-sm">
            <div class="text-[10px] font-extrabold text-rose-500 uppercase tracking-wider">Cepat Basi</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($stats['total_sayur']) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-0.5">Sayuran & makanan basah</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4.5 shadow-sm">
            <div class="text-[10px] font-extrabold text-tealBrand uppercase tracking-wider">Lauk Biasa</div>
            <div class="text-2xl font-extrabold text-tealBrand mt-1">{{ number_format($stats['total_lauk']) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-0.5">Lauk olahan & protein</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4.5 shadow-sm">
            <div class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">Menu Aktif</div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['total_active']) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-0.5">Tersedia di form dapur</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('menus.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1.5">Cari Menu</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nama masakan..." 
                        class="w-full pl-9 pr-3.5 py-2 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 outline-none transition-all duration-150"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1.5">Sifat Lauk</label>
                <select name="category" class="w-full px-3.5 py-2 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 outline-none transition-all duration-150 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="perishable" {{ request('category') === 'perishable' ? 'selected' : '' }}>Cepat Basi</option>
                    <option value="non_perishable" {{ request('category') === 'non_perishable' ? 'selected' : '' }}>Lauk Biasa (Tahan Lama)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1.5">Status Menu</label>
                <select name="status" class="w-full px-3.5 py-2 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 outline-none transition-all duration-150 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif Saja</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif Saja</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-navy-800 hover:bg-navy-900 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('menus.index') }}" class="py-2 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl text-center transition-all duration-150">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Menu Masakan</th>
                        <th class="py-3.5 px-4">Sifat Lauk</th>
                        <th class="py-3.5 px-4 text-right">Harga Default</th>
                        @foreach($branches as $b)
                        <th class="py-3.5 px-4 text-right text-slate-700">{{ $b->name }}</th>
                        @endforeach
                        <th class="py-3.5 px-4 text-center">Status</th>
                        @if(auth()->user()->isSuperAdmin())
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($menus as $menu)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $menu->order_number ?: '-' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $menu->name }}
                        </td>
                        <td class="py-3 px-4">
                            @if($menu->is_perishable)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200 uppercase tracking-wider">
                                Cepat Basi
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                                Lauk Biasa
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-800">
                            Rp {{ number_format($menu->default_price, 0, ',', '.') }}
                        </td>
                        @foreach($branches as $b)
                        @php
                            $branchPrice = $menu->branchPrices->firstWhere('branch_id', $b->id);
                            $priceVal = $branchPrice && $branchPrice->price > 0 ? $branchPrice->price : $menu->default_price;
                            $isCustom = $branchPrice && $branchPrice->price != $menu->default_price;
                        @endphp
                        <td class="py-3 px-4 text-right">
                            <span class="font-medium {{ $isCustom ? 'text-tealBrand font-bold' : 'text-slate-600' }}">
                                Rp {{ number_format($priceVal, 0, ',', '.') }}
                            </span>
                        </td>
                        @endforeach
                        <td class="py-3 px-4 text-center">
                            @if($menu->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase tracking-wider">
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wider">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <!-- Tombol Setting Harga Cabang -->
                                <button 
                                    type="button" 
                                    onclick="openPriceModal({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ json_encode($menu->branchPrices->pluck('price', 'branch_id')) }})" 
                                    title="Atur Harga Tiap Cabang"
                                    class="p-1.5 text-slate-400 hover:text-tealBrand hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <!-- Tombol Edit Menu -->
                                <button 
                                    type="button" 
                                    onclick="openEditModal({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ $menu->order_number ?: 0 }}, {{ $menu->is_perishable ? 1 : 0 }}, {{ $menu->default_price }}, {{ $menu->is_active ? 1 : 0 }})" 
                                    title="Edit Menu"
                                    class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <!-- Tombol Hapus -->
                                <button 
                                    type="button" 
                                    onclick="confirmDelete({{ $menu->id }}, '{{ addslashes($menu->name) }}')" 
                                    title="Hapus Menu"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + count($branches) }}" class="py-12 text-center text-slate-400 font-medium">
                            Tidak ada data menu masakan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($menus->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $menus->links() }}
        </div>
        @endif
    </div>
</div>

<!-- MODAL TAMBAH MENU -->
<div id="createModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <h3 class="text-sm font-extrabold text-slate-900">Tambah Menu Masakan Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('menus.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Menu</label>
                <input type="number" name="order_number" min="1" placeholder="Auto / Contoh: 1" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Masakan <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Rendang Daging Sapi" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sifat Lauk <span class="text-rose-500">*</span></label>
                <select name="is_perishable" required class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none cursor-pointer">
                    <option value="0">Lauk Biasa (Tahan Lama / Bisa Diolah Kembali)</option>
                    <option value="1">Cepat Basi (Sayuran / Makanan Basah)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="default_price" required min="0" step="500" placeholder="Contoh: 9000" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                <label for="create_is_active" class="text-xs font-medium text-slate-700">Menu Aktif (Tersedia di form dapur)</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MENU -->
<div id="editModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <h3 class="text-sm font-extrabold text-slate-900">Edit Data Menu Masakan</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Menu</label>
                <input type="number" id="edit_order_number" name="order_number" min="1" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Masakan <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sifat Lauk <span class="text-rose-500">*</span></label>
                <select id="edit_is_perishable" name="is_perishable" required class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none cursor-pointer">
                    <option value="0">Lauk Biasa (Tahan Lama / Bisa Diolah Kembali)</option>
                    <option value="1">Cepat Basi (Sayuran / Makanan Basah)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="edit_default_price" name="default_price" required min="0" step="500" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-tealBrand outline-none">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                <label for="edit_is_active" class="text-xs font-medium text-slate-700">Menu Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-navy-800 hover:bg-navy-900 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Perbarui Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SETTING HARGA CABANG -->
<div id="priceModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Setting Harga Cabang</h3>
                <p id="priceModalSubtitle" class="text-[11px] text-slate-500 mt-0.5 font-medium">Atur harga khusus untuk masing-masing cabang</p>
            </div>
            <button type="button" onclick="closePriceModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="priceForm" method="POST" class="p-5 space-y-4">
            @csrf
            <div class="space-y-3">
                @foreach($branches as $b)
                <div class="flex items-center justify-between gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <div class="text-xs font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-[10px] text-slate-400 font-medium">{{ $b->code }} - {{ $b->address }}</div>
                    </div>
                    <div class="w-36">
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-[11px] text-slate-400 font-bold">Rp</span>
                            <input 
                                type="number" 
                                name="prices[{{ $b->id }}]" 
                                id="price_branch_{{ $b->id }}" 
                                min="0" 
                                step="500" 
                                placeholder="0" 
                                class="w-full pl-9 pr-3 py-1.5 text-xs text-right font-bold bg-white border border-slate-200 rounded-lg focus:border-tealBrand outline-none"
                            >
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePriceModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Simpan Harga Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete Hidden -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(id, name, order, isPerishable, defaultPrice, isActive) {
        const form = document.getElementById('editForm');
        form.action = `/menus/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_order_number').value = order;
        document.getElementById('edit_is_perishable').value = isPerishable ? "1" : "0";
        document.getElementById('edit_default_price').value = defaultPrice;
        document.getElementById('edit_is_active').checked = isActive === 1;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openPriceModal(id, name, prices) {
        const form = document.getElementById('priceForm');
        form.action = `/menus/${id}/prices`;
        document.getElementById('priceModalSubtitle').innerText = `Menu: ${name}`;
        
        @foreach($branches as $b)
        document.getElementById('price_branch_{{ $b->id }}').value = '';
        @endforeach

        if (prices) {
            for (const [branchId, price] of Object.entries(prices)) {
                const input = document.getElementById(`price_branch_${branchId}`);
                if (input) input.value = price;
            }
        }

        document.getElementById('priceModal').classList.remove('hidden');
    }
    function closePriceModal() {
        document.getElementById('priceModal').classList.add('hidden');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Menu Masakan?',
            text: `Apakah Anda yakin ingin menghapus menu "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Menu',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = `/menus/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection
