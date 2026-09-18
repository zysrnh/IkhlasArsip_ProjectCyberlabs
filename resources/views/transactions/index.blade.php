@extends('layouts.app')

@section('title', 'Summary Transaksi & Keuangan Cabang')

@section('content')
<div class="space-y-6 animate-fadeIn pb-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Summary Transaksi & Keuangan</h1>
                @if(auth()->user()->isViewer())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                        Viewer
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Rekapitulasi pendapatan kasir (Cash, QRIS, Online Food), belanja operasional cabang, dan sisa setoran kas bersih.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto flex-wrap">
            <a 
                href="{{ route('transactions.export-pdf', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Export PDF</span>
            </a>

            <a 
                href="{{ route('transactions.export-excel', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- 4 Primary Stat Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. Total Pendapatan Cash (Laci) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-slate-700 transition-colors">
                    1. Pendapatan Cash (Laci)
                </span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Uang Tunai Fisik</span>
                <span class="font-bold text-blue-600">{{ number_format($stats['total_records']) }} Hari Laporan</span>
            </div>
        </div>

        <!-- 2. Total Pendapatan QRIS / Transfer -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-slate-700 transition-colors">
                    2. Pendapatan QRIS / Bank
                </span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-tealBrand flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Settlement Digital</span>
                <span class="font-bold text-tealBrand">Direct Bank</span>
            </div>
        </div>

        <!-- 3. Total Pengeluaran Belanja Cabang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                    3. Total Belanja Cabang
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-2xl font-black text-rose-600 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Bahan Baku + Non + Pribadi</span>
                <span class="font-bold text-rose-600">Pengeluaran</span>
            </div>
        </div>

        <!-- 4. Sisa Setoran Kas Bersih (Setoran Bersih) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-700 transition-colors">
                    4. Sisa Kas Setoran Bersih
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="my-2.5">
                <div class="text-2xl font-black text-emerald-700 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Cash Laci - Belanja</span>
                <span class="font-bold text-emerald-700">Wajib Disetor</span>
            </div>
        </div>

    </div>

    <!-- Filter Toolbar (Sesuai Sheet SUMMARY Excel) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3.5">
        <form action="{{ route('transactions.index') }}" method="GET" id="summaryFilterForm" class="space-y-3">
            <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ $selectedBranchId }}">
            <input type="hidden" name="month" id="filterMonthInput" value="{{ $selectedMonth }}">
            <input type="hidden" name="sort" id="filterSortInput" value="{{ request('sort', 'terbaru') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Search Input (Col 3) -->
                <div class="lg:col-span-3 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="summarySearchInput"
                        value="{{ request('search') }}" 
                        placeholder="Cari cabang, catatan, staf..." 
                        class="w-full pl-10 pr-8 py-2.5 bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 shadow-sm focus:outline-none transition"
                    >
                    @if(request('search'))
                        <a href="{{ route('transactions.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>

                <!-- Filter Cabang (Col 3) -->
                <div class="lg:col-span-3 relative" id="summaryBranchWrapper">
                    @if(auth()->user()->canAccessAllBranches() || auth()->user()->isViewer())
                        <button 
                            type="button" 
                            onclick="toggleSummaryDropdown('summaryBranchMenu')" 
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

                        <div id="summaryBranchMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 max-h-56 overflow-y-auto animate-fadeIn">
                            <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Pilih Cabang</div>
                            <button 
                                type="button" 
                                onclick="selectSummaryBranchOption('', 'Semua Cabang')" 
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
                                    onclick="selectSummaryBranchOption('{{ $b->id }}', '{{ $b->name }}')" 
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

                <!-- Filter Bulan Cepat (Col 3) -->
                <div class="lg:col-span-3 relative" id="summaryMonthWrapper">
                    <button 
                        type="button" 
                        onclick="toggleSummaryDropdown('summaryMonthMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                    >
                        <span class="truncate" id="selectedMonthLabel">
                            @if(!empty($selectedMonth))
                                {{ collect($availableMonths)->firstWhere('value', $selectedMonth)['label'] ?? $selectedMonth }}
                            @else
                                Semua Bulan
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="summaryMonthMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 max-h-56 overflow-y-auto animate-fadeIn">
                        <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Filter Bulan</div>
                        <button 
                            type="button" 
                            onclick="selectSummaryMonthOption('', 'Semua Bulan')" 
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedMonth) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>Semua Bulan</span>
                            @if(empty($selectedMonth))
                                <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                        @foreach($availableMonths as $m)
                            <button 
                                type="button" 
                                onclick="selectSummaryMonthOption('{{ $m['value'] }}', '{{ $m['label'] }}')" 
                                class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $selectedMonth == $m['value'] ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                            >
                                <span>{{ $m['label'] }}</span>
                                @if($selectedMonth == $m['value'])
                                    <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Sorting Dropdown (Col 3) -->
                <div class="lg:col-span-3 relative" id="summarySortWrapper">
                    <button 
                        type="button" 
                        onclick="toggleSummaryDropdown('summarySortMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                    >
                        <span class="truncate">
                            @if(request('sort') === 'terlama')
                                Urutan: Terlama
                            @elseif(request('sort') === 'omset_terbesar')
                                Urutan: Omset Terbesar
                            @elseif(request('sort') === 'belanja_terbesar')
                                Urutan: Belanja Terbesar
                            @elseif(request('sort') === 'sisa_terbesar')
                                Urutan: Sisa Kas Terbesar
                            @else
                                Urutan: Terbaru
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="summarySortMenu" class="hidden absolute right-0 left-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 animate-fadeIn">
                        <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Urutkan Berdasarkan</div>
                        <button type="button" onclick="selectSummarySortOption('terbaru')" class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between hover:bg-slate-50 {{ request('sort', 'terbaru') == 'terbaru' ? 'text-tealBrand font-bold bg-teal-50/50' : 'text-slate-700' }}">
                            <span>Terbaru</span>
                        </button>
                        <button type="button" onclick="selectSummarySortOption('terlama')" class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between hover:bg-slate-50 {{ request('sort') == 'terlama' ? 'text-tealBrand font-bold bg-teal-50/50' : 'text-slate-700' }}">
                            <span>Terlama</span>
                        </button>
                        <button type="button" onclick="selectSummarySortOption('omset_terbesar')" class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between hover:bg-slate-50 {{ request('sort') == 'omset_terbesar' ? 'text-tealBrand font-bold bg-teal-50/50' : 'text-slate-700' }}">
                            <span>Omset Terbesar</span>
                        </button>
                        <button type="button" onclick="selectSummarySortOption('belanja_terbesar')" class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between hover:bg-slate-50 {{ request('sort') == 'belanja_terbesar' ? 'text-tealBrand font-bold bg-teal-50/50' : 'text-slate-700' }}">
                            <span>Belanja Terbesar</span>
                        </button>
                        <button type="button" onclick="selectSummarySortOption('sisa_terbesar')" class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between hover:bg-slate-50 {{ request('sort') == 'sisa_terbesar' ? 'text-tealBrand font-bold bg-teal-50/50' : 'text-slate-700' }}">
                            <span>Sisa Setoran Terbesar</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Tanggal Dari & Sampai Row -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1">
                <!-- Tanggal Dari (Col 5) -->
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="date_from" 
                        id="filterSummaryDateFrom" 
                        value="{{ request('date_from') }}" 
                        placeholder="Tanggal Dari" 
                        class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 hover:border-tealBrand rounded-xl text-xs font-bold text-slate-800 shadow-sm focus:outline-none cursor-pointer"
                    >
                </div>

                <!-- Tanggal Sampai (Col 5) -->
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="date_to" 
                        id="filterSummaryDateTo" 
                        value="{{ request('date_to') }}" 
                        placeholder="Tanggal Sampai" 
                        class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 hover:border-tealBrand rounded-xl text-xs font-bold text-slate-800 shadow-sm focus:outline-none cursor-pointer"
                    >
                </div>

                <!-- Action Button Terapkan & Reset (Col 2) -->
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 px-3 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'branch_id', 'month', 'date_from', 'date_to', 'sort']))
                        <a 
                            href="{{ route('transactions.index') }}" 
                            title="Reset Semua Filter" 
                            class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Records Table (Desktop) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden hidden md:block">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rekapitulasi Summary Transaksi Cabang</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-tealBrand border border-teal-200">
                    {{ number_format($summaryReports->total()) }} Data
                </span>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Omset = Cash + QRIS + Online | Sisa Setoran = Cash - Belanja
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-[11px] font-extrabold text-slate-700 uppercase tracking-wider">
                        <th class="py-3.5 px-3 text-center w-12 shrink-0">No</th>
                        <th class="py-3.5 px-3 text-center w-28 shrink-0">Tanggal</th>
                        <th class="py-3.5 px-4 min-w-[150px]">Cabang</th>
                        <th class="py-3.5 px-3 text-right w-32 shrink-0">Cash (Laci)</th>
                        <th class="py-3.5 px-3 text-right w-32 shrink-0">QRIS / Transfer</th>
                        <th class="py-3.5 px-3 text-right w-28 shrink-0">Online Food</th>
                        <th class="py-3.5 px-4 text-right w-36 shrink-0">Total Omset</th>
                        <th class="py-3.5 px-3 text-right w-32 shrink-0">Total Belanja</th>
                        <th class="py-3.5 px-4 text-right w-36 shrink-0">Sisa Kas Setoran</th>
                        <th class="py-3.5 px-3 text-center w-24 shrink-0">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                    @forelse($summaryReports as $index => $r)
                        @php
                            $rowNetCash = $r->cash_income - ($r->total_expense ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <!-- No -->
                            <td class="py-3 px-3 text-center text-slate-400 font-semibold">
                                {{ $summaryReports->firstItem() + $index }}
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3 px-3 text-center font-bold text-slate-900 whitespace-nowrap">
                                {{ $r->report_date->translatedFormat('d M Y') }}
                            </td>

                            <!-- Cabang -->
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-1.5">
                                    <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                                    <span>{{ $r->branch->name ?? '-' }}</span>
                                </div>
                            </td>

                            <!-- Pendapatan Cash -->
                            <td class="py-3 px-3 text-right font-semibold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($r->cash_income, 0, ',', '.') }}
                            </td>

                            <!-- Pendapatan QRIS -->
                            <td class="py-3 px-3 text-right font-semibold text-teal-700 whitespace-nowrap">
                                Rp {{ number_format($r->qris_income, 0, ',', '.') }}
                            </td>

                            <!-- Pendapatan Online -->
                            <td class="py-3 px-3 text-right font-semibold text-slate-600 whitespace-nowrap">
                                Rp {{ number_format($r->online_food_income, 0, ',', '.') }}
                            </td>

                            <!-- Total Omset -->
                            <td class="py-3 px-4 text-right font-black text-emerald-700 whitespace-nowrap">
                                Rp {{ number_format($r->total_omset, 0, ',', '.') }}
                            </td>

                            <!-- Total Belanja -->
                            <td class="py-3 px-3 text-right font-black text-rose-600 whitespace-nowrap">
                                Rp {{ number_format($r->total_expense ?? 0, 0, ',', '.') }}
                            </td>

                            <!-- Sisa Kas Setoran Bersih -->
                            <td class="py-3 px-4 text-right font-black text-tealBrand whitespace-nowrap">
                                Rp {{ number_format($rowNetCash, 0, ',', '.') }}
                            </td>

                            <!-- Aksi Detail -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <a 
                                    href="{{ route('kitchen-reports.show', $r->id) }}" 
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-[#0B192C] hover:text-white text-slate-700 transition"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-xs font-semibold">Tidak ada data transaksi atau laporan yang sesuai dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Sticky Bottom Grand Total -->
                <tfoot class="sticky bottom-0 bg-[#0B192C] text-white font-bold text-xs border-t-2 border-slate-800 z-10 shadow-lg">
                    <tr>
                        <td colspan="3" class="py-3.5 px-4 uppercase tracking-wider text-right text-slate-300 font-extrabold">
                            GRAND TOTAL:
                        </td>
                        <td class="py-3.5 px-3 text-right text-slate-100 font-black whitespace-nowrap">
                            Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right text-teal-300 font-black whitespace-nowrap">
                            Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right text-slate-300 font-bold whitespace-nowrap">
                            Rp {{ number_format($stats['total_online_income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-emerald-400 font-black text-sm whitespace-nowrap">
                            Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right text-rose-300 font-black text-sm whitespace-nowrap">
                            Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-cyan-300 font-black text-sm whitespace-nowrap">
                            Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($summaryReports->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $summaryReports->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Card View -->
    <div class="space-y-3.5 md:hidden">
        @forelse($summaryReports as $r)
            @php
                $rowNetCashMobile = $r->cash_income - ($r->total_expense ?? 0);
            @endphp
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <div class="font-extrabold text-sm text-slate-900">{{ $r->branch->name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500 font-semibold">{{ $r->report_date->translatedFormat('l, d F Y') }}</div>
                    </div>
                    <a 
                        href="{{ route('kitchen-reports.show', $r->id) }}" 
                        class="px-3 py-1 bg-slate-100 text-slate-800 text-xs font-bold rounded-lg"
                    >
                        Detail
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl">
                        <span class="text-[10px] text-slate-400 block font-bold uppercase">Cash Laci</span>
                        <span class="font-extrabold text-slate-900">Rp {{ number_format($r->cash_income, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl">
                        <span class="text-[10px] text-slate-400 block font-bold uppercase">QRIS / Bank</span>
                        <span class="font-extrabold text-teal-700">Rp {{ number_format($r->qris_income, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-emerald-50 p-2.5 rounded-xl">
                        <span class="text-[10px] text-emerald-800 block font-bold uppercase">Total Omset</span>
                        <span class="font-black text-emerald-700">Rp {{ number_format($r->total_omset, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-rose-50 p-2.5 rounded-xl">
                        <span class="text-[10px] text-rose-800 block font-bold uppercase">Total Belanja</span>
                        <span class="font-black text-rose-600">Rp {{ number_format($r->total_expense ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="p-3 bg-cyan-50/80 rounded-xl border border-cyan-200 flex items-center justify-between text-xs">
                    <span class="font-black text-cyan-950 uppercase">Sisa Kas Setoran Bersih:</span>
                    <span class="font-black text-tealBrand text-sm">Rp {{ number_format($rowNetCashMobile, 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                Belum ada data laporan summary transaksi.
            </div>
        @endforelse

        @if($summaryReports->hasPages())
            <div class="pt-2">
                {{ $summaryReports->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#filterSummaryDateFrom", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        flatpickr("#filterSummaryDateTo", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        document.addEventListener('click', function(e) {
            const wrappers = [
                { wrapper: 'summaryBranchWrapper', menu: 'summaryBranchMenu' },
                { wrapper: 'summaryMonthWrapper', menu: 'summaryMonthMenu' },
                { wrapper: 'summarySortWrapper', menu: 'summarySortMenu' }
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

    function toggleSummaryDropdown(menuId) {
        const menu = document.getElementById(menuId);
        if (menu) menu.classList.toggle('hidden');
    }

    function selectSummaryBranchOption(val, label) {
        document.getElementById('filterBranchInput').value = val;
        const labelEl = document.getElementById('selectedBranchLabel');
        if (labelEl) labelEl.innerText = label;
        document.getElementById('summaryBranchMenu').classList.add('hidden');
        document.getElementById('summaryFilterForm').submit();
    }

    function selectSummaryMonthOption(val, label) {
        document.getElementById('filterMonthInput').value = val;
        const labelEl = document.getElementById('selectedMonthLabel');
        if (labelEl) labelEl.innerText = label;
        document.getElementById('summaryMonthMenu').classList.add('hidden');
        document.getElementById('summaryFilterForm').submit();
    }

    function selectSummarySortOption(val) {
        document.getElementById('filterSortInput').value = val;
        document.getElementById('summarySortMenu').classList.add('hidden');
        document.getElementById('summaryFilterForm').submit();
    }
</script>
@endsection
