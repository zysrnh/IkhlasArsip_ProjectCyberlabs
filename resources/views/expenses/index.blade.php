@extends('layouts.app')

@section('title', 'Belanja Harian Cabang')

@section('content')
<div class="space-y-6 animate-fadeIn pb-6">

    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Belanja Harian Cabang</h1>
                @if(auth()->user()->isViewer())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                        Viewer
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Pencatatan pengeluaran belanja bahan baku pasar, operasional non bahan baku, dan belanja pribadi cabang.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <!-- Tombol Export PDF Sesuai Filter -->
            <a 
                href="{{ route('daily-expenses.export-pdf', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-all duration-150 cursor-pointer"
                title="Unduh Laporan Belanja Harian PDF Sesuai Filter"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export PDF</span>
            </a>

            @if(!auth()->user()->isViewer())
                <button 
                    type="button" 
                    onclick="toggleExpenseInputModal()" 
                    class="px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-2 transition-all duration-150 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Catat Belanja Harian</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 4 Primary Stat Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Belanja Keseluruhan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                    Grand Total Belanja
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-2xl font-black text-rose-600 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['grand_total_expense'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['grand_total_expense'], 0, ',', '.') }}
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Total Catatan</span>
                <span class="font-bold text-slate-700">{{ number_format($stats['total_records']) }} hari</span>
            </div>
        </div>

        <!-- Belanja Bahan Baku -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-tealBrand transition-colors">
                    1. Belanja Bahan Baku
                </span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-tealBrand flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-xl font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_raw'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_raw'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                Daging, ayam, beras, sayuran, bumbu
            </div>
        </div>

        <!-- Belanja Non Bahan Baku -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition-colors">
                    2. Belanja Non Bahan Baku
                </span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-xl font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_non_raw'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_non_raw'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                Gas LPG, plastik, sabun, listrik
            </div>
        </div>

        <!-- Belanja Pribadi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-purple-600 transition-colors">
                    3. Belanja Pribadi
                </span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-xl font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_personal'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_personal'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                Kasbon, keperluan pribadi/karyawan
            </div>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3.5">
        <form action="{{ route('daily-expenses.index') }}" method="GET" id="expenseFilterForm" class="space-y-3">
            <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ $selectedBranchId }}">
            <input type="hidden" name="sort" id="filterSortInput" value="{{ request('sort', 'terbaru') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Search Bar (Col 4) -->
                <div class="lg:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="expenseSearchInput"
                        value="{{ request('search') }}" 
                        placeholder="Cari rincian belanja, PIC, cabang..." 
                        class="w-full pl-10 pr-8 py-2.5 bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 shadow-sm focus:outline-none transition"
                    >
                    @if(request('search'))
                        <a href="{{ route('daily-expenses.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>

                <!-- Filter Cabang (Col 3) -->
                <div class="lg:col-span-3 relative" id="expenseBranchWrapper">
                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                        <button 
                            type="button" 
                            onclick="toggleExpenseDropdown('expenseBranchMenu')" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                        >
                            <div class="flex items-center space-x-2 truncate">
                                <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                                <span id="selectedBranchLabel" class="truncate">
                                    @if(!empty($selectedBranchId))
                                        {{ $branches->firstWhere('id', $selectedBranchId)?->name ?? 'Semua Cabang' }}
                                    @else
                                        Semua Cabang
                                    @endif
                                </span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="expenseBranchMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 max-h-56 overflow-y-auto animate-fadeIn">
                            <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Pilih Cabang</div>
                            <button 
                                type="button" 
                                onclick="selectExpenseBranchOption('', 'Semua Cabang')" 
                                class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedBranchId) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                            >
                                <span>Semua Cabang</span>
                                @if(empty($selectedBranchId))
                                    <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                            @foreach($branches as $b)
                                <button 
                                    type="button" 
                                    onclick="selectExpenseBranchOption('{{ $b->id }}', '{{ $b->name }}')" 
                                    class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $selectedBranchId == $b->id ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                                >
                                    <span>{{ $b->name }}</span>
                                    @if($selectedBranchId == $b->id)
                                        <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold flex items-center justify-between">
                            <span class="truncate">{{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
                            <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider bg-slate-200/70 px-1.5 py-0.5 rounded">Terkunci</span>
                        </div>
                    @endif
                </div>

                <!-- Sorting Dropdown (Col 3) -->
                <div class="lg:col-span-3 relative" id="expenseSortWrapper">
                    <button 
                        type="button" 
                        onclick="toggleExpenseDropdown('expenseSortMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                    >
                        <span class="truncate">
                            @if(request('sort') === 'terlama')
                                Urutan: Terlama
                            @elseif(request('sort') === 'terbesar')
                                Urutan: Belanja Terbesar
                            @elseif(request('sort') === 'terkecil')
                                Urutan: Belanja Terkecil
                            @else
                                Urutan: Terbaru
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="expenseSortMenu" class="hidden absolute right-0 mt-1.5 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 animate-fadeIn">
                        <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Urutan Belanja</div>
                        @php
                            $expSortOptions = [
                                'terbaru' => 'Terbaru',
                                'terlama' => 'Terlama',
                                'terbesar' => 'Belanja Terbesar',
                                'terkecil' => 'Belanja Terkecil',
                            ];
                        @endphp
                        @foreach($expSortOptions as $sKey => $sLabel)
                            <button 
                                type="button" 
                                onclick="selectExpenseSortOption('{{ $sKey }}')" 
                                class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('sort', 'terbaru') === $sKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                            >
                                <span>{{ $sLabel }}</span>
                                @if(request('sort', 'terbaru') === $sKey)
                                    <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Reset Filter (Col 2) -->
                <div class="lg:col-span-2">
                    @if(request()->anyFilled(['search', 'branch_id', 'date_from', 'date_to', 'sort']))
                        <a href="{{ route('daily-expenses.index') }}" class="w-full px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Reset</span>
                        </a>
                    @else
                        <button type="submit" class="w-full px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span>Filter</span>
                        </button>
                    @endif
                </div>

            </div>

            <!-- Row 2: Date Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center pt-1 border-t border-slate-100">
                <div class="lg:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="filterExpenseDateFrom"
                        name="date_from" 
                        value="{{ request('date_from') }}" 
                        placeholder="Tanggal Dari"
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-tealBrand cursor-pointer shadow-xs"
                    >
                </div>

                <div class="lg:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="filterExpenseDateTo"
                        name="date_to" 
                        value="{{ request('date_to') }}" 
                        placeholder="Tanggal Sampai"
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-tealBrand cursor-pointer shadow-xs"
                    >
                </div>

                <div class="lg:col-span-2">
                    <button type="submit" class="w-full px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Section (Desktop View) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 rounded-l-lg">Tanggal</th>
                        <th class="py-3.5 px-4">Cabang</th>
                        <th class="py-3.5 px-4 text-right">1. Bahan Baku</th>
                        <th class="py-3.5 px-4 text-right">2. Non Bahan Baku</th>
                        <th class="py-3.5 px-4 text-right">3. Belanja Pribadi</th>
                        <th class="py-3.5 px-4 text-right">Total Belanja</th>
                        <th class="py-3.5 px-4">Rincian / Catatan</th>
                        <th class="py-3.5 px-4">Penginput</th>
                        <th class="py-3.5 px-4 text-center rounded-r-lg w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $exp->report_date->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $exp->report_date->translatedFormat('l') }}</div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-800">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $exp->branch->name ?? '-' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right font-bold text-slate-900 font-sans whitespace-nowrap">
                                Rp {{ number_format($exp->expense_raw_material, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-bold text-slate-900 font-sans whitespace-nowrap">
                                Rp {{ number_format($exp->expense_non_raw_material, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-bold text-purple-700 font-sans whitespace-nowrap">
                                Rp {{ number_format($exp->expense_personal, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-black text-rose-600 font-sans text-xs whitespace-nowrap bg-rose-50/20">
                                Rp {{ number_format($exp->total_expense, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-[11px] text-slate-600 max-w-xs truncate" title="{{ $exp->expense_notes }}">
                                {{ $exp->expense_notes ?: '-' }}
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-slate-500">
                                <div class="font-bold text-slate-800">{{ $exp->user->name ?? '-' }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <a 
                                    href="{{ route('kitchen-reports.show', $exp->id) }}" 
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition"
                                >
                                    Laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="font-bold text-slate-700 text-sm">Belum ada catatan belanja harian.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Catat Belanja Harian" untuk memulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/60">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Cards View (< md) -->
    <div class="md:hidden space-y-3">
        @forelse($expenses as $exp)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <div class="font-extrabold text-sm text-slate-900">{{ $exp->report_date->translatedFormat('d M Y') }}</div>
                        <div class="text-[10px] text-slate-400">{{ $exp->report_date->translatedFormat('l') }}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $exp->branch->name ?? '-' }}
                    </span>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">1. Bahan Baku:</span>
                        <span class="font-bold text-slate-800 font-sans">Rp {{ number_format($exp->expense_raw_material, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">2. Non Bahan Baku:</span>
                        <span class="font-bold text-slate-800 font-sans">Rp {{ number_format($exp->expense_non_raw_material, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">3. Belanja Pribadi:</span>
                        <span class="font-bold text-purple-700 font-sans">Rp {{ number_format($exp->expense_personal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-slate-100">
                        <span class="font-bold text-slate-900">Total Belanja:</span>
                        <span class="font-black text-rose-600 font-sans">Rp {{ number_format($exp->total_expense, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($exp->expense_notes)
                    <div class="p-2 bg-slate-50 rounded-lg text-[11px] text-slate-600">
                        {{ $exp->expense_notes }}
                    </div>
                @endif
            </div>
        @empty
            <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                Belum ada catatan belanja harian.
            </div>
        @endforelse

        @if($expenses->hasPages())
            <div class="pt-2">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Input Cepat Belanja Harian -->
<div id="expenseInputModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 animate-fadeIn border border-slate-100">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Catat Belanja Harian Cabang</h3>
                    <p class="text-xs text-slate-500">Sesuai format Excel Belanja Cabang.</p>
                </div>
            </div>
            <button type="button" onclick="toggleExpenseInputModal()" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form action="{{ route('daily-expenses.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Cabang & Tanggal Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <!-- Pilihan Cabang (Custom Styled Dropdown) -->
                <div class="relative" id="modalBranchDropdownWrapper">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cabang Outlet</label>
                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                        <input type="hidden" name="branch_id" id="modalBranchInput" value="{{ $selectedBranchId ?: ($branches->first()?->id ?? '') }}">
                        <button 
                            type="button" 
                            onclick="toggleExpenseDropdown('modalBranchDropdownMenu')" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-slate-800 font-bold focus:outline-none focus:border-tealBrand transition cursor-pointer"
                        >
                            <div class="flex items-center space-x-2 truncate">
                                <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                                <span id="modalSelectedBranchLabel" class="truncate font-bold text-slate-900">
                                    {{ $branches->firstWhere('id', $selectedBranchId)?->name ?? ($branches->first()?->name ?? 'Pilih Cabang') }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="modalBranchDropdownMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 py-1 max-h-56 overflow-y-auto animate-fadeIn">
                            @foreach($branches as $b)
                                <button 
                                    type="button" 
                                    onclick="selectModalBranch('{{ $b->id }}', '{{ $b->name }}')" 
                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-slate-50 flex items-center justify-between {{ ($selectedBranchId == $b->id || (empty($selectedBranchId) && $loop->first)) ? 'font-bold text-tealBrand bg-teal-50/50' : 'text-slate-700' }}"
                                >
                                    <span>{{ $b->name }}</span>
                                    <span class="modal-branch-check {{ ($selectedBranchId == $b->id || (empty($selectedBranchId) && $loop->first)) ? '' : 'hidden' }}" id="modalCheck-{{ $b->id }}">
                                        <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        <div class="px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 flex items-center justify-between">
                            <span>{{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
                            <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider bg-slate-200/70 px-1.5 py-0.5 rounded">Terkunci</span>
                        </div>
                    @endif
                </div>

                <!-- Pilihan Tanggal (Flatpickr) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Belanja</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="report_date" 
                            id="modalReportDateInput" 
                            value="{{ date('Y-m-d') }}" 
                            class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-tealBrand transition cursor-pointer"
                            required
                        >
                    </div>
                </div>
            </div>

            <!-- 3 Input Pos Belanja -->
            <div class="space-y-3 pt-1">
                <!-- 1. Belanja Bahan Baku -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-800">1. Belanja Bahan Baku</label>
                        <span class="text-[10px] text-slate-400 font-medium">Daging, ayam, sayur, bumbu</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs text-slate-400 font-black">Rp</span>
                        <input 
                            type="text" 
                            name="expense_raw_material" 
                            id="modalRawExpense" 
                            value="0" 
                            onfocus="if(this.value==='0') this.select();"
                            oninput="formatRupiahInput(this); calculateModalTotalExpense();" 
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-tealBrand rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none transition"
                        >
                    </div>
                </div>

                <!-- 2. Belanja Non Bahan Baku -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-800">2. Belanja Non Bahan Baku</label>
                        <span class="text-[10px] text-slate-400 font-medium">Gas LPG, plastik, sabun, listrik</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs text-slate-400 font-black">Rp</span>
                        <input 
                            type="text" 
                            name="expense_non_raw_material" 
                            id="modalNonRawExpense" 
                            value="0" 
                            onfocus="if(this.value==='0') this.select();"
                            oninput="formatRupiahInput(this); calculateModalTotalExpense();" 
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-tealBrand rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none transition"
                        >
                    </div>
                </div>

                <!-- 3. Belanja Pribadi -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-800">3. Belanja Pribadi / Kasbon</label>
                        <span class="text-[10px] text-slate-400 font-medium">Kasbon, keperluan pribadi/staf</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs text-slate-400 font-black">Rp</span>
                        <input 
                            type="text" 
                            name="expense_personal" 
                            id="modalPersonalExpense" 
                            value="0" 
                            onfocus="if(this.value==='0') this.select();"
                            oninput="formatRupiahInput(this); calculateModalTotalExpense();" 
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-tealBrand rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Total Belanja Live Summary Card -->
            <div class="p-4 bg-rose-50/80 border border-rose-200 rounded-xl flex items-center justify-between">
                <div>
                    <span class="block text-[10px] font-black text-rose-900 uppercase tracking-wider">TOTAL PENGELUARAN BELANJA</span>
                    <span class="text-[11px] text-rose-600 font-medium">Akumulasi Bahan Baku + Non Bahan Baku + Pribadi</span>
                </div>
                <div class="text-xl font-black text-rose-600 font-sans tracking-tight" id="modalTotalExpenseLabel">
                    Rp 0
                </div>
            </div>

            <!-- Catatan Belanja -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rincian / Catatan Belanja (Opsional)</label>
                <textarea 
                    name="expense_notes" 
                    rows="2" 
                    placeholder="Contoh: Beli ayam 10kg Rp 350.000, Gas LPG 2 tabung Rp 44.000..." 
                    class="w-full p-3 text-xs bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-tealBrand transition"
                ></textarea>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button 
                    type="button" 
                    onclick="toggleExpenseInputModal()" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>Simpan Belanja</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#filterExpenseDateFrom", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        flatpickr("#filterExpenseDateTo", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        flatpickr("#modalReportDateInput", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        document.addEventListener('click', function(e) {
            const wrappers = [
                { wrapper: 'expenseBranchWrapper', menu: 'expenseBranchMenu' },
                { wrapper: 'expenseSortWrapper', menu: 'expenseSortMenu' },
                { wrapper: 'modalBranchDropdownWrapper', menu: 'modalBranchDropdownMenu' }
            ];

            wrappers.forEach(item => {
                const wrapEl = document.getElementById(item.wrapper);
                const menuEl = document.getElementById(item.menu);
                if (wrapEl && menuEl && !wrapEl.contains(e.target)) {
                    menuEl.classList.add('hidden');
                }
            });
        });
    });

    function toggleExpenseDropdown(menuId) {
        const menu = document.getElementById(menuId);
        if (menu) menu.classList.toggle('hidden');
    }

    function selectModalBranch(id, name) {
        const input = document.getElementById('modalBranchInput');
        const label = document.getElementById('modalSelectedBranchLabel');
        const menu = document.getElementById('modalBranchDropdownMenu');
        if (input) input.value = id;
        if (label) label.innerText = name;
        if (menu) menu.classList.add('hidden');

        document.querySelectorAll('.modal-branch-check').forEach(el => el.classList.add('hidden'));
        const check = document.getElementById('modalCheck-' + id);
        if (check) check.classList.remove('hidden');
    }

    function selectExpenseBranchOption(val, label) {
        document.getElementById('filterBranchInput').value = val;
        const labelEl = document.getElementById('selectedBranchLabel');
        if (labelEl) labelEl.innerText = label;
        document.getElementById('expenseBranchMenu').classList.add('hidden');
        document.getElementById('expenseFilterForm').submit();
    }

    function selectExpenseSortOption(val) {
        document.getElementById('filterSortInput').value = val;
        document.getElementById('expenseSortMenu').classList.add('hidden');
        document.getElementById('expenseFilterForm').submit();
    }

    function toggleExpenseInputModal() {
        const modal = document.getElementById('expenseInputModal');
        if (modal) modal.classList.toggle('hidden');
    }

    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value === '') {
            input.value = '0';
        } else {
            input.value = parseInt(value, 10).toLocaleString('id-ID');
        }
    }

    function cleanNumber(str) {
        if (str === null || str === undefined || str === '') return 0;
        if (typeof str === 'number') return isNaN(str) ? 0 : str;

        let s = str.toString().trim();
        if (/^-?\d+(\.\d{1,2})?$/.test(s)) {
            const val = parseFloat(s);
            return isNaN(val) ? 0 : val;
        }
        s = s.replace(/\./g, '').replace(/,/g, '.');
        const parsed = parseFloat(s);
        return isNaN(parsed) ? 0 : parsed;
    }

    function calculateModalTotalExpense() {
        const raw = cleanNumber(document.getElementById('modalRawExpense').value);
        const nonRaw = cleanNumber(document.getElementById('modalNonRawExpense').value);
        const personal = cleanNumber(document.getElementById('modalPersonalExpense').value);

        const total = raw + nonRaw + personal;
        document.getElementById('modalTotalExpenseLabel').innerText = 'Rp ' + Math.round(total).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endsection
