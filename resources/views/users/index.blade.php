@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Kelola akun, hak akses role cabang, dan status pengguna sistem.</p>
        </div>
        <button 
            type="button" 
            onclick="openCreateModal()" 
            class="inline-flex items-center justify-center space-x-1.5 px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-colors cursor-pointer"
        >
            <span class="text-sm leading-none">+</span>
            <span>Tambah User Baru</span>
        </button>
    </div>

    <!-- Role Capabilities Info (Mobile Swipe Carousel & Desktop Grid) -->
    <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-none gap-3.5 -mx-4 px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:px-0 md:pb-0 md:grid md:grid-cols-3 md:gap-4 md:overflow-visible">
        
        <!-- Super Admin Card -->
        <div class="min-w-[80vw] sm:min-w-[50vw] md:min-w-0 snap-center shrink-0 md:shrink bg-white rounded-xl border border-slate-200 p-4 sm:p-4.5 shadow-sm border-l-4 border-l-purple-500 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Super Administrator</span>
                    <span class="text-[9px] font-extrabold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200/80">FULL ACCESS</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Akses semua cabang, kelola user, master data outlet, resume statistik analitik global, dan export semua cabang.
                </p>
            </div>
        </div>

        <!-- Admin Cabang Card -->
        <div class="min-w-[80vw] sm:min-w-[50vw] md:min-w-0 snap-center shrink-0 md:shrink bg-white rounded-xl border border-slate-200 p-4 sm:p-4.5 shadow-sm border-l-4 border-l-cyan-500 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Admin Cabang</span>
                    <span class="text-[9px] font-extrabold bg-cyan-50 text-cyan-700 px-2 py-0.5 rounded-full border border-cyan-200/80">OUTLET LEVEL</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Input data transaksi/arsip menu, edit data cabang miliknya, lihat riwayat cabang, dan export laporan cabang.
                </p>
            </div>
        </div>

        <!-- Viewer / Staf Card -->
        <div class="min-w-[80vw] sm:min-w-[50vw] md:min-w-0 snap-center shrink-0 md:shrink bg-white rounded-xl border border-slate-200 p-4 sm:p-4.5 shadow-sm border-l-4 border-l-slate-400 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Viewer / Staf</span>
                    <span class="text-[9px] font-extrabold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full border border-slate-200/80">READ ONLY</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Hanya memiliki hak akses untuk melihat data arsip dan mengunduh laporan berkas tanpa izin mengubah data.
                </p>
            </div>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        
        <!-- Filter Header with Toggle for Mobile -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>PENCARIAN & FILTER</span>
                @if(request()->hasAny(['search', 'role', 'branch_id', 'status']))
                    <span class="w-2 h-2 rounded-full bg-tealBrand inline-block"></span>
                @endif
            </div>

            <button 
                type="button" 
                onclick="toggleUserMobileFilter()"
                id="userMobileFilterBtn" 
                class="sm:hidden text-xs font-bold text-tealBrand hover:text-tealBrand-hover inline-flex items-center space-x-1 cursor-pointer"
            >
                <span id="userMobileFilterText">Tutup Filter</span>
                <svg id="userMobileFilterIcon" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <form id="userFilterForm" method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            <input type="hidden" name="role" id="userRoleFilterInput" value="{{ request('role') }}">
            <input type="hidden" name="branch_id" id="userBranchFilterInput" value="{{ request('branch_id') }}">
            <input type="hidden" name="status" id="userStatusFilterInput" value="{{ request('status') }}">

            <!-- 1. Live Search Input -->
            <div class="relative sm:col-span-2 lg:col-span-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg id="userSearchStaticIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg id="userSearchLoadingIcon" class="w-4 h-4 text-tealBrand animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                <input 
                    type="text" 
                    id="userSearchInput"
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama atau email..." 
                    autocomplete="off"
                    oninput="handleUserSearchDebounce(this)"
                    class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-tealBrand focus:ring-1 focus:ring-tealBrand shadow-sm transition"
                >

                <!-- Clear Button -->
                <button 
                    type="button" 
                    id="userSearchClearBtn"
                    onclick="clearUserSearchInput()"
                    class="{{ request('search') ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"
                    title="Hapus pencarian"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- 2. Role Custom Dropdown -->
            <div class="relative" id="userRoleDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleUserPopover('userRoleMenu', 'userRoleChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if(request('role') === 'superadmin')
                            Super Admin
                        @elseif(request('role') === 'admin_cabang')
                            Admin Cabang
                        @elseif(request('role') === 'viewer')
                            Viewer
                        @else
                            Semua Role
                        @endif
                    </span>
                    <svg id="userRoleChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="userRoleMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[190px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $roleOptions = [
                            '' => 'Semua Role',
                            'superadmin' => 'Super Admin',
                            'admin_cabang' => 'Admin Cabang',
                            'viewer' => 'Viewer (Read-Only)'
                        ];
                    @endphp
                    @foreach($roleOptions as $rKey => $rLabel)
                        <button 
                            type="button" 
                            onclick="selectUserFilter('role', '{{ $rKey }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('role') === $rKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $rLabel }}</span>
                            @if(request('role') === $rKey)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Cabang Custom Dropdown -->
            <div class="relative" id="userBranchDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleUserPopover('userBranchMenu', 'userBranchChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if(request('branch_id'))
                            {{ $branches->firstWhere('id', request('branch_id'))->name ?? 'Semua Cabang' }}
                        @else
                            Semua Cabang
                        @endif
                    </span>
                    <svg id="userBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="userBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectUserFilter('branch_id', '')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty(request('branch_id')) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Cabang</span>
                        @if(empty(request('branch_id')))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    @foreach($branches as $branch)
                        <button 
                            type="button" 
                            onclick="selectUserFilter('branch_id', '{{ $branch->id }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('branch_id') == $branch->id ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $branch->name }}</span>
                            @if(request('branch_id') == $branch->id)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 4. Status Custom Dropdown -->
            <div class="relative" id="userStatusDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleUserPopover('userStatusMenu', 'userStatusChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if(request('status') === 'active')
                            Aktif
                        @elseif(request('status') === 'inactive')
                            Nonaktif
                        @else
                            Semua Status
                        @endif
                    </span>
                    <svg id="userStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="userStatusMenu" class="hidden absolute right-0 top-full mt-1.5 w-full min-w-[180px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $statusOptions = [
                            '' => 'Semua Status',
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif'
                        ];
                    @endphp
                    @foreach($statusOptions as $stKey => $stLabel)
                        <button 
                            type="button" 
                            onclick="selectUserFilter('status', '{{ $stKey }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('status') === $stKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $stLabel }}</span>
                            @if(request('status') === $stKey)
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
            Menampilkan <strong class="text-slate-800 font-bold">{{ $users->total() }}</strong> pengguna
            @if(request()->hasAny(['search', 'role', 'branch_id', 'status']))
                &bull; <a href="{{ route('users.index') }}" class="text-tealBrand hover:underline font-bold">Reset Filter</a>
            @endif
        </div>
        <div class="text-xs text-slate-400 font-mono font-medium">
            Total terdaftar: {{ $users->total() }} user
        </div>
    </div>

    <!-- Data Container: Mobile Card List + Desktop Table -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Pengguna</h3>
            <span class="text-xs text-slate-400 font-medium font-mono">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
        </div>

        <!-- 1. Mobile Cards View (< md) -->
        <div class="block md:hidden space-y-3">
            @forelse ($users as $index => $user)
                <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200/90 rounded-xl p-3.5 space-y-2.5 transition-all">
                    
                    <!-- Top Row: Avatar & Name + Status -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center space-x-2.5">
                            <!-- Initials Avatar -->
                            <div class="w-8 h-8 rounded-full bg-navy-900 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-slate-900 leading-tight">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="ml-1 text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-bold">Anda</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($user->status === 'active')
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

                    <!-- Role & Branch Info -->
                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-slate-200/60">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Role:</span>
                            @if($user->role === 'superadmin')
                                <span class="font-bold text-purple-700">Super Admin</span>
                            @elseif($user->role === 'admin_cabang')
                                <span class="font-bold text-cyan-700">Admin Cabang</span>
                            @else
                                <span class="font-bold text-slate-600">Viewer</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Cabang:</span>
                            <span class="font-bold text-slate-800">{{ $user->branch->name ?? 'Semua Cabang' }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-end space-x-2">
                        <button 
                            type="button" 
                            onclick="openEditModal({{ json_encode($user) }})"
                            class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span>Edit</span>
                        </button>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dipulihkan.');" class="inline">
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
                        @endif
                    </div>

                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Tidak ada data pengguna yang sesuai dengan filter pencarian.
                </div>
            @endforelse
        </div>

        <!-- 2. Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5 w-12 text-center">#</th>
                        <th class="py-3 px-3.5">Nama User</th>
                        <th class="py-3 px-3.5">Email</th>
                        <th class="py-3 px-3.5">Role Akses</th>
                        <th class="py-3 px-3.5">Penempatan Cabang</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($users as $index => $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-3.5 text-center font-mono text-slate-400">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-full bg-navy-900 text-white font-bold text-[11px] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="font-bold text-slate-900">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="ml-1 text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-bold">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5 text-slate-600 font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="py-3 px-3.5">
                                @if($user->role === 'superadmin')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80">
                                        Super Admin
                                    </span>
                                @elseif($user->role === 'admin_cabang')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">
                                        Admin Cabang
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                        Viewer
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                @if($user->branch)
                                    <span class="font-bold text-slate-800">{{ $user->branch->name }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">{{ $user->branch->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">Semua Cabang (Global)</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                @if($user->status === 'active')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode($user) }})"
                                        class="p-1 text-slate-400 hover:text-slate-800 transition-colors cursor-pointer"
                                        title="Edit Pengguna"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dipulihkan.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                                title="Hapus Pengguna"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                Tidak ada data pengguna yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="pt-5 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah User -->
<div id="createModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA LENGKAP</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">ALAMAT EMAIL</label>
                <input type="email" name="email" required placeholder="budi@cabang.com" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PASSWORD</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Role & Branch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">HAK AKSES (ROLE)</label>
                    <div class="relative">
                        <select name="role" id="createRole" onchange="toggleBranchField('create')" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="admin_cabang">Admin Cabang</option>
                            <option value="superadmin">Super Admin</option>
                            <option value="viewer">Viewer (Read-Only)</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                <div id="createBranchContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PILIH CABANG</label>
                    <div class="relative">
                        <select name="branch_id" id="createBranchId" class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS AKUN</label>
                <div class="relative">
                    <select name="status" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                <button type="button" onclick="closeCreateModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Edit Data Pengguna</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA LENGKAP</label>
                <input type="text" name="name" id="editName" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">ALAMAT EMAIL</label>
                <input type="email" name="email" id="editEmail" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Password Reset (Optional) -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">
                    PASSWORD BARU <span class="text-slate-400 normal-case font-normal">(Kosongkan jika tidak ingin diubah)</span>
                </label>
                <input type="password" name="password" placeholder="Minimal 6 karakter..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Role & Branch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">HAK AKSES (ROLE)</label>
                    <div class="relative">
                        <select name="role" id="editRole" onchange="toggleBranchField('edit')" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="admin_cabang">Admin Cabang</option>
                            <option value="superadmin">Super Admin</option>
                            <option value="viewer">Viewer (Read-Only)</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                <div id="editBranchContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PILIH CABANG</label>
                    <div class="relative">
                        <select name="branch_id" id="editBranchId" class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS AKUN</label>
                <div class="relative">
                    <select name="status" id="editStatus" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                <button type="button" onclick="closeEditModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Live Search Debounce
    let userSearchDebounceTimer = null;
    function handleUserSearchDebounce(input) {
        const clearBtn = document.getElementById('userSearchClearBtn');
        const staticIcon = document.getElementById('userSearchStaticIcon');
        const loadingIcon = document.getElementById('userSearchLoadingIcon');

        if (input.value.trim().length > 0) {
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else {
            if (clearBtn) clearBtn.classList.add('hidden');
        }

        clearTimeout(userSearchDebounceTimer);
        
        if (staticIcon && loadingIcon) {
            staticIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
        }

        userSearchDebounceTimer = setTimeout(() => {
            document.getElementById('userFilterForm').submit();
        }, 450);
    }

    function clearUserSearchInput() {
        const input = document.getElementById('userSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('userSearchClearBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        document.getElementById('userFilterForm').submit();
    }

    function toggleUserMobileFilter() {
        const form = document.getElementById('userFilterForm');
        const text = document.getElementById('userMobileFilterText');
        const icon = document.getElementById('userMobileFilterIcon');
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

    // Custom Popovers
    function toggleUserPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const allPopovers = ['userRoleMenu', 'userBranchMenu', 'userStatusMenu'];
        const allChevrons = ['userRoleChevron', 'userBranchChevron', 'userStatusChevron'];

        allPopovers.forEach((id, idx) => {
            if (id !== menuId) {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
                const ch = document.getElementById(allChevrons[idx]);
                if (ch) ch.classList.remove('rotate-180');
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

    function selectUserFilter(filterName, value) {
        if (filterName === 'role') {
            document.getElementById('userRoleFilterInput').value = value;
        } else if (filterName === 'branch_id') {
            document.getElementById('userBranchFilterInput').value = value;
        } else if (filterName === 'status') {
            document.getElementById('userStatusFilterInput').value = value;
        }
        document.getElementById('userFilterForm').submit();
    }

    document.addEventListener('click', function(e) {
        const containers = [
            'userRoleDropdownContainer',
            'userBranchDropdownContainer',
            'userStatusDropdownContainer'
        ];
        const isInsideAny = containers.some(id => {
            const el = document.getElementById(id);
            return el && el.contains(e.target);
        });

        if (!isInsideAny) {
            ['userRoleMenu', 'userBranchMenu', 'userStatusMenu'].forEach((id, idx) => {
                const menu = document.getElementById(id);
                if (menu) menu.classList.add('hidden');
            });
            ['userRoleChevron', 'userBranchChevron', 'userStatusChevron'].forEach(id => {
                const ch = document.getElementById(id);
                if (ch) ch.classList.remove('rotate-180');
            });
        }
    });

    // Modals
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
            if (branchSelect) {
                branchSelect.value = '';
                branchSelect.removeAttribute('required');
            }
        } else {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            if (branchSelect && role === 'admin_cabang') {
                branchSelect.setAttribute('required', 'required');
            }
        }
    }

    // Keyboard shortcut '/' to focus search input
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const searchInput = document.getElementById('userSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });
</script>
@endpush
@endsection
