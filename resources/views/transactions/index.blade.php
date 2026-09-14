@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Top Header & Branch Indicator -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Transaksi</h1>
                @if(auth()->user()->isViewer())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                        Viewer
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                @if(auth()->user()->isViewer())
                    Pemantauan resume transaksi, filter laporan data, dan pengunduhan dokumen cabang.
                @else
                    Manajemen resume transaksi, filtering laporan, bulk delete, dan export dokumen cabang.
                @endif
            </p>
        </div>

        @if(auth()->user()->canAccessAllBranches() || auth()->user()->isViewer())
            <div class="relative w-full sm:w-auto" id="trxHeaderBranchDropdownContainer">
                <form id="trxHeaderBranchForm" method="GET" action="{{ route('transactions.index') }}" class="hidden">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="branch_id" id="trxHeaderBranchInput" value="{{ $selectedBranchId }}">
                </form>

                <button 
                    type="button" 
                    id="trxHeaderBranchBtn"
                    onclick="toggleCustomPopover('trxHeaderBranchMenu', 'trxHeaderBranchChevron')"
                    class="w-full sm:w-auto flex items-center justify-between space-x-3 bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-full py-2 pl-4 pr-3.5 text-xs font-bold text-slate-700 shadow-sm transition-all duration-150 cursor-pointer"
                >
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                        <span id="trxHeaderBranchLabel">
                            @if($selectedBranchId)
                                {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                            @else
                                Semua Cabang
                            @endif
                        </span>
                    </div>
                    <svg id="trxHeaderBranchChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Popover Menu -->
                <div 
                    id="trxHeaderBranchMenu" 
                    class="hidden absolute right-0 top-full mt-1.5 w-52 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn"
                >
                    <div class="px-3 py-1.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        Pilih Cabang
                    </div>
                    
                    <button 
                        type="button" 
                        onclick="selectTrxHeaderBranch('', 'Semua Cabang')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedBranchId) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Cabang</span>
                        @if(empty($selectedBranchId))
                            <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>

                    @foreach($branches as $branch)
                        <button 
                            type="button" 
                            onclick="selectTrxHeaderBranch('{{ $branch->id }}', '{{ $branch->name }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $selectedBranchId == $branch->id ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $branch->name }}</span>
                            @if($selectedBranchId == $branch->id)
                                <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="inline-flex items-center space-x-2 bg-white border border-slate-200 rounded-full px-4 py-2 shadow-sm text-xs font-bold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                <span>{{ auth()->user()->branch->name ?? 'Cabang' }}</span>
            </div>
        @endif
    </div>

    <!-- Viewer Notice Banner -->
    @if(auth()->user()->isViewer())
        <div class="bg-teal-50/80 border border-teal-200 text-teal-950 px-4 py-3 rounded-xl flex items-center justify-between gap-3 text-xs shadow-xs">
            <div class="flex items-center space-x-2.5">
                <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0 animate-pulse"></span>
                <span><strong>Mode Viewer (Hanya Lihat):</strong> Akun Anda memiliki izin untuk memantau data transaksi seluruh cabang serta mengunduh laporan dalam format <strong>PDF</strong> dan <strong>Excel</strong>.</span>
            </div>
        </div>
    @endif

    <!-- Search & Action Buttons Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        
        <!-- Search Input with Live Auto-Search & Clear Button -->
        <div class="w-full lg:max-w-md">
            <form id="trxSearchForm" method="GET" action="{{ route('transactions.index') }}" class="relative">
                <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">

                <!-- Search Icon / Live Spinner -->
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg id="searchStaticIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg id="searchLoadingIcon" class="w-4 h-4 text-tealBrand animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                <input 
                    type="text" 
                    id="mainSearchInput"
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari ID, deskripsi, customer... (otomatis)"
                    autocomplete="off"
                    oninput="handleSearchDebounce(this)"
                    class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 shadow-sm focus:outline-none transition-all duration-150"
                >

                <button 
                    type="button" 
                    id="searchClearBtn"
                    onclick="clearSearchInput()"
                    class="{{ request('search') ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"
                    title="Hapus pencarian"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Action Buttons Layout -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <!-- Utility Buttons Group (Import, PDF, Excel) -->
            <div class="grid {{ auth()->user()->isViewer() ? 'grid-cols-2' : 'grid-cols-3' }} sm:flex items-center gap-2">
                @if(!auth()->user()->isViewer())
                    <!-- Import Excel (Hidden for Viewer) -->
                    <button 
                        type="button" 
                        onclick="openImportModal()"
                        class="px-3 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-colors cursor-pointer"
                        title="Import data transaksi dari file Excel"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="truncate">Import</span>
                    </button>
                @endif

                <!-- Export PDF -->
                <a 
                    href="{{ route('transactions.export-pdf', request()->query()) }}" 
                    class="px-3 py-2.5 bg-white hover:bg-rose-50 border border-rose-300 hover:border-rose-400 text-rose-600 text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-colors cursor-pointer"
                    title="Download Laporan PDF Ber-KOP Resmi"
                >
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="truncate">PDF</span>
                </a>

                <!-- Export Excel -->
                <a 
                    href="{{ route('transactions.export-excel', request()->query()) }}" 
                    class="px-3 py-2.5 bg-white hover:bg-emerald-50 border border-emerald-300 hover:border-emerald-400 text-emerald-600 text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-colors cursor-pointer"
                    title="Download Laporan Format Excel (.xlsx)"
                >
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="truncate">Excel</span>
                </a>
            </div>

            @if(!auth()->user()->isViewer())
                <!-- Primary Action: + Tambah Transaksi (Hidden for Viewer) -->
                <button 
                    type="button" 
                    onclick="openCreateModal()"
                    class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-colors cursor-pointer"
                >
                    <span class="text-sm leading-none">+</span>
                    <span>Tambah Transaksi</span>
                </button>
            @endif
        </div>

    </div>

    <!-- Filter & Sorting Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        
        <!-- Filter Header with Toggle for Mobile -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>FILTER & SORTING</span>
                @if(request()->hasAny(['type', 'date_from', 'date_to', 'sort', 'branch_id']))
                    <span class="w-2 h-2 rounded-full bg-tealBrand inline-block"></span>
                @endif
            </div>

            <button 
                type="button" 
                onclick="toggleMobileFilterPanel()" 
                class="sm:hidden text-xs text-tealBrand font-bold flex items-center space-x-1 cursor-pointer"
            >
                <span id="mobileFilterToggleText">Tutup Filter</span>
                <svg id="mobileFilterToggleIcon" class="w-3.5 h-3.5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <!-- Filter Grid Form (Submit otomatis via JS saat opsi dipilih) -->
        <form id="filterFormMain" method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ request('branch_id') }}">
            <input type="hidden" name="type" id="filterTypeInput" value="{{ request('type') }}">
            <input type="hidden" name="sort" id="filterSortInput" value="{{ request('sort', 'terbaru') }}">

            <!-- 1. Cabang Filter (Khusus Super Admin / Kepala Cabang / Viewer) -->
            @if(auth()->user()->canAccessAllBranches() || auth()->user()->isViewer())
                <div class="relative" id="filterBranchDropdownContainer">
                    <button 
                        type="button"
                        onclick="toggleCustomPopover('filterBranchMenu', 'filterBranchChevron')"
                        class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                    >
                        <span class="truncate">
                            @if(request('branch_id'))
                                {{ $branches->firstWhere('id', request('branch_id'))->name ?? 'Semua Cabang' }}
                            @else
                                Semua Cabang
                            @endif
                        </span>
                        <svg id="filterBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="filterBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                        <button 
                            type="button" 
                            onclick="selectFilterBranch('', 'Semua Cabang')"
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
                                onclick="selectFilterBranch('{{ $branch->id }}', '{{ $branch->name }}')"
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
            @else
                <!-- Readonly Branch Field for Admin Cabang -->
                <div class="px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-600 font-bold flex items-center justify-between">
                    <span class="truncate">{{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Terkunci</span>
                </div>
            @endif

            <!-- 2. Jenis Transaksi Custom Dropdown -->
            <div class="relative" id="filterTypeDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleCustomPopover('filterTypeMenu', 'filterTypeChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        {{ request('type') ?: 'Semua Jenis' }}
                    </span>
                    <svg id="filterTypeChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="filterTypeMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $types = ['Semua Jenis' => '', 'Penjualan Tunai' => 'Penjualan Tunai', 'Penjualan Kredit' => 'Penjualan Kredit', 'Retur Penjualan' => 'Retur Penjualan', 'Transfer Cabang' => 'Transfer Cabang'];
                    @endphp
                    @foreach($types as $tLabel => $tVal)
                        <button 
                            type="button" 
                            onclick="selectFilterType('{{ $tVal }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('type') === $tVal ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $tLabel }}</span>
                            @if(request('type') === $tVal)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Tanggal Dari (Flatpickr) -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="filterDateFrom"
                    name="date_from" 
                    value="{{ request('date_from') }}" 
                    placeholder="Tanggal Dari"
                    class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:border-tealBrand cursor-pointer"
                >
            </div>

            <!-- 4. Tanggal Sampai (Flatpickr) -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="filterDateTo"
                    name="date_to" 
                    value="{{ request('date_to') }}" 
                    placeholder="Tanggal Sampai"
                    class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:border-tealBrand cursor-pointer"
                >
            </div>

            <!-- 5. Sorting Custom Dropdown -->
            <div class="relative sm:col-span-2 lg:col-span-4" id="filterSortDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleCustomPopover('filterSortMenu', 'filterSortChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if(request('sort') === 'terlama')
                            Urutan: Terlama
                        @elseif(request('sort') === 'terbanyak')
                            Urutan: Transaksi Terbanyak (Nominal)
                        @elseif(request('sort') === 'tersedikit')
                            Urutan: Transaksi Paling Sedikit
                        @else
                            Urutan: Terbaru
                        @endif
                    </span>
                    <svg id="filterSortChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="filterSortMenu" class="hidden absolute right-0 top-full mt-1.5 w-full min-w-[210px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    @php
                        $sortOptions = [
                            'terbaru' => 'Terbaru',
                            'terlama' => 'Terlama',
                            'terbanyak' => 'Transaksi Terbanyak (Nominal)',
                            'tersedikit' => 'Transaksi Paling Sedikit',
                        ];
                    @endphp
                    @foreach($sortOptions as $sKey => $sLabel)
                        <button 
                            type="button" 
                            onclick="selectFilterSort('{{ $sKey }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('sort', 'terbaru') === $sKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $sLabel }}</span>
                            @if(request('sort', 'terbaru') === $sKey)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </form>

    </div>

    @if(!auth()->user()->isViewer())
        <!-- Bulk Action Toolbar (Muncul saat ada checkbox dicentang) -->
        <div id="bulkTrxToolbar" class="hidden bg-slate-900 text-white p-3.5 rounded-xl shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fadeIn">
            <div class="flex items-center space-x-2 text-xs font-bold px-2">
                <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                <span><span id="selectedTrxCount">0</span> transaksi dipilih</span>
            </div>

            <div class="flex items-center space-x-2">
                <form id="bulkDeleteForm" action="{{ route('transactions.bulk-delete') }}" method="POST" class="inline">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                    <button 
                        type="button" 
                        onclick="confirmCustomAction({ title: 'Pindahkan Transaksi Terpilih?', text: 'Semua transaksi yang dipilih akan dipindahkan ke tempat sampah.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Pindahkan ke Sampah', form: document.getElementById('bulkDeleteForm') });" 
                        class="w-full sm:w-auto px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center space-x-1.5 shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus Terpilih (Pindah ke Sampah)</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Summary Info Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs px-1">
        <div class="text-slate-500 font-medium">
            Menampilkan <strong class="text-slate-800 font-bold">{{ $transactions->total() }}</strong> transaksi
            @if(request()->hasAny(['search', 'type', 'date_from', 'date_to', 'sort', 'branch_id']))
                &bull; <a href="{{ route('transactions.index') }}" class="text-tealBrand hover:underline font-bold">Reset Filter</a>
            @endif
        </div>
        <div class="text-xs sm:text-sm font-bold text-slate-700">
            Total : <span class="text-emerald-600 font-extrabold font-sans">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Data Container: Mobile Card List + Desktop Table -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm overflow-hidden">
        
        <!-- Table & List Header -->
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Transaksi</h3>
                @if(!auth()->user()->isViewer())
                    <!-- Master Checkbox for Mobile -->
                    <label class="md:hidden flex items-center space-x-1.5 text-[11px] font-bold text-slate-500 cursor-pointer">
                        <input type="checkbox" id="selectAllTrxMobile" onchange="toggleSelectAllTrx(this)" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                        <span>Pilih Semua</span>
                    </label>
                @endif
            </div>
            <span class="text-xs text-slate-400 font-medium font-mono">Page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</span>
        </div>

        <!-- 1. Mobile Cards View (< md) -->
        <div class="block md:hidden space-y-3">
            @forelse($transactions as $trx)
                <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200/90 rounded-xl p-3.5 space-y-2.5 transition-all">
                    
                    <!-- Card Top Row: Checkbox, Code, Type Badge & Date -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            @if(!auth()->user()->isViewer() && (auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id))
                                <input type="checkbox" name="trx_ids[]" value="{{ $trx->id }}" onchange="updateTrxSelection()" class="trx-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                            @endif
                            <span class="font-mono text-[11px] font-bold text-slate-700 bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                                {{ $trx->code }}
                            </span>
                        </div>
                        
                        <span class="text-[10px] text-slate-400 font-medium font-mono">
                            {{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}
                        </span>
                    </div>

                    <!-- Card Body: Customer, Notes, Branch & Type -->
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-bold text-xs text-slate-900 leading-tight">
                                {{ $trx->customer_name }}
                            </div>
                            <!-- Type Badge -->
                            <div>
                                @if($trx->type === 'Penjualan Tunai')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tunai
                                    </span>
                                @elseif($trx->type === 'Penjualan Kredit')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                        Kredit
                                    </span>
                                @elseif($trx->type === 'Retur Penjualan')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Retur
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Transfer
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="text-[11px] text-slate-500">
                            {{ $trx->notes ?: 'Tidak ada deskripsi' }}
                        </div>
                        
                        <div class="text-[10px] font-semibold text-slate-400 flex items-center space-x-1">
                            <span>Cabang:</span>
                            <span class="text-slate-700 font-bold">{{ $trx->branch->name ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Card Bottom: QTY, Nominal & Action Buttons -->
                    <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                        <div class="text-xs">
                            <span class="text-[10px] text-slate-400 font-bold mr-1">QTY: {{ $trx->qty }}</span>
                            <span class="font-extrabold {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div>
                            @if(auth()->user()->isViewer())
                                <button 
                                    type="button" 
                                    onclick="openDetailModal({{ json_encode($trx) }})"
                                    class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <span>Detail</span>
                                </button>
                            @elseif(auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id)
                                <div class="flex items-center space-x-2">
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode($trx) }})"
                                        class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Transaksi?', text: 'Transaksi {{ $trx->code }} akan dipindahkan ke tempat sampah.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Pindahkan', form: this });" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus Transaksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Tidak ada transaksi yang sesuai dengan filter.
                </div>
            @endforelse
        </div>

        <!-- 2. Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        @if(!auth()->user()->isViewer())
                            <th class="py-3 px-3 w-8 text-center">
                                <input type="checkbox" id="selectAllTrx" onchange="toggleSelectAllTrx(this)" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                            </th>
                        @endif
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Cabang</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Deskripsi</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3 text-center">QTY</th>
                        <th class="py-3 px-3 text-right">Jumlah</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            @if(!auth()->user()->isViewer())
                                <!-- Checkbox Row -->
                                <td class="py-3 px-3 text-center">
                                    @if(auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id)
                                        <input type="checkbox" name="trx_ids[]" value="{{ $trx->id }}" onchange="updateTrxSelection()" class="trx-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                                    @else
                                        <input type="checkbox" disabled class="rounded border-slate-200 text-slate-300 cursor-not-allowed opacity-40">
                                    @endif
                                </td>
                            @endif

                            <!-- ID -->
                            <td class="py-3 px-3 font-mono text-slate-500 font-bold">{{ $trx->code }}</td>
                            
                            <!-- Tanggal -->
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">
                                {{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}
                            </td>
                            
                            <!-- Cabang -->
                            <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">
                                {{ $trx->branch->name ?? '-' }}
                            </td>
                            
                            <!-- Jenis (Badge) -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($trx->type === 'Penjualan Tunai')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        Penjualan Tunai
                                    </span>
                                @elseif($trx->type === 'Penjualan Kredit')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">
                                        Penjualan Kredit
                                    </span>
                                @elseif($trx->type === 'Retur Penjualan')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                        Retur Penjualan
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        Transfer Cabang
                                    </span>
                                @endif
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3 px-3 text-slate-600 max-w-xs truncate" title="{{ $trx->notes }}">
                                {{ $trx->notes ?: '-' }}
                            </td>

                            <!-- Customer -->
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap font-medium">
                                {{ $trx->customer_name }}
                            </td>

                            <!-- Qty -->
                            <td class="py-3 px-3 text-center text-slate-700 font-bold font-mono">
                                {{ $trx->qty }}
                            </td>

                            <!-- Jumlah -->
                            <td class="py-3 px-3 text-right font-extrabold whitespace-nowrap {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-3 text-center">
                                @if(auth()->user()->isViewer())
                                    <button 
                                        type="button" 
                                        onclick="openDetailModal({{ json_encode($trx) }})"
                                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors inline-flex items-center space-x-1 cursor-pointer"
                                        title="Lihat Detail Transaksi"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Detail</span>
                                    </button>
                                @elseif(auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id)
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button 
                                            type="button" 
                                            onclick="openEditModal({{ json_encode($trx) }})"
                                            class="p-1 text-slate-400 hover:text-slate-800 transition-colors cursor-pointer"
                                            title="Edit Transaksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Transaksi?', text: 'Transaksi {{ $trx->code }} akan dipindahkan ke tempat sampah.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Pindahkan', form: this });" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                                title="Hapus Transaksi (Pindah ke Sampah)"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-[10px] italic">Read-only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isViewer() ? 9 : 10 }}" class="py-12 text-center text-slate-400 font-medium">
                                Tidak ada transaksi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="pt-5 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

</div>

<!-- Modal Detail Transaksi (Untuk Viewer & Semua User) -->
<div id="detailModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-[480px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Rincian Transaksi <span id="detailCodeDisplay" class="font-mono text-tealBrand font-bold"></span></h3>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-2.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">ID KODE</span>
                    <span id="detailCode" class="font-mono font-bold text-slate-900"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">TANGGAL</span>
                    <span id="detailDate" class="font-bold text-slate-800"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">CABANG</span>
                    <span id="detailBranch" class="font-bold text-slate-800"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">JENIS TRANSAKSI</span>
                    <div id="detailType"></div>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">CUSTOMER / TUJUAN</span>
                    <span id="detailCustomer" class="font-bold text-slate-900"></span>
                </div>
                <div class="pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px] block mb-1">DESKRIPSI / CATATAN</span>
                    <span id="detailNotes" class="text-slate-700 leading-relaxed"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">KUANTITAS (QTY)</span>
                    <span id="detailQty" class="font-mono font-bold text-slate-900"></span>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-slate-500 font-bold uppercase text-[11px]">TOTAL JUMLAH</span>
                    <span id="detailAmount" class="font-extrabold text-base"></span>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" onclick="closeDetailModal()" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer text-center">
                    Tutup Rincian
                </button>
            </div>
        </div>
    </div>
</div>

@if(!auth()->user()->isViewer())
    <!-- Modal Tambah Transaksi -->
    <div id="createModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-[480px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
            
            <!-- Header -->
            <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
                <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Input Transaksi Baru</h3>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Form Body -->
            <form action="{{ route('transactions.store') }}" method="POST" class="p-5 space-y-3.5 overflow-y-auto">
                @csrf
                <input type="hidden" name="code" value="{{ $nextCode }}">

                <!-- Baris 1: Tanggal & Cabang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">TANGGAL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="text" name="transaction_date" id="createDateInput" value="{{ date('Y-m-d') }}" required class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">CABANG</label>
                        @if(auth()->user()->canAccessAllBranches())
                            <div class="relative">
                                <select name="branch_id" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            <input type="text" disabled value="{{ auth()->user()->branch->name ?? 'Cabang' }}" class="w-full px-3.5 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-xl font-bold text-slate-600 cursor-not-allowed">
                        @endif
                    </div>
                </div>

                <!-- Baris 2: Jenis Transaksi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">JENIS TRANSAKSI</label>
                    <div class="relative">
                        <select name="type" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="Penjualan Tunai">Penjualan Tunai</option>
                            <option value="Penjualan Kredit">Penjualan Kredit</option>
                            <option value="Retur Penjualan">Retur Penjualan</option>
                            <option value="Transfer Cabang">Transfer Cabang</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Deskripsi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">DESKRIPSI</label>
                    <input type="text" name="notes" placeholder="Deskripsi Transaksi" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>

                <!-- Baris 4: Nama Customer / Vendor -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA CUSTOMER / VENDOR</label>
                    <input type="text" name="customer_name" required placeholder="PT / CV / Toko" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>

                <!-- Baris 5: Jumlah (Rp) & Qty -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">JUMLAH (RP)</label>
                        <input type="text" inputmode="numeric" name="amount" id="createAmount" value="0" required oninput="formatRupiahInput(this)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">QTY</label>
                        <input type="number" name="qty" min="1" value="1" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                    </div>
                </div>

                <!-- Baris 6: Dibuat Oleh -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">DIBUAT OLEH</label>
                    <input type="text" disabled value="{{ auth()->user()->name }}" class="w-full px-3.5 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-semibold cursor-not-allowed">
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                    <button type="button" onclick="closeCreateModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Transaksi -->
    <div id="editModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-[480px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
            
            <!-- Header -->
            <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
                <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Edit Transaksi <span id="editCodeDisplay" class="font-mono text-tealBrand font-bold"></span></h3>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="p-5 space-y-3.5 overflow-y-auto">
                @csrf
                @method('PUT')

                <!-- Baris 1: Tanggal & Cabang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">TANGGAL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="text" name="transaction_date" id="editDate" required class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">CABANG</label>
                        @if(auth()->user()->canAccessAllBranches())
                            <div class="relative">
                                <select name="branch_id" id="editBranchId" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        @else
                            <input type="text" id="editBranchName" disabled class="w-full px-3.5 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-xl font-bold text-slate-600 cursor-not-allowed">
                        @endif
                    </div>
                </div>

                <!-- Baris 2: Jenis Transaksi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">JENIS TRANSAKSI</label>
                    <div class="relative">
                        <select name="type" id="editType" required class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition cursor-pointer">
                            <option value="Penjualan Tunai">Penjualan Tunai</option>
                            <option value="Penjualan Kredit">Penjualan Kredit</option>
                            <option value="Retur Penjualan">Retur Penjualan</option>
                            <option value="Transfer Cabang">Transfer Cabang</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Deskripsi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">DESKRIPSI</label>
                    <input type="text" name="notes" id="editNotes" placeholder="Deskripsi Transaksi" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>

                <!-- Baris 4: Nama Customer / Vendor -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA CUSTOMER / VENDOR</label>
                    <input type="text" name="customer_name" id="editCustomer" required placeholder="PT / CV / Toko" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 font-medium focus:outline-none focus:border-tealBrand transition">
                </div>

                <!-- Baris 5: Jumlah (Rp) & Qty -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">JUMLAH (RP)</label>
                        <input type="text" inputmode="numeric" name="amount" id="editAmount" required oninput="formatRupiahInput(this)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">QTY</label>
                        <input type="number" name="qty" id="editQty" min="1" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition">
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5 shrink-0">
                    <button type="button" onclick="closeEditModal()" class="w-full py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors text-center cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="w-full py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl transition-colors text-center cursor-pointer shadow-sm">
                        Perbarui Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div id="importModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
            <!-- Header -->
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
                <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Import dari Excel</h3>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('transactions.import-excel') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                @csrf

                <!-- Amber Warning Notice -->
                <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-xl flex items-start space-x-3 text-amber-900">
                    <div class="shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-xs leading-relaxed">
                        <strong class="font-bold">Pastikan format Excel sesuai template</strong> yang disediakan kepala cabang. Kolom: <span class="font-medium text-amber-950">Tanggal, Cabang, Jenis, Deskripsi, Customer, Jumlah, Qty</span>
                    </div>
                </div>

                <!-- Dropzone -->
                <div id="dropzoneContainer" onclick="document.getElementById('importFileInput').click()" class="border-2 border-dashed border-slate-200 hover:border-tealBrand rounded-2xl p-6 text-center transition-all bg-slate-50/60 hover:bg-teal-50/20 cursor-pointer group">
                    <input type="file" id="importFileInput" name="file" required accept=".xlsx,.xls,.csv" class="hidden" onchange="handleFileSelect(this)">
                    
                    <div class="w-12 h-12 mx-auto mb-3 bg-emerald-100 text-emerald-600 group-hover:scale-105 rounded-full flex items-center justify-center transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div class="text-xs font-bold text-slate-800 mb-1">
                        Pilih File Excel / CSV
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium mb-1">
                        Drag & drop berkas ke sini, atau klik untuk memilih (.xlsx, .xls, .csv)
                    </div>
                    <div id="selectedFileName" class="hidden mt-2.5 inline-flex items-center px-3 py-1 rounded-md bg-emerald-100 text-emerald-800 text-xs font-bold">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="fileNameText">file.xlsx</span>
                    </div>
                </div>

                <!-- Download Template Link -->
                <div class="text-center pt-1">
                    <a href="{{ route('transactions.download-template') }}" class="inline-flex items-center text-xs font-bold text-tealBrand hover:text-tealBrand-hover transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Template Excel
                    </a>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2.5 shrink-0">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    // Live Search Debounce
    let searchDebounceTimer = null;
    function handleSearchDebounce(input) {
        const clearBtn = document.getElementById('searchClearBtn');
        const staticIcon = document.getElementById('searchStaticIcon');
        const loadingIcon = document.getElementById('searchLoadingIcon');

        if (input.value.trim().length > 0) {
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else {
            if (clearBtn) clearBtn.classList.add('hidden');
        }

        clearTimeout(searchDebounceTimer);
        
        if (staticIcon && loadingIcon) {
            staticIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
        }

        searchDebounceTimer = setTimeout(() => {
            document.getElementById('trxSearchForm').submit();
        }, 450);
    }

    function clearSearchInput() {
        const input = document.getElementById('mainSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('searchClearBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        document.getElementById('trxSearchForm').submit();
    }

    // Flatpickr Instances
    let editFlatpickr = null;
    let createFlatpickr = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Filter Date From
        flatpickr("#filterDateFrom", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: false,
            onChange: function(selectedDates, dateStr) {
                document.getElementById('filterFormMain').submit();
            }
        });

        // Filter Date To
        flatpickr("#filterDateTo", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: false,
            onChange: function(selectedDates, dateStr) {
                document.getElementById('filterFormMain').submit();
            }
        });

        // Create Modal Date (if exists)
        if (document.getElementById('createDateInput')) {
            createFlatpickr = flatpickr("#createDateInput", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                defaultDate: "{{ date('Y-m-d') }}",
                allowInput: false
            });
        }

        // Edit Modal Date (if exists)
        if (document.getElementById('editDate')) {
            editFlatpickr = flatpickr("#editDate", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                allowInput: false
            });
        }

        // Shortcut '/' to focus search input
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const searchInput = document.getElementById('mainSearchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    });

    function toggleMobileFilterPanel() {
        const form = document.getElementById('filterFormMain');
        const text = document.getElementById('mobileFilterToggleText');
        const icon = document.getElementById('mobileFilterToggleIcon');
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

    function formatRupiahInput(input) {
        let raw = input.value.replace(/[^0-9]/g, '');
        if (!raw) {
            input.value = '';
            return;
        }
        input.value = new Intl.NumberFormat('id-ID').format(raw);
    }

    // Detail Modal Functions
    function openDetailModal(trx) {
        document.getElementById('detailCodeDisplay').textContent = trx.code ? `(${trx.code})` : '';
        document.getElementById('detailCode').textContent = trx.code || '-';
        
        let dateStr = trx.transaction_date ? trx.transaction_date.substring(0, 10) : '-';
        document.getElementById('detailDate').textContent = dateStr;
        
        document.getElementById('detailBranch').textContent = (trx.branch && trx.branch.name) ? trx.branch.name : '-';
        
        let typeHtml = `<span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">${trx.type}</span>`;
        if (trx.type === 'Penjualan Tunai') {
            typeHtml = `<span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Penjualan Tunai</span>`;
        } else if (trx.type === 'Penjualan Kredit') {
            typeHtml = `<span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">Penjualan Kredit</span>`;
        } else if (trx.type === 'Retur Penjualan') {
            typeHtml = `<span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Retur Penjualan</span>`;
        } else {
            typeHtml = `<span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">${trx.type}</span>`;
        }
        document.getElementById('detailType').innerHTML = typeHtml;
        
        document.getElementById('detailCustomer').textContent = trx.customer_name || '-';
        document.getElementById('detailNotes').textContent = trx.notes || '-';
        document.getElementById('detailQty').textContent = trx.qty || '1';
        
        let amountNum = parseFloat(trx.amount) || 0;
        let formattedAmount = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Math.abs(amountNum));
        let amountElem = document.getElementById('detailAmount');
        if (amountNum < 0) {
            amountElem.textContent = '-' + formattedAmount;
            amountElem.className = 'font-extrabold text-base text-rose-600';
        } else {
            amountElem.textContent = formattedAmount;
            amountElem.className = 'font-extrabold text-base text-slate-900';
        }

        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openCreateModal() {
        const modal = document.getElementById('createModal');
        if (!modal) return;
        const amountInput = document.getElementById('createAmount');
        if (amountInput && (!amountInput.value || amountInput.value === '0')) {
            amountInput.value = '0';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal(trx) {
        const modal = document.getElementById('editModal');
        if (!modal) return;
        const form = document.getElementById('editForm');
        
        form.action = `/transactions/${trx.id}`;
        document.getElementById('editCodeDisplay').textContent = `(${trx.code})`;
        
        if (editFlatpickr && trx.transaction_date) {
            editFlatpickr.setDate(trx.transaction_date.substring(0, 10));
        } else if (document.getElementById('editDate')) {
            document.getElementById('editDate').value = trx.transaction_date ? trx.transaction_date.substring(0, 10) : '';
        }

        document.getElementById('editType').value = trx.type;
        document.getElementById('editCustomer').value = trx.customer_name;
        document.getElementById('editQty').value = trx.qty;
        
        const rawAmount = Math.abs(Math.round(trx.amount || 0));
        document.getElementById('editAmount').value = new Intl.NumberFormat('id-ID').format(rawAmount);
        
        document.getElementById('editNotes').value = trx.notes || '';

        const branchSelect = document.getElementById('editBranchId');
        if (branchSelect) {
            branchSelect.value = trx.branch_id;
        }
        const branchNameInput = document.getElementById('editBranchName');
        if (branchNameInput && trx.branch) {
            branchNameInput.value = trx.branch.name;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openImportModal() {
        const modal = document.getElementById('importModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeImportModal() {
        const modal = document.getElementById('importModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            document.getElementById('fileNameText').textContent = fileName;
            document.getElementById('selectedFileName').classList.remove('hidden');
        }
    }

    // Bulk Action Checkboxes Sync
    function toggleSelectAllTrx(master) {
        const checkboxes = document.querySelectorAll('.trx-item-checkbox:not(:disabled)');
        checkboxes.forEach(cb => cb.checked = master.checked);
        
        const masterDesktop = document.getElementById('selectAllTrx');
        const masterMobile = document.getElementById('selectAllTrxMobile');
        if (masterDesktop) masterDesktop.checked = master.checked;
        if (masterMobile) masterMobile.checked = master.checked;

        updateTrxSelection();
    }

    function updateTrxSelection() {
        const checkboxes = document.querySelectorAll('.trx-item-checkbox:checked');
        const count = checkboxes.length;
        const toolbar = document.getElementById('bulkTrxToolbar');
        if (!toolbar) return;
        const countText = document.getElementById('selectedTrxCount');
        const deleteContainer = document.getElementById('bulkDeleteInputs');

        if (count > 0) {
            countText.textContent = count;
            toolbar.classList.remove('hidden');

            deleteContainer.innerHTML = '';
            const selectedIds = new Set();
            checkboxes.forEach(cb => {
                if (!selectedIds.has(cb.value)) {
                    selectedIds.add(cb.value);
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    deleteContainer.appendChild(input);
                }
            });
            countText.textContent = selectedIds.size;
        } else {
            toolbar.classList.add('hidden');
            deleteContainer.innerHTML = '';
            const masterDesktop = document.getElementById('selectAllTrx');
            const masterMobile = document.getElementById('selectAllTrxMobile');
            if (masterDesktop) masterDesktop.checked = false;
            if (masterMobile) masterMobile.checked = false;
        }
    }

    // Drag & Drop event listeners
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('dropzoneContainer');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-tealBrand', 'bg-teal-50/40');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-tealBrand', 'bg-teal-50/40');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    const input = document.getElementById('importFileInput');
                    input.files = files;
                    handleFileSelect(input);
                }
            });
        }
    });

    // Custom Popover Dropdown Handlers
    function toggleCustomPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const allPopovers = ['trxHeaderBranchMenu', 'filterBranchMenu', 'filterTypeMenu', 'filterSortMenu'];
        const allChevrons = ['trxHeaderBranchChevron', 'filterBranchChevron', 'filterTypeChevron', 'filterSortChevron'];

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

    function selectTrxHeaderBranch(branchId, branchName) {
        document.getElementById('trxHeaderBranchInput').value = branchId;
        document.getElementById('trxHeaderBranchLabel').textContent = branchName;
        document.getElementById('trxHeaderBranchForm').submit();
    }

    function selectFilterBranch(branchId, branchName) {
        document.getElementById('filterBranchInput').value = branchId;
        document.getElementById('filterFormMain').submit();
    }

    function selectFilterType(type) {
        document.getElementById('filterTypeInput').value = type;
        document.getElementById('filterFormMain').submit();
    }

    function selectFilterSort(sort) {
        document.getElementById('filterSortInput').value = sort;
        document.getElementById('filterFormMain').submit();
    }

    document.addEventListener('click', function(e) {
        const containers = [
            'trxHeaderBranchDropdownContainer',
            'filterBranchDropdownContainer',
            'filterTypeDropdownContainer',
            'filterSortDropdownContainer'
        ];
        const isInsideAny = containers.some(id => {
            const el = document.getElementById(id);
            return el && el.contains(e.target);
        });

        if (!isInsideAny) {
            ['trxHeaderBranchMenu', 'filterBranchMenu', 'filterTypeMenu', 'filterSortMenu'].forEach((id, idx) => {
                const menu = document.getElementById(id);
                if (menu) menu.classList.add('hidden');
            });
            ['trxHeaderBranchChevron', 'filterBranchChevron', 'filterTypeChevron', 'filterSortChevron'].forEach(id => {
                const ch = document.getElementById(id);
                if (ch) ch.classList.remove('rotate-180');
            });
        }
    });
</script>
@endpush
@endsection
