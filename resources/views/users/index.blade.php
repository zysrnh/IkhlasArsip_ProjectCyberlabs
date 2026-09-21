@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                @if(auth()->user()->isKepalaCabang())
                    Kelola akun Admin Cabang dan staf operasional di wilayah cabang Anda.
                @else
                    Kelola akun, hak akses role cabang, dan status pengguna sistem.
                @endif
            </p>
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
    <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-none gap-3.5 -mx-4 px-4 pb-2 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0 lg:pb-0 lg:grid lg:grid-cols-4 lg:gap-3.5 lg:overflow-visible">
        
        <!-- Super Admin Card -->
        <div class="min-w-[78vw] sm:min-w-[45vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-4 shadow-sm border-l-4 border-l-purple-500 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Super Admin</span>
                    <span class="text-[9px] font-extrabold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200/80">FULL ACCESS</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Akses penuh ke seluruh data cabang, kelola pengguna sistem, dan pengaturan teknis global.
                </p>
            </div>
        </div>

        <!-- Kepala Cabang Card -->
        <div class="min-w-[78vw] sm:min-w-[45vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-4 shadow-sm border-l-4 border-l-tealBrand flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Kepala Cabang</span>
                    <span class="text-[9px] font-extrabold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full border border-teal-200/80">SUPERVISI</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Memantau statistik cabang, membuat akun admin cabang, dan mengunduh laporan berkas.
                </p>
            </div>
        </div>

        <!-- Admin Cabang Card -->
        <div class="min-w-[78vw] sm:min-w-[45vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-4 shadow-sm border-l-4 border-l-cyan-500 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Admin Cabang</span>
                    <span class="text-[9px] font-extrabold bg-cyan-50 text-cyan-700 px-2 py-0.5 rounded-full border border-cyan-200/80">OUTLET LEVEL</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Input & edit data transaksi di cabangnya masing-masing, import data Excel, dan export laporan cabang.
                </p>
            </div>
        </div>

        <!-- Viewer / Staf Card -->
        <div class="min-w-[78vw] sm:min-w-[45vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-4 shadow-sm border-l-4 border-l-slate-400 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Viewer / Staf</span>
                    <span class="text-[9px] font-extrabold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full border border-slate-200/80">READ ONLY</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                    Melihat data arsip transaksi dan mengunduh laporan berkas tanpa izin mengubah atau menghapus data.
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
                        @elseif(request('role') === 'kepala_cabang')
                            Kepala Cabang
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

                <div id="userRoleMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $roleOptions = auth()->user()->isSuperAdmin()
                            ? [
                                '' => 'Semua Role',
                                'superadmin' => 'Super Admin',
                                'kepala_cabang' => 'Kepala Cabang',
                                'admin_cabang' => 'Admin Cabang',
                                'viewer' => 'Viewer (Read-Only)'
                            ]
                            : [
                                '' => 'Semua Role',
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
                            <!-- Avatar Image / Initials -->
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 shadow-xs">
                            @else
                                <div class="w-8 h-8 rounded-full {{ $user->role === 'kepala_cabang' ? 'bg-tealBrand text-white' : ($user->role === 'superadmin' ? 'bg-purple-700 text-white' : 'bg-navy-900 text-white') }} font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                    {{ $user->initials }}
                                </div>
                            @endif
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
                            @elseif($user->role === 'kepala_cabang')
                                <span class="font-bold text-tealBrand">Kepala Cabang</span>
                            @elseif($user->role === 'admin_cabang')
                                <span class="font-bold text-cyan-700">Admin Cabang</span>
                            @else
                                <span class="font-bold text-slate-600">Viewer</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Cabang:</span>
                            @if(in_array($user->role, ['kepala_cabang', 'viewer']))
                                @if($user->managedBranches->count() > 1)
                                    <div class="mt-0.5 space-y-0.5">
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200/80">
                                            {{ $user->managedBranches->count() }} Cabang
                                        </span>
                                        <div class="text-[11px] font-semibold text-slate-700">
                                            {{ $user->managedBranches->pluck('name')->implode(', ') }}
                                        </div>
                                    </div>
                                @elseif($user->managedBranches->count() === 1)
                                    <span class="font-bold text-slate-800">{{ $user->managedBranches->first()->name }}</span>
                                @elseif($user->branch)
                                    <span class="font-bold text-slate-800">{{ $user->branch->name }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum Ditentukan</span>
                                @endif
                            @elseif($user->branch)
                                <span class="font-bold text-slate-800">{{ $user->branch->name }}</span>
                            @else
                                <span class="text-slate-400 italic">Semua Cabang (Global)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-end space-x-2">
                        @if(auth()->user()->isSuperAdmin() || (!in_array($user->role, ['superadmin', 'kepala_cabang']) || $user->id === auth()->id()))
                            <button 
                                type="button" 
                                onclick="openEditModal({{ json_encode($user->load('managedBranches')) }})"
                                class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                <span>Edit</span>
                            </button>
                        @endif

                        @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || !in_array($user->role, ['superadmin', 'kepala_cabang'])))
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Pengguna?', text: 'Pengguna {{ $user->name }} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus Permanen', form: this });" class="inline">
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
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 shadow-xs">
                                    @else
                                        <div class="w-7 h-7 rounded-full {{ $user->role === 'kepala_cabang' ? 'bg-tealBrand text-white' : ($user->role === 'superadmin' ? 'bg-purple-700 text-white' : 'bg-navy-900 text-white') }} font-bold text-[11px] flex items-center justify-center shrink-0">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
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
                                @elseif($user->role === 'kepala_cabang')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/80">
                                        Kepala Cabang
                                    </span>
                                @elseif($user->role === 'admin_cabang')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">
                                        Admin Cabang
                                    </span>
                                @elseif($user->role === 'admin_dapur')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        Admin Dapur
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                        Viewer
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                @if(in_array($user->role, ['kepala_cabang', 'viewer']))
                                    @if($user->managedBranches->count() > 1)
                                        <div class="flex items-center gap-1.5 flex-wrap max-w-sm">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-teal-50 text-teal-700 border border-teal-200/80 shrink-0">
                                                {{ $user->managedBranches->count() }} Cabang
                                            </span>
                                            <span class="text-xs font-semibold text-slate-700">
                                                {{ $user->managedBranches->pluck('name')->implode(', ') }}
                                            </span>
                                        </div>
                                    @elseif($user->managedBranches->count() === 1)
                                        <span class="text-xs font-semibold text-slate-700">{{ $user->managedBranches->first()->name }}</span>
                                    @elseif($user->branch)
                                        <span class="text-xs font-semibold text-slate-700">{{ $user->branch->name }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum Ditentukan</span>
                                    @endif
                                @elseif($user->branch)
                                    <span class="text-xs font-semibold text-slate-700">{{ $user->branch->name }}</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Semua Cabang (Global)</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                @if($user->status === 'active')
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
                                    @if(auth()->user()->isSuperAdmin() || (!in_array($user->role, ['superadmin', 'kepala_cabang']) || $user->id === auth()->id()))
                                        <button 
                                            type="button" 
                                            onclick="openEditModal({{ json_encode($user->load('managedBranches')) }})"
                                            class="p-1 text-slate-400 hover:text-tealBrand transition-colors cursor-pointer"
                                            title="Edit Pengguna"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || !in_array($user->role, ['superadmin', 'kepala_cabang'])))
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Pengguna?', text: 'Pengguna {{ $user->name }} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus Permanen', form: this });" class="inline">
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
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
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
                <input type="email" name="email" required placeholder="budi@ikhlas.com" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition">
            </div>

            <!-- Password Field with Generator & Strength Meter -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">PASSWORD</label>
                    <div class="flex items-center space-x-1.5">
                        <button 
                            type="button" 
                            onclick="generateStrongPassword('createPassword', 'create')"
                            class="inline-flex items-center space-x-1 text-[10.5px] font-bold text-tealBrand hover:text-tealBrand-hover bg-teal-50 hover:bg-teal-100/80 px-2 py-0.5 rounded-lg transition-colors cursor-pointer"
                            title="Buat password acak yang kuat"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span>Generate</span>
                        </button>
                        <button 
                            type="button" 
                            onclick="copyPasswordToClipboard('createPassword', this)"
                            class="inline-flex items-center space-x-1 text-[10.5px] font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2 py-0.5 rounded-lg transition-colors cursor-pointer"
                            title="Salin password ke clipboard"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="createPassword" 
                        required 
                        placeholder="Minimal 6 karakter" 
                        oninput="checkPasswordStrength(this.value, 'create')"
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition font-mono"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordVisibility('createPassword', 'eyeIcon_createPassword')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                        title="Tampilkan / Sembunyikan Password"
                    >
                        <svg id="eyeIcon_createPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <!-- Password Strength Meter Bar -->
                <div id="strengthContainer_create" class="mt-2 hidden space-y-1">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-400 font-medium">Kekuatan Password:</span>
                        <span id="strengthText_create" class="font-bold text-rose-500">Sangat Lemah</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden p-0.5">
                        <div id="strengthBar_create_1" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_create_2" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_create_3" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_create_4" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                    </div>
                    <p id="strengthFeedback_create" class="text-[9.5px] text-slate-400 font-medium leading-tight pt-0.5">
                        Kombinasikan huruf besar, kecil, angka, dan simbol untuk password yang kuat.
                    </p>
                </div>
            </div>

            <!-- Role & Branch Custom Popovers -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Role -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">HAK AKSES (ROLE)</label>
                    <div class="relative" id="createRoleDropdownContainer">
                        <input type="hidden" name="role" id="createRole" value="admin_cabang" required>
                        <button 
                            type="button"
                            id="createRoleTrigger"
                            onclick="toggleModalDropdown('createRole')"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                        >
                            <span id="createRoleLabel">Admin Cabang</span>
                            <svg id="createRoleChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="createRoleMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                            @php
                                $modalRoles = auth()->user()->isSuperAdmin()
                                    ? [
                                        'admin_cabang' => 'Admin Cabang',
                                        'admin_dapur' => 'Admin Dapur',
                                        'kepala_cabang' => 'Kepala Cabang',
                                        'superadmin' => 'Super Admin',
                                        'viewer' => 'Viewer (Read-Only)'
                                    ]
                                    : [
                                        'admin_cabang' => 'Admin Cabang',
                                        'admin_dapur' => 'Admin Dapur',
                                        'viewer' => 'Viewer (Read-Only)'
                                    ];
                            @endphp
                            @foreach($modalRoles as $mRoleKey => $mRoleLabel)
                                <button 
                                    type="button"
                                    id="createRoleOpt_{{ $mRoleKey }}"
                                    onclick="selectModalDropdown('create', 'Role', '{{ $mRoleKey }}', '{{ $mRoleLabel }}')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $mRoleKey === 'admin_cabang' ? 'text-tealBrand font-bold bg-teal-50/60' : 'hover:bg-slate-50 font-medium text-slate-700' }}"
                                >
                                    <span>{{ $mRoleLabel }}</span>
                                    <svg id="createRoleCheck_{{ $mRoleKey }}" class="w-3.5 h-3.5 text-tealBrand {{ $mRoleKey === 'admin_cabang' ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Single Branch (Admin Cabang / Admin Dapur) -->
                <div id="createSingleBranchContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PILIH CABANG</label>
                    <div class="relative" id="createBranchDropdownContainer">
                        <input type="hidden" name="branch_id" id="createBranchId" value="{{ auth()->user()->isKepalaCabang() && auth()->user()->branch_id ? auth()->user()->branch_id : '' }}">
                        <button 
                            type="button"
                            id="createBranchTrigger"
                            onclick="toggleModalDropdown('createBranch')"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                        >
                            <span id="createBranchLabel" class="truncate">
                                @if(auth()->user()->isKepalaCabang() && auth()->user()->branch_id)
                                    {{ auth()->user()->branch->name ?? '-- Pilih Cabang --' }}
                                @else
                                    -- Pilih Cabang --
                                @endif
                            </span>
                            <svg id="createBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="createBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full max-h-52 overflow-y-auto bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                            @if(!auth()->user()->isKepalaCabang())
                                <button 
                                    type="button" 
                                    id="createBranchOpt_"
                                    onclick="selectModalDropdown('create', 'Branch', '', '-- Pilih Cabang --')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                                >
                                    <span>-- Pilih Cabang --</span>
                                    <svg id="createBranchCheck_" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endif
                            @foreach($branches as $branch)
                                <button 
                                    type="button" 
                                    id="createBranchOpt_{{ $branch->id }}"
                                    onclick="selectModalDropdown('create', 'Branch', '{{ $branch->id }}', '{{ addslashes($branch->name) }}')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                                >
                                    <span class="truncate">{{ $branch->name }}</span>
                                    <svg id="createBranchCheck_{{ $branch->id }}" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Multi Branch Checkbox (Khusus Kepala Cabang) -->
            <div id="createMultiBranchContainer" class="hidden space-y-1.5 pt-1">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                        WILAYAH CABANG YANG DIKELOLA (BISA PILIH LEBIH DARI 1)
                    </label>
                    <span class="text-[10px] text-tealBrand font-bold">Multi-Cabang</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/50 p-3 rounded-xl border border-slate-200 max-h-48 overflow-y-auto">
                    @foreach($branches as $branch)
                        <label class="flex items-center space-x-2.5 p-2 bg-white rounded-lg border border-slate-200/80 hover:border-tealBrand cursor-pointer transition">
                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" class="create-branch-cb rounded border-slate-300 text-tealBrand focus:ring-tealBrand w-4 h-4 cursor-pointer">
                            <span class="text-xs font-bold text-slate-800 select-none truncate">{{ $branch->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Status Custom Popover -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS AKUN</label>
                <div class="relative" id="createStatusDropdownContainer">
                    <input type="hidden" name="status" id="createStatus" value="active" required>
                    <button 
                        type="button"
                        id="createStatusTrigger"
                        onclick="toggleModalDropdown('createStatus')"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="createStatusLabel">Aktif</span>
                        <svg id="createStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="createStatusMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                        <button 
                            type="button"
                            id="createStatusOpt_active"
                            onclick="selectModalDropdown('create', 'Status', 'active', 'Aktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                        >
                            <span>Aktif</span>
                            <svg id="createStatusCheck_active" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                        <button 
                            type="button"
                            id="createStatusOpt_inactive"
                            onclick="selectModalDropdown('create', 'Status', 'inactive', 'Nonaktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                        >
                            <span>Nonaktif</span>
                            <svg id="createStatusCheck_inactive" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Send Credentials Email Checkbox -->
            <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3 flex items-start space-x-2.5">
                <input 
                    type="checkbox" 
                    name="send_email" 
                    id="createSendEmail" 
                    value="1" 
                    checked 
                    class="mt-0.5 rounded border-slate-300 text-tealBrand focus:ring-tealBrand w-4 h-4 cursor-pointer"
                >
                <label for="createSendEmail" class="text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <span>Kirim informasi akun & password ke email pengguna</span>
                    <p class="text-[10.5px] text-slate-400 font-normal mt-0.5">Pengguna akan menerima email berisi detail login dan instruksi masuk sistem secara otomatis.</p>
                </label>
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
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
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

            <!-- Password Reset (Optional) with Generator & Strength Meter -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                        PASSWORD BARU <span class="text-slate-400 normal-case font-normal">(Opsional)</span>
                    </label>
                    <div class="flex items-center space-x-1.5">
                        <button 
                            type="button" 
                            onclick="generateStrongPassword('editPassword', 'edit')"
                            class="inline-flex items-center space-x-1 text-[10.5px] font-bold text-tealBrand hover:text-tealBrand-hover bg-teal-50 hover:bg-teal-100/80 px-2 py-0.5 rounded-lg transition-colors cursor-pointer"
                            title="Buat password acak yang kuat"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span>Generate</span>
                        </button>
                        <button 
                            type="button" 
                            onclick="copyPasswordToClipboard('editPassword', this)"
                            class="inline-flex items-center space-x-1 text-[10.5px] font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2 py-0.5 rounded-lg transition-colors cursor-pointer"
                            title="Salin password ke clipboard"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="editPassword" 
                        placeholder="Kosongkan jika tidak ingin mengubah password..." 
                        oninput="checkPasswordStrength(this.value, 'edit')"
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition font-mono"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordVisibility('editPassword', 'eyeIcon_editPassword')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                        title="Tampilkan / Sembunyikan Password"
                    >
                        <svg id="eyeIcon_editPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <!-- Password Strength Meter Bar -->
                <div id="strengthContainer_edit" class="mt-2 hidden space-y-1">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-400 font-medium">Kekuatan Password Baru:</span>
                        <span id="strengthText_edit" class="font-bold text-rose-500">Sangat Lemah</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden p-0.5">
                        <div id="strengthBar_edit_1" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_edit_2" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_edit_3" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="strengthBar_edit_4" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                    </div>
                    <p id="strengthFeedback_edit" class="text-[9.5px] text-slate-400 font-medium leading-tight pt-0.5">
                        Kombinasikan huruf besar, kecil, angka, dan simbol untuk password yang kuat.
                    </p>
                </div>
            </div>

            <!-- Role & Branch Custom Popovers -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Role -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">HAK AKSES (ROLE)</label>
                    <div class="relative" id="editRoleDropdownContainer">
                        <input type="hidden" name="role" id="editRole" value="admin_cabang" required>
                        <button 
                            type="button"
                            id="editRoleTrigger"
                            onclick="toggleModalDropdown('editRole')"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                        >
                            <span id="editRoleLabel">Admin Cabang</span>
                            <svg id="editRoleChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="editRoleMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                            @foreach($modalRoles as $mRoleKey => $mRoleLabel)
                                <button 
                                    type="button"
                                    id="editRoleOpt_{{ $mRoleKey }}"
                                    onclick="selectModalDropdown('edit', 'Role', '{{ $mRoleKey }}', '{{ $mRoleLabel }}')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $mRoleKey === 'admin_cabang' ? 'text-tealBrand font-bold bg-teal-50/60' : 'hover:bg-slate-50 font-medium text-slate-700' }}"
                                >
                                    <span>{{ $mRoleLabel }}</span>
                                    <svg id="editRoleCheck_{{ $mRoleKey }}" class="w-3.5 h-3.5 text-tealBrand {{ $mRoleKey === 'admin_cabang' ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Single Branch (Admin Cabang / Admin Dapur) -->
                <div id="editSingleBranchContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PILIH CABANG</label>
                    <div class="relative" id="editBranchDropdownContainer">
                        <input type="hidden" name="branch_id" id="editBranchId" value="">
                        <button 
                            type="button"
                            id="editBranchTrigger"
                            onclick="toggleModalDropdown('editBranch')"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                        >
                            <span id="editBranchLabel" class="truncate">-- Pilih Cabang --</span>
                            <svg id="editBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="editBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full max-h-52 overflow-y-auto bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                            @if(!auth()->user()->isKepalaCabang())
                                <button 
                                    type="button" 
                                    id="editBranchOpt_"
                                    onclick="selectModalDropdown('edit', 'Branch', '', '-- Pilih Cabang --')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                                >
                                    <span>-- Pilih Cabang --</span>
                                    <svg id="editBranchCheck_" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endif
                            @foreach($branches as $branch)
                                <button 
                                    type="button" 
                                    id="editBranchOpt_{{ $branch->id }}"
                                    onclick="selectModalDropdown('edit', 'Branch', '{{ $branch->id }}', '{{ addslashes($branch->name) }}')"
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                                >
                                    <span class="truncate">{{ $branch->name }}</span>
                                    <svg id="editBranchCheck_{{ $branch->id }}" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Multi Branch Checkbox (Khusus Kepala Cabang) -->
            <div id="editMultiBranchContainer" class="hidden space-y-1.5 pt-1">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                        WILAYAH CABANG YANG DIKELOLA (BISA PILIH LEBIH DARI 1)
                    </label>
                    <span class="text-[10px] text-tealBrand font-bold">Multi-Cabang</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/50 p-3 rounded-xl border border-slate-200 max-h-48 overflow-y-auto">
                    @foreach($branches as $branch)
                        <label class="flex items-center space-x-2.5 p-2 bg-white rounded-lg border border-slate-200/80 hover:border-tealBrand cursor-pointer transition">
                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" class="edit-branch-cb rounded border-slate-300 text-tealBrand focus:ring-tealBrand w-4 h-4 cursor-pointer">
                            <span class="text-xs font-bold text-slate-800 select-none truncate">{{ $branch->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Status Custom Popover -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">STATUS AKUN</label>
                <div class="relative" id="editStatusDropdownContainer">
                    <input type="hidden" name="status" id="editStatus" value="active" required>
                    <button 
                        type="button"
                        id="editStatusTrigger"
                        onclick="toggleModalDropdown('editStatus')"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="editStatusLabel">Aktif</span>
                        <svg id="editStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="editStatusMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn">
                        <button 
                            type="button"
                            id="editStatusOpt_active"
                            onclick="selectModalDropdown('edit', 'Status', 'active', 'Aktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors text-tealBrand font-bold bg-teal-50/60"
                        >
                            <span>Aktif</span>
                            <svg id="editStatusCheck_active" class="w-3.5 h-3.5 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                        <button 
                            type="button"
                            id="editStatusOpt_inactive"
                            onclick="selectModalDropdown('edit', 'Status', 'inactive', 'Nonaktif')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors hover:bg-slate-50 font-medium text-slate-700"
                        >
                            <span>Nonaktif</span>
                            <svg id="editStatusCheck_inactive" class="w-3.5 h-3.5 text-tealBrand hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
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
    const isUserKepalaCabang = {{ auth()->user()->isKepalaCabang() ? 'true' : 'false' }};
    const userDefaultBranchId = '{{ auth()->user()->branch_id ?? "" }}';

    const branchesMap = {
        @foreach($branches as $b)
        '{{ $b->id }}': '{{ addslashes($b->name) }}',
        @endforeach
    };

    const roleLabels = {
        'superadmin': 'Super Admin',
        'kepala_cabang': 'Kepala Cabang',
        'admin_cabang': 'Admin Cabang',
        'admin_dapur': 'Admin Dapur',
        'viewer': 'Viewer (Read-Only)'
    };

    const statusLabels = {
        'active': 'Aktif',
        'inactive': 'Nonaktif'
    };

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

    // Custom Popovers for Main Filters
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

    // Modal Custom Dropdowns
    function toggleModalDropdown(prefix) {
        const menu = document.getElementById(prefix + 'Menu');
        const chevron = document.getElementById(prefix + 'Chevron');
        if (!menu) return;

        const allModalPopovers = [
            'createRole', 'createBranch', 'createStatus',
            'editRole', 'editBranch', 'editStatus'
        ];

        allModalPopovers.forEach(p => {
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

    function selectModalDropdown(modalType, field, value, label) {
        const prefix = modalType + field;
        const input = document.getElementById(field === 'Branch' ? (modalType + 'BranchId') : prefix);
        const labelEl = document.getElementById(prefix + 'Label');
        const menu = document.getElementById(prefix + 'Menu');
        const chevron = document.getElementById(prefix + 'Chevron');

        if (input) input.value = value;
        if (labelEl) labelEl.textContent = label;

        if (menu) {
            const optionBtns = menu.querySelectorAll('button');
            optionBtns.forEach(btn => {
                btn.classList.remove('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                btn.classList.add('text-slate-700', 'font-medium');
                const svg = btn.querySelector('svg');
                if (svg) svg.classList.add('hidden');
            });

            const selectedBtn = document.getElementById(prefix + 'Opt_' + value);
            if (selectedBtn) {
                selectedBtn.classList.remove('text-slate-700', 'font-medium');
                selectedBtn.classList.add('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                const check = document.getElementById(prefix + 'Check_' + value);
                if (check) check.classList.remove('hidden');
            }

            menu.classList.add('hidden');
        }
        if (chevron) chevron.classList.remove('rotate-180');

        if (field === 'Role') {
            toggleBranchField(modalType);
        }
    }

    function setModalDropdownValue(modalType, field, value, label) {
        const prefix = modalType + field;
        const input = document.getElementById(field === 'Branch' ? (modalType + 'BranchId') : prefix);
        const labelEl = document.getElementById(prefix + 'Label');
        const menu = document.getElementById(prefix + 'Menu');

        if (input) input.value = value;
        if (labelEl) labelEl.textContent = label;

        if (menu) {
            const optionBtns = menu.querySelectorAll('button');
            optionBtns.forEach(btn => {
                btn.classList.remove('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                btn.classList.add('text-slate-700', 'font-medium');
                const svg = btn.querySelector('svg');
                if (svg) svg.classList.add('hidden');
            });

            const selectedBtn = document.getElementById(prefix + 'Opt_' + value);
            if (selectedBtn) {
                selectedBtn.classList.remove('text-slate-700', 'font-medium');
                selectedBtn.classList.add('text-tealBrand', 'font-bold', 'bg-teal-50/60');
                const check = document.getElementById(prefix + 'Check_' + value);
                if (check) check.classList.remove('hidden');
            }
        }
    }

    function toggleBranchField(type) {
        const roleInput = document.getElementById(type + 'Role');
        const role = roleInput ? roleInput.value : 'admin_cabang';
        const singleContainer = document.getElementById(type + 'SingleBranchContainer');
        const multiContainer = document.getElementById(type + 'MultiBranchContainer');

        if (role === 'kepala_cabang' || role === 'viewer') {
            if (singleContainer) singleContainer.classList.add('hidden');
            if (multiContainer) multiContainer.classList.remove('hidden');
        } else if (role === 'admin_cabang' || role === 'admin_dapur') {
            if (singleContainer) {
                singleContainer.classList.remove('hidden');
                singleContainer.style.opacity = '1';
                singleContainer.style.pointerEvents = 'auto';
            }
            if (multiContainer) multiContainer.classList.add('hidden');
            
            // If logged in as Kepala Cabang, lock to their branch
            if (isUserKepalaCabang && userDefaultBranchId) {
                const branchName = branchesMap[userDefaultBranchId] || '-- Pilih Cabang --';
                setModalDropdownValue(type, 'Branch', userDefaultBranchId, branchName);
            } else {
                const branchInput = document.getElementById(type + 'BranchId');
                const curVal = branchInput ? branchInput.value : '';
                const curLabel = curVal && branchesMap[curVal] ? branchesMap[curVal] : '-- Pilih Cabang --';
                setModalDropdownValue(type, 'Branch', curVal, curLabel);
            }
        } else {
            // superadmin
            if (singleContainer) singleContainer.classList.add('hidden');
            if (multiContainer) multiContainer.classList.add('hidden');
            setModalDropdownValue(type, 'Branch', '', 'Semua Cabang (Global)');
        }
    }

    // Global Click Listener for closing open popovers
    document.addEventListener('click', function(e) {
        const mainContainers = [
            'userRoleDropdownContainer',
            'userBranchDropdownContainer',
            'userStatusDropdownContainer'
        ];
        const isInsideMain = mainContainers.some(id => {
            const el = document.getElementById(id);
            return el && el.contains(e.target);
        });

        if (!isInsideMain) {
            ['userRoleMenu', 'userBranchMenu', 'userStatusMenu'].forEach((id, idx) => {
                const menu = document.getElementById(id);
                if (menu) menu.classList.add('hidden');
            });
            ['userRoleChevron', 'userBranchChevron', 'userStatusChevron'].forEach(id => {
                const ch = document.getElementById(id);
                if (ch) ch.classList.remove('rotate-180');
            });
        }

        const modalContainers = [
            'createRoleDropdownContainer', 'createBranchDropdownContainer', 'createStatusDropdownContainer',
            'editRoleDropdownContainer', 'editBranchDropdownContainer', 'editStatusDropdownContainer'
        ];
        const isInsideModal = modalContainers.some(id => {
            const el = document.getElementById(id);
            return el && el.contains(e.target);
        });

        if (!isInsideModal) {
            const allModalPopovers = [
                'createRole', 'createBranch', 'createStatus',
                'editRole', 'editBranch', 'editStatus'
            ];
            allModalPopovers.forEach(p => {
                const m = document.getElementById(p + 'Menu');
                const c = document.getElementById(p + 'Chevron');
                if (m) m.classList.add('hidden');
                if (c) c.classList.remove('rotate-180');
            });
        }
    });

    // Modals
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Reset password and strength meter
        const pwInput = document.getElementById('createPassword');
        if (pwInput) {
            pwInput.value = '';
            pwInput.type = 'password';
            const eyeIcon = document.getElementById('eyeIcon_createPassword');
            if (eyeIcon) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
            checkPasswordStrength('', 'create');
        }

        // Reset multi-branch checkboxes
        document.querySelectorAll('.create-branch-cb').forEach(cb => cb.checked = false);

        setModalDropdownValue('create', 'Role', 'admin_cabang', 'Admin Cabang');
        
        if (isUserKepalaCabang && userDefaultBranchId) {
            const branchName = branchesMap[userDefaultBranchId] || '-- Pilih Cabang --';
            setModalDropdownValue('create', 'Branch', userDefaultBranchId, branchName);
        } else {
            setModalDropdownValue('create', 'Branch', '', '-- Pilih Cabang --');
        }

        setModalDropdownValue('create', 'Status', 'active', 'Aktif');
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
        
        // Reset password and strength meter in edit modal
        const pwInput = document.getElementById('editPassword');
        if (pwInput) {
            pwInput.value = '';
            pwInput.type = 'password';
            const eyeIcon = document.getElementById('eyeIcon_editPassword');
            if (eyeIcon) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
            checkPasswordStrength('', 'edit');
        }

        setModalDropdownValue('edit', 'Role', user.role, roleLabels[user.role] || 'Admin Cabang');
        setModalDropdownValue('edit', 'Status', user.status, statusLabels[user.status] || 'Aktif');
        
        const branchVal = user.branch_id ? String(user.branch_id) : '';
        const branchLabel = branchVal && branchesMap[branchVal] ? branchesMap[branchVal] : '-- Pilih Cabang --';
        setModalDropdownValue('edit', 'Branch', branchVal, branchLabel);

        // Prepopulate multi-branch checkboxes
        const managedIds = (user.managed_branches || []).map(b => b.id);
        document.querySelectorAll('.edit-branch-cb').forEach(cb => {
            cb.checked = managedIds.includes(parseInt(cb.value));
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleBranchField('edit');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Generate Strong Random Password
    function generateStrongPassword(inputId, type) {
        const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lowercase = 'abcdefghijkmnopqrstuvwxyz';
        const numbers = '23456789';
        const symbols = '!@#$%&*';
        const allChars = uppercase + lowercase + numbers + symbols;

        let password = '';
        // Ensure at least 1 of each category
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += symbols.charAt(Math.floor(Math.random() * symbols.length));

        // Fill up to 12 characters
        for (let i = 4; i < 12; i++) {
            password += allChars.charAt(Math.floor(Math.random() * allChars.length));
        }

        // Shuffle password
        password = password.split('').sort(() => 0.5 - Math.random()).join('');

        const inputEl = document.getElementById(inputId);
        if (inputEl) {
            inputEl.value = password;
            // Switch to text so user can see what was generated
            inputEl.type = 'text';
            const eyeIcon = document.getElementById('eyeIcon_' + inputId);
            if (eyeIcon) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            }
            checkPasswordStrength(password, type);
            
            if (typeof IkhlasToast !== 'undefined') {
                IkhlasToast.fire({
                    icon: 'success',
                    iconColor: '#0A97B0',
                    title: 'Password kuat berhasil di-generate!'
                });
            }
        }
    }

    // Copy Password to Clipboard
    function copyPasswordToClipboard(inputId, btnElement) {
        const inputEl = document.getElementById(inputId);
        if (!inputEl || !inputEl.value) {
            if (typeof IkhlasToast !== 'undefined') {
                IkhlasToast.fire({
                    icon: 'error',
                    iconColor: '#f43f5e',
                    title: 'Password masih kosong, tidak ada yang disalin.'
                });
            }
            return;
        }

        navigator.clipboard.writeText(inputEl.value).then(() => {
            const originalHTML = btnElement ? btnElement.innerHTML : '';
            if (btnElement) {
                btnElement.innerHTML = `<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg><span class="text-emerald-700 font-bold">Tersalin!</span>`;
                setTimeout(() => {
                    btnElement.innerHTML = originalHTML;
                }, 2000);
            }

            if (typeof IkhlasToast !== 'undefined') {
                IkhlasToast.fire({
                    icon: 'success',
                    iconColor: '#0A97B0',
                    title: 'Password berhasil disalin ke clipboard!'
                });
            }
        }).catch(() => {
            inputEl.select();
            document.execCommand('copy');
            if (typeof IkhlasToast !== 'undefined') {
                IkhlasToast.fire({
                    icon: 'success',
                    iconColor: '#0A97B0',
                    title: 'Password berhasil disalin!'
                });
            }
        });
    }

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, eyeIconId) {
        const inputEl = document.getElementById(inputId);
        const eyeIcon = document.getElementById(eyeIconId);
        if (!inputEl) return;

        if (inputEl.type === 'password') {
            inputEl.type = 'text';
            if (eyeIcon) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            }
        } else {
            inputEl.type = 'password';
            if (eyeIcon) {
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    }

    // Check Password Strength
    function checkPasswordStrength(password, type) {
        const container = document.getElementById('strengthContainer_' + type);
        const textEl = document.getElementById('strengthText_' + type);
        const feedbackEl = document.getElementById('strengthFeedback_' + type);
        const bar1 = document.getElementById('strengthBar_' + type + '_1');
        const bar2 = document.getElementById('strengthBar_' + type + '_2');
        const bar3 = document.getElementById('strengthBar_' + type + '_3');
        const bar4 = document.getElementById('strengthBar_' + type + '_4');

        if (!container || !textEl) return;

        if (!password) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');

        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) score++;

        // Reset bars
        [bar1, bar2, bar3, bar4].forEach(b => {
            if (b) b.className = 'h-full rounded-full bg-slate-200 transition-all duration-300';
        });

        if (score <= 1) {
            textEl.textContent = 'Sangat Lemah';
            textEl.className = 'font-bold text-rose-500';
            if (bar1) bar1.className = 'h-full rounded-full bg-rose-500 transition-all duration-300';
            if (feedbackEl) feedbackEl.textContent = 'Terlalu pendek dan mudah ditebak. Tambahkan huruf besar & angka.';
        } else if (score === 2) {
            textEl.textContent = 'Cukup';
            textEl.className = 'font-bold text-amber-500';
            if (bar1) bar1.className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
            if (bar2) bar2.className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
            if (feedbackEl) feedbackEl.textContent = 'Cukup baik. Tambahkan simbol unik seperti @, #, $, atau % agar lebih aman.';
        } else if (score === 3) {
            textEl.textContent = 'Kuat';
            textEl.className = 'font-bold text-tealBrand';
            if (bar1) bar1.className = 'h-full rounded-full bg-tealBrand transition-all duration-300';
            if (bar2) bar2.className = 'h-full rounded-full bg-tealBrand transition-all duration-300';
            if (bar3) bar3.className = 'h-full rounded-full bg-tealBrand transition-all duration-300';
            if (feedbackEl) feedbackEl.textContent = 'Password kuat! Sangat sulit ditebak orang lain.';
        } else {
            textEl.textContent = 'Sangat Kuat';
            textEl.className = 'font-bold text-emerald-600';
            [bar1, bar2, bar3, bar4].forEach(b => {
                if (b) b.className = 'h-full rounded-full bg-emerald-500 transition-all duration-300';
            });
            if (feedbackEl) feedbackEl.textContent = 'Password sangat aman dan memenuhi standar keamanan tinggi.';
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
