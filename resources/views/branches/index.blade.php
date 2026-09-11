@extends('layouts.app')

@section('title', 'Manajemen Cabang')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Cabang</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Kelola master data kantor cabang, alamat operasional, dan status aktifitas outlet.</p>
        </div>
        <button 
            type="button" 
            onclick="openCreateBranchModal()" 
            class="inline-flex items-center justify-center space-x-1.5 px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-colors cursor-pointer"
        >
            <span class="text-sm leading-none">+</span>
            <span>Tambah Cabang Baru</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>PENCARIAN & FILTER CABANG</span>
                @if(request()->hasAny(['search', 'status']))
                    <span class="w-2 h-2 rounded-full bg-tealBrand inline-block"></span>
                @endif
            </div>

            <button 
                type="button" 
                onclick="toggleBranchMobileFilter()"
                id="branchMobileFilterBtn" 
                class="sm:hidden text-xs font-bold text-tealBrand hover:text-tealBrand-hover inline-flex items-center space-x-1 cursor-pointer"
            >
                <span id="branchMobileFilterText">Tutup Filter</span>
                <svg id="branchMobileFilterIcon" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <form id="branchFilterForm" method="GET" action="{{ route('branches.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
            <input type="hidden" name="status" id="branchStatusFilterInput" value="{{ request('status') }}">

            <!-- 1. Live Search Input -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg id="branchSearchStaticIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg id="branchSearchLoadingIcon" class="w-4 h-4 text-tealBrand animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                <input 
                    type="text" 
                    id="branchSearchInput"
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama, kode cabang, atau alamat..." 
                    autocomplete="off"
                    oninput="handleBranchSearchDebounce(this)"
                    class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-tealBrand focus:ring-1 focus:ring-tealBrand shadow-sm transition"
                >

                <!-- Clear Button -->
                <button 
                    type="button" 
                    id="branchSearchClearBtn"
                    onclick="clearBranchSearchInput()"
                    class="{{ request('search') ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"
                    title="Hapus pencarian"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- 2. Status Custom Dropdown -->
            <div class="relative" id="branchStatusDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleBranchPopover('branchStatusMenu', 'branchStatusChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if(request('status') === 'active')
                            Cabang Aktif
                        @elseif(request('status') === 'inactive')
                            Cabang Nonaktif
                        @else
                            Semua Status
                        @endif
                    </span>
                    <svg id="branchStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="branchStatusMenu" class="hidden absolute right-0 top-full mt-1.5 w-full min-w-[180px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $statusOpts = [
                            '' => 'Semua Status',
                            'active' => 'Cabang Aktif',
                            'inactive' => 'Cabang Nonaktif'
                        ];
                    @endphp
                    @foreach($statusOpts as $sKey => $sLabel)
                        <button 
                            type="button" 
                            onclick="selectBranchFilter('status', '{{ $sKey }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('status') === $sKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $sLabel }}</span>
                            @if(request('status') === $sKey)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

        </form>

    </div>

    <!-- Summary Info Bar -->
    <div class="flex items-center justify-between text-xs px-1">
        <div class="text-slate-500 font-medium">
            Menampilkan <strong class="text-slate-800 font-bold">{{ $branches->total() }}</strong> cabang terdaftar
            @if(request()->hasAny(['search', 'status']))
                &bull; <a href="{{ route('branches.index') }}" class="text-tealBrand hover:underline font-bold">Reset Filter</a>
            @endif
        </div>
        <div class="text-xs text-slate-400 font-mono font-medium">
            Total master: {{ $branches->total() }} cabang
        </div>
    </div>

    <!-- Data Container: Mobile Card List + Desktop Table -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Kantor Cabang</h3>
            <span class="text-xs text-slate-400 font-medium font-mono">Page {{ $branches->currentPage() }} of {{ $branches->lastPage() }}</span>
        </div>

        <!-- 1. Mobile Cards View (< md) -->
        <div class="block md:hidden space-y-3">
            @forelse ($branches as $index => $branch)
                <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200/90 rounded-xl p-3.5 space-y-2.5 transition-all">
                    
                    <!-- Top Row: Code & Name + Status -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200/70 px-2 py-0.5 rounded">
                                    {{ $branch->code }}
                                </span>
                                <span class="font-bold text-xs text-slate-900 leading-tight">{{ $branch->name }}</span>
                            </div>
                            @if($branch->address)
                                <div class="text-[11px] text-slate-500 mt-1">
                                    {{ $branch->address }}
                                </div>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($branch->status === 'active')
                                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Details: Phone, Users, Transactions -->
                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-slate-200/60">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">No. Telepon:</span>
                            <span class="font-mono text-slate-700">{{ $branch->phone ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Statistik Data:</span>
                            <span class="font-medium text-slate-700">{{ $branch->users_count }} user &bull; {{ $branch->transactions_count }} transaksi</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-end space-x-2">
                        <button 
                            type="button" 
                            onclick="openEditBranchModal({{ json_encode($branch) }})"
                            class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span>Edit</span>
                        </button>

                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Cabang?', text: 'Cabang {{ $branch->name }} akan dihapus. Pastikan tidak ada transaksi aktif yang tertaut.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus Cabang', form: this });" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="px-2.5 py-1 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Tidak ada data cabang yang sesuai dengan filter pencarian.
                </div>
            @endforelse
        </div>

        <!-- 2. Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5 w-12 text-center">#</th>
                        <th class="py-3 px-3.5 w-28">Kode</th>
                        <th class="py-3 px-3.5">Nama Kantor Cabang</th>
                        <th class="py-3 px-3.5">Alamat & Kontak</th>
                        <th class="py-3 px-3.5 text-center">User Terdaftar</th>
                        <th class="py-3 px-3.5 text-center">Arsip Transaksi</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($branches as $index => $branch)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-3.5 text-center font-mono text-slate-400">
                                {{ $branches->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-3.5">
                                <span class="font-mono text-[11px] font-extrabold text-tealBrand bg-teal-50 border border-teal-200/80 px-2 py-0.5 rounded">
                                    {{ $branch->code }}
                                </span>
                            </td>
                            <td class="py-3 px-3.5 font-bold text-slate-900">
                                {{ $branch->name }}
                            </td>
                            <td class="py-3 px-3.5 text-slate-600">
                                <div>{{ $branch->address ?? '-' }}</div>
                                @if($branch->phone)
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $branch->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center font-bold text-slate-800">
                                {{ $branch->users_count }}
                            </td>
                            <td class="py-3 px-3.5 text-center font-bold text-slate-800">
                                {{ $branch->transactions_count }}
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                @if($branch->status === 'active')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openEditBranchModal({{ json_encode($branch) }})"
                                        class="p-1 text-slate-400 hover:text-tealBrand transition-colors cursor-pointer"
                                        title="Edit Cabang"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Cabang?', text: 'Cabang {{ $branch->name }} akan dihapus. Pastikan tidak ada transaksi aktif yang tertaut.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus Cabang', form: this });" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus Cabang"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                                Tidak ada data cabang yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($branches->hasPages())
            <div class="pt-5 border-t border-slate-100">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Cabang -->
<div id="createBranchModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Tambah Cabang Baru</h3>
            <button type="button" onclick="closeCreateBranchModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('branches.store') }}" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf

            <!-- Kode Cabang & Nama Cabang -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">KODE CABANG</label>
                    <input type="text" name="code" required placeholder="CBG-01" class="w-full uppercase font-mono px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA CABANG</label>
                    <input type="text" name="name" required placeholder="Contoh: Cabang Bandung Barat" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>
            </div>

            <!-- No. Telepon -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NO. TELEPON / HP</label>
                <input type="text" name="phone" placeholder="0812-xxxx-xxxx" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Alamat -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">ALAMAT LENGKAP</label>
                <textarea name="address" rows="2" placeholder="Jl. Sudirman No. 123..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"></textarea>
            </div>

            <!-- Status Custom Popover -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS CABANG</label>
                <div class="relative" id="createBranchStatusDropdownContainer">
                    <input type="hidden" name="status" id="createBranchStatusInput" value="active" required>
                    <button 
                        type="button"
                        id="createBranchStatusTrigger"
                        onclick="toggleBranchModalDropdown('createBranchStatus')"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="createBranchStatusLabel">Cabang Aktif</span>
                        <svg id="createBranchStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="createBranchStatusMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                        <button 
                            type="button"
                            onclick="selectBranchModalStatus('create', 'active', 'Cabang Aktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                            id="createBranchStatusOpt_active"
                        >
                            <span>Cabang Aktif</span>
                            <svg id="createBranchStatusCheck_active" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                        <button 
                            type="button"
                            onclick="selectBranchModalStatus('create', 'inactive', 'Cabang Nonaktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                            id="createBranchStatusOpt_inactive"
                        >
                            <span>Cabang Nonaktif</span>
                            <svg id="createBranchStatusCheck_inactive" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                <button type="button" onclick="closeCreateBranchModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                    Simpan Cabang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Cabang -->
<div id="editBranchModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Edit Data Cabang</h3>
            <button type="button" onclick="closeEditBranchModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editBranchForm" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf
            @method('PUT')

            <!-- Kode Cabang & Nama Cabang -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">KODE CABANG</label>
                    <input type="text" name="code" id="editBranchCode" required class="w-full uppercase font-mono px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA CABANG</label>
                    <input type="text" name="name" id="editBranchName" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>
            </div>

            <!-- No. Telepon -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NO. TELEPON / HP</label>
                <input type="text" name="phone" id="editBranchPhone" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Alamat -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">ALAMAT LENGKAP</label>
                <textarea name="address" id="editBranchAddress" rows="2" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"></textarea>
            </div>

            <!-- Status Custom Popover -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS CABANG</label>
                <div class="relative" id="editBranchStatusDropdownContainer">
                    <input type="hidden" name="status" id="editBranchStatusInput" value="active" required>
                    <button 
                        type="button"
                        id="editBranchStatusTrigger"
                        onclick="toggleBranchModalDropdown('editBranchStatus')"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="editBranchStatusLabel">Cabang Aktif</span>
                        <svg id="editBranchStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="editBranchStatusMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                        <button 
                            type="button"
                            onclick="selectBranchModalStatus('edit', 'active', 'Cabang Aktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                            id="editBranchStatusOpt_active"
                        >
                            <span>Cabang Aktif</span>
                            <svg id="editBranchStatusCheck_active" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                        <button 
                            type="button"
                            onclick="selectBranchModalStatus('edit', 'inactive', 'Cabang Nonaktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                            id="editBranchStatusOpt_inactive"
                        >
                            <span>Cabang Nonaktif</span>
                            <svg id="editBranchStatusCheck_inactive" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                <button type="button" onclick="closeEditBranchModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                    Perbarui Cabang
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Live Search Debounce
    let branchSearchDebounceTimer = null;
    function handleBranchSearchDebounce(input) {
        const clearBtn = document.getElementById('branchSearchClearBtn');
        const staticIcon = document.getElementById('branchSearchStaticIcon');
        const loadingIcon = document.getElementById('branchSearchLoadingIcon');

        if (input.value.trim().length > 0) {
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else {
            if (clearBtn) clearBtn.classList.add('hidden');
        }

        clearTimeout(branchSearchDebounceTimer);
        
        if (staticIcon && loadingIcon) {
            staticIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
        }

        branchSearchDebounceTimer = setTimeout(() => {
            document.getElementById('branchFilterForm').submit();
        }, 450);
    }

    function clearBranchSearchInput() {
        const input = document.getElementById('branchSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('branchSearchClearBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        document.getElementById('branchFilterForm').submit();
    }

    function toggleBranchMobileFilter() {
        const form = document.getElementById('branchFilterForm');
        const text = document.getElementById('branchMobileFilterText');
        const icon = document.getElementById('branchMobileFilterIcon');
        if (!form) return;

        const isHidden = form.classList.contains('hidden');
        if (isHidden) {
            form.classList.remove('hidden');
            if (text) text.textContent = 'Tutup Filter';
            if (icon) icon.classList.remove('rotate-180');
        } else {
            form.classList.add('hidden');
            if (text) text.textContent = 'Buka Filter';
            if (icon) icon.classList.add('rotate-180');
        }
    }

    // Filter Popover
    function toggleBranchPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function selectBranchFilter(filterName, value) {
        if (filterName === 'status') {
            document.getElementById('branchStatusFilterInput').value = value;
        }
        document.getElementById('branchFilterForm').submit();
    }

    // Modal Status Popover
    function toggleBranchModalDropdown(prefix) {
        const menu = document.getElementById(prefix + 'Menu');
        const chevron = document.getElementById(prefix + 'Chevron');
        if (!menu) return;

        const all = ['createBranchStatus', 'editBranchStatus'];
        all.forEach(p => {
            if (p !== prefix) {
                const m = document.getElementById(p + 'Menu');
                const c = document.getElementById(p + 'Chevron');
                if (m) m.classList.add('hidden');
                if (c) c.classList.remove('rotate-180');
            }
        });

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function selectBranchModalStatus(modalType, value, label) {
        const prefix = modalType + 'BranchStatus';
        const input = document.getElementById(prefix + 'Input');
        const labelEl = document.getElementById(prefix + 'Label');
        const menu = document.getElementById(prefix + 'Menu');
        const chevron = document.getElementById(prefix + 'Chevron');

        if (input) input.value = value;
        if (labelEl) labelEl.textContent = label;

        if (menu) {
            const btns = menu.querySelectorAll('button');
            btns.forEach(b => {
                b.classList.remove('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                b.classList.add('text-slate-700', 'font-medium');
                const svg = b.querySelector('svg');
                if (svg) svg.classList.add('hidden');
            });

            const targetBtn = document.getElementById(prefix + 'Opt_' + value);
            if (targetBtn) {
                targetBtn.classList.remove('text-slate-700', 'font-medium');
                targetBtn.classList.add('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                const check = document.getElementById(prefix + 'Check_' + value);
                if (check) check.classList.remove('hidden');
            }

            menu.classList.add('hidden');
        }
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function setBranchModalStatusValue(modalType, value, label) {
        const prefix = modalType + 'BranchStatus';
        const input = document.getElementById(prefix + 'Input');
        const labelEl = document.getElementById(prefix + 'Label');
        const menu = document.getElementById(prefix + 'Menu');

        if (input) input.value = value;
        if (labelEl) labelEl.textContent = label;

        if (menu) {
            const btns = menu.querySelectorAll('button');
            btns.forEach(b => {
                b.classList.remove('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                b.classList.add('text-slate-700', 'font-medium');
                const svg = b.querySelector('svg');
                if (svg) svg.classList.add('hidden');
            });

            const targetBtn = document.getElementById(prefix + 'Opt_' + value);
            if (targetBtn) {
                targetBtn.classList.remove('text-slate-700', 'font-medium');
                targetBtn.classList.add('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                const check = document.getElementById(prefix + 'Check_' + value);
                if (check) check.classList.remove('hidden');
            }
        }
    }

    // Outside Click
    document.addEventListener('click', function(e) {
        const filterContainer = document.getElementById('branchStatusDropdownContainer');
        if (filterContainer && !filterContainer.contains(e.target)) {
            const menu = document.getElementById('branchStatusMenu');
            const ch = document.getElementById('branchStatusChevron');
            if (menu) menu.classList.add('hidden');
            if (ch) ch.classList.remove('rotate-180');
        }

        const modalContainers = ['createBranchStatusDropdownContainer', 'editBranchStatusDropdownContainer'];
        const isInside = modalContainers.some(id => {
            const el = document.getElementById(id);
            return el && el.contains(e.target);
        });

        if (!isInside) {
            ['createBranchStatus', 'editBranchStatus'].forEach(p => {
                const m = document.getElementById(p + 'Menu');
                const c = document.getElementById(p + 'Chevron');
                if (m) m.classList.add('hidden');
                if (c) c.classList.remove('rotate-180');
            });
        }
    });

    // Modals
    function openCreateBranchModal() {
        const modal = document.getElementById('createBranchModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setBranchModalStatusValue('create', 'active', 'Cabang Aktif');
    }

    function closeCreateBranchModal() {
        const modal = document.getElementById('createBranchModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditBranchModal(branch) {
        const modal = document.getElementById('editBranchModal');
        const form = document.getElementById('editBranchForm');
        
        form.action = `/branches/${branch.id}`;
        document.getElementById('editBranchCode').value = branch.code;
        document.getElementById('editBranchName').value = branch.name;
        document.getElementById('editBranchPhone').value = branch.phone || '';
        document.getElementById('editBranchAddress').value = branch.address || '';
        
        const isAct = branch.status === 'active';
        setBranchModalStatusValue('edit', branch.status, isAct ? 'Cabang Aktif' : 'Cabang Nonaktif');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditBranchModal() {
        const modal = document.getElementById('editBranchModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Keyboard shortcut '/'
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const searchInput = document.getElementById('branchSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });
</script>
@endpush
@endsection
