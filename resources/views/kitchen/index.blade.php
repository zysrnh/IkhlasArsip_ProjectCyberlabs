@extends('layouts.app')

@section('title', 'Riwayat Input Dapur Harian')

@section('content')
<div class="space-y-6 animate-fadeIn pb-6">

    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Input Dapur Harian</h1>
                @if(auth()->user()->isViewer())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                        Viewer
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Pencatatan porsi masakan dapur harian, kontrol sisa otomatis, monitoring lauk terbuang, dan rekapan omzet kasir per cabang.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <!-- Tombol Export PDF Rekapitulasi Sesuai Filter -->
            <a 
                href="{{ route('kitchen-reports.export-summary-pdf', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-all duration-150 cursor-pointer"
                title="Unduh Rekap Laporan Dapur PDF Sesuai Filter"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export PDF</span>
            </a>

            @if(!auth()->user()->isViewer())
                @php
                    $yesterday = \Carbon\Carbon::yesterday()->format('Y-m-d');
                @endphp
                <a 
                    href="{{ route('kitchen-reports.create', ['report_date' => $yesterday]) }}" 
                    class="px-3.5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold rounded-xl shadow-2xs inline-flex items-center space-x-1.5 transition cursor-pointer"
                    title="Input Laporan Susulan Kemarin (H-1)"
                >
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Input Susulan (H-1)</span>
                </a>
                <a 
                    href="{{ route('kitchen-reports.create') }}" 
                    class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-2xs inline-flex items-center space-x-1.5 transition cursor-pointer"
                    title="Import Laporan via Excel Spreadsheet"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Import Excel</span>
                </a>
                <a 
                    href="{{ route('kitchen-reports.create') }}" 
                    class="px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center justify-center space-x-2 transition-all duration-150 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Input Masakan Hari Ini</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 6 Primary Stat Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5">
        
        <!-- Total Laporan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-blue-600 transition-colors">
                    Total Laporan
                </span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-xl font-black text-slate-900 tracking-tight">
                    {{ number_format($stats['total_reports']) }} <span class="text-xs font-normal text-slate-400">hari</span>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Sesuai filter aktif
            </div>
        </div>

        <!-- Penjualan Porsi Dapur -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-tealBrand transition-colors">
                    Penjualan Dapur
                </span>
                <div class="w-7 h-7 rounded-lg bg-teal-50 text-tealBrand flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-lg font-black text-slate-900 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Nilai porsi terjual
            </div>
        </div>

        <!-- Total Omzet Kasir -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">
                    Total Omzet Kasir
                </span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-lg font-black text-emerald-600 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Cash + QRIS + Online
            </div>
        </div>

        <!-- Selisih Kasir -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition-colors">
                    Akumulasi Selisih
                </span>
                <div class="w-7 h-7 rounded-lg {{ $stats['total_diff'] < 0 ? 'bg-rose-50 text-rose-600' : ($stats['total_diff'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-500') }} flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-lg font-black tracking-tight font-sans truncate {{ $stats['total_diff'] < 0 ? 'text-rose-600' : ($stats['total_diff'] > 0 ? 'text-amber-600' : 'text-slate-800') }}">
                    {{ $stats['total_diff'] < 0 ? '-' : ($stats['total_diff'] > 0 ? '+' : '') }}Rp {{ number_format(abs($stats['total_diff']), 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Kasir vs Penjualan
            </div>
        </div>

        <!-- Sisa Lauk Bisa Dijual -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-cyan-600 transition-colors">
                    Sisa Bisa Dijual
                </span>
                <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-lg font-black text-[#0A97B0] tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_sellable'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_sellable'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Lauk tersimpan H+1
            </div>
        </div>

        <!-- Lauk Terbuang / Basi -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                    Lauk Terbuang (Basi)
                </span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <div class="my-2">
                <div class="text-lg font-black text-rose-600 tracking-tight font-sans truncate" title="Rp {{ number_format($stats['total_wasted'], 0, ',', '.') }}">
                    Rp {{ number_format($stats['total_wasted'], 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-slate-400 font-medium pt-1.5 border-t border-slate-100">
                Kerugian basi
            </div>
        </div>

    </div>

    <!-- Search & Filter Bar Component -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3.5">
        
        <!-- Header Filter -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filter & Pencarian Laporan</span>
                @if(request()->anyFilled(['search', 'branch_id', 'diff_status', 'date_from', 'date_to', 'sort']))
                    <span class="w-2 h-2 rounded-full bg-tealBrand inline-block"></span>
                @endif
            </div>

            @if(request()->anyFilled(['search', 'branch_id', 'diff_status', 'date_from', 'date_to', 'sort']))
                <a href="{{ route('kitchen-reports.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-bold flex items-center space-x-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span>Reset Semua Filter</span>
                </a>
            @endif
        </div>

        <form action="{{ route('kitchen-reports.index') }}" method="GET" id="kitchenFilterFormMain" class="space-y-3">
            <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ $selectedBranchId }}">
            <input type="hidden" name="diff_status" id="filterDiffStatusInput" value="{{ request('diff_status') }}">
            <input type="hidden" name="sort" id="filterSortInput" value="{{ request('sort', 'terbaru') }}">

            <!-- Row 1: Search & Dropdowns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- 1. Search Bar (Col 4) -->
                <div class="lg:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="mainKitchenSearchInput"
                        value="{{ request('search') }}" 
                        placeholder="Cari PIC, catatan, atau cabang..." 
                        class="w-full pl-10 pr-8 py-2.5 bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 shadow-sm focus:outline-none transition"
                        oninput="handleKitchenSearchDebounce(this)"
                    >
                    @if(request('search'))
                        <a href="{{ route('kitchen-reports.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>

                <!-- 2. Filter Cabang (Col 3) -->
                <div class="lg:col-span-3 relative" id="filterBranchWrapper">
                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                        <button 
                            type="button" 
                            onclick="toggleFilterPopover('filterBranchMenu')" 
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

                        <div id="filterBranchMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 max-h-56 overflow-y-auto animate-fadeIn">
                            <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Pilih Cabang</div>
                            <button 
                                type="button" 
                                onclick="selectFilterBranchOption('', 'Semua Cabang')" 
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
                                    onclick="selectFilterBranchOption('{{ $b->id }}', '{{ $b->name }}')" 
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

                <!-- 3. Filter Status Selisih (Col 3) -->
                <div class="lg:col-span-3 relative" id="filterDiffWrapper">
                    <button 
                        type="button" 
                        onclick="toggleFilterPopover('filterDiffMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                    >
                        <span class="truncate">
                            @if(request('diff_status') === 'match')
                                Selisih: Cocok (Match)
                            @elseif(request('diff_status') === 'over')
                                Selisih: Uang Kasir Lebih (+)
                            @elseif(request('diff_status') === 'under')
                                Selisih: Uang Kasir Kurang (-)
                            @else
                                Semua Status Selisih
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="filterDiffMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 animate-fadeIn">
                        <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Status Selisih Kasir</div>
                        
                        @php
                            $diffOptions = [
                                '' => 'Semua Status Selisih',
                                'match' => 'Cocok (Match)',
                                'over' => 'Uang Kasir Lebih (+)',
                                'under' => 'Uang Kasir Kurang (-)'
                            ];
                        @endphp

                        @foreach($diffOptions as $dKey => $dLabel)
                            <button 
                                type="button" 
                                onclick="selectFilterDiffOption('{{ $dKey }}')" 
                                class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('diff_status') === $dKey ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                            >
                                <span>{{ $dLabel }}</span>
                                @if(request('diff_status') === $dKey)
                                    <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Sorting Filter (Col 2) -->
                <div class="lg:col-span-2 relative" id="filterSortWrapper">
                    <button 
                        type="button" 
                        onclick="toggleFilterPopover('filterSortMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-bold transition cursor-pointer"
                    >
                        <span class="truncate">
                            @if(request('sort') === 'terlama')
                                Terlama
                            @elseif(request('sort') === 'omset_terbanyak')
                                Omzet Kasir Tertinggi
                            @elseif(request('sort') === 'omset_tersedikit')
                                Omzet Terendah
                            @elseif(request('sort') === 'penjualan_terbanyak')
                                Penjualan Tertinggi
                            @elseif(request('sort') === 'selisih_terbesar')
                                Selisih Terbesar
                            @else
                                Terbaru
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="filterSortMenu" class="hidden absolute right-0 mt-1.5 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 py-1.5 animate-fadeIn">
                        <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">Urutan Laporan</div>
                        
                        @php
                            $sortOptions = [
                                'terbaru' => 'Terbaru',
                                'terlama' => 'Terlama',
                                'omset_terbanyak' => 'Omzet Kasir Tertinggi',
                                'omset_tersedikit' => 'Omzet Terendah',
                                'penjualan_terbanyak' => 'Penjualan Tertinggi',
                                'selisih_terbesar' => 'Selisih Terbesar',
                            ];
                        @endphp

                        @foreach($sortOptions as $sKey => $sLabel)
                            <button 
                                type="button" 
                                onclick="selectFilterSortOption('{{ $sKey }}')" 
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

            </div>

            <!-- Row 2: Date Filters & Apply Button -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center pt-1 border-t border-slate-100">
                
                <!-- Tanggal Dari (Col 5) -->
                <div class="lg:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="filterDateFrom"
                        name="date_from" 
                        value="{{ request('date_from') }}" 
                        placeholder="Tanggal Dari"
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-tealBrand cursor-pointer shadow-xs"
                    >
                </div>

                <!-- Tanggal Sampai (Col 5) -->
                <div class="lg:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="filterDateTo"
                        name="date_to" 
                        value="{{ request('date_to') }}" 
                        placeholder="Tanggal Sampai"
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-tealBrand cursor-pointer shadow-xs"
                    >
                </div>

                <!-- Submit Button (Col 2) -->
                <div class="lg:col-span-2">
                    <button 
                        type="submit" 
                        class="w-full px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition duration-150 flex items-center justify-center space-x-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>

            </div>
        </form>

    </div>

    <!-- Summary Info Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs px-1 text-slate-500 font-medium">
        <div>
            Menampilkan <strong class="text-slate-800 font-bold">{{ $reports->total() }}</strong> laporan dapur
            @if(request()->anyFilled(['search', 'branch_id', 'diff_status', 'date_from', 'date_to', 'sort']))
                &bull; <span class="text-tealBrand font-bold">Filter Sedang Aktif</span>
            @endif
        </div>
        <div class="flex items-center space-x-4">
            <span>Total Omzet Terfilter: <strong class="text-slate-900 font-black font-sans">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <!-- Main Table View (Desktop >= md) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 rounded-l-lg">Tanggal</th>
                        <th class="py-3.5 px-4">Cabang</th>
                        <th class="py-3.5 px-4 text-right">Penjualan Dapur</th>
                        <th class="py-3.5 px-4 text-right">Total Omzet Kasir</th>
                        <th class="py-3.5 px-4 text-center">Status Selisih</th>
                        <th class="py-3.5 px-4 text-right">Sisa Bisa Dijual</th>
                        <th class="py-3.5 px-4 text-right">Lauk Terbuang</th>
                        <th class="py-3.5 px-4">Penginput</th>
                        <th class="py-3.5 px-4 text-center rounded-r-lg w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($reports as $r)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('kitchen-reports.show', $r->id) }}" class="font-bold text-slate-900 hover:text-tealBrand transition-colors">
                                    {{ $r->report_date->translatedFormat('d M Y') }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ $r->report_date->translatedFormat('l') }}</div>
                            </td>

                            <!-- Cabang -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-800">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $r->branch->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Penjualan Dapur -->
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900 font-sans whitespace-nowrap">
                                Rp {{ number_format($r->grand_total_sales, 0, ',', '.') }}
                            </td>

                            <!-- Total Omzet Kasir & Breakdown -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="font-black text-emerald-600 font-sans text-xs">
                                    Rp {{ number_format($r->total_omset, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-400 space-x-1 font-normal">
                                    <span>T: {{ number_format($r->cash_income / 1000, 0) }}k</span>
                                    <span>&bull;</span>
                                    <span>Q: {{ number_format($r->qris_income / 1000, 0) }}k</span>
                                    <span>&bull;</span>
                                    <span>O: {{ number_format($r->online_food_income / 1000, 0) }}k</span>
                                </div>
                            </td>

                            <!-- Status Selisih -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if(abs($r->difference_amount) < 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Cocok (Match)
                                    </span>
                                @elseif($r->difference_amount > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="Uang kasir lebih banyak dari nilai porsi terjual">
                                        Lebih +Rp {{ number_format($r->difference_amount, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200" title="Uang kasir kurang dari nilai porsi terjual">
                                        Selisih -Rp {{ number_format(abs($r->difference_amount), 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Sisa Bisa Dijual -->
                            <td class="py-3.5 px-4 text-right font-bold text-cyan-600 font-sans whitespace-nowrap">
                                Rp {{ number_format($r->total_remaining_sellable, 0, ',', '.') }}
                            </td>

                            <!-- Lauk Terbuang (Basi) -->
                            <td class="py-3.5 px-4 text-right font-bold text-rose-600 font-sans whitespace-nowrap">
                                Rp {{ number_format($r->total_wasted_food, 0, ',', '.') }}
                            </td>

                            <!-- Penginput -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-slate-500">
                                <div class="font-bold text-slate-800 leading-tight">{{ $r->user->name ?? 'Sistem' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $r->created_at ? $r->created_at->format('H:i') : '' }}</div>
                            </td>

                            <!-- Aksi Cepat -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    
                                    <!-- Detail -->
                                    <a 
                                        href="{{ route('kitchen-reports.show', $r->id) }}" 
                                        class="p-1.5 text-slate-500 hover:text-tealBrand hover:bg-slate-100 rounded-lg transition" 
                                        title="Lihat Detail Laporan"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <!-- PDF Export -->
                                    <a 
                                        href="{{ route('kitchen-reports.export-pdf', $r->id) }}" 
                                        class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                        title="Download PDF Resmi"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>

                                    @if(!auth()->user()->isViewer())
                                        <!-- Edit -->
                                        <a 
                                            href="{{ route('kitchen-reports.edit', $r->id) }}" 
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" 
                                            title="Edit Laporan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('kitchen-reports.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan dapur tanggal {{ $r->report_date->translatedFormat('d F Y') }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" 
                                                title="Hapus Laporan"
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
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="font-bold text-slate-700 text-sm">Tidak ada laporan dapur yang sesuai filter.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah rentang tanggal atau reset filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/60">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Cards View (< md) -->
    <div class="md:hidden space-y-3">
        @forelse($reports as $r)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-xs">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <a href="{{ route('kitchen-reports.show', $r->id) }}" class="font-extrabold text-sm text-slate-900 hover:text-tealBrand">
                            {{ $r->report_date->translatedFormat('d M Y') }}
                        </a>
                        <div class="text-[10px] text-slate-400 font-semibold">{{ $r->report_date->translatedFormat('l') }}</div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $r->branch->name ?? '-' }}
                    </span>
                </div>

                <!-- Card Body -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 font-medium">Penjualan Dapur:</span>
                        <div class="font-bold text-slate-900 font-sans">Rp {{ number_format($r->grand_total_sales, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-medium">Omzet Kasir:</span>
                        <div class="font-black text-emerald-600 font-sans">Rp {{ number_format($r->total_omset, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-medium">Sisa Bisa Dijual:</span>
                        <div class="font-bold text-cyan-600 font-sans">Rp {{ number_format($r->total_remaining_sellable, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-medium">Lauk Basi:</span>
                        <div class="font-bold text-rose-600 font-sans">Rp {{ number_format($r->total_wasted_food, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Status Selisih Badge -->
                <div class="pt-1">
                    @if(abs($r->difference_amount) < 1)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Cocok (Match)
                        </span>
                    @elseif($r->difference_amount > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Lebih +Rp {{ number_format($r->difference_amount, 0, ',', '.') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                            Selisih -Rp {{ number_format(abs($r->difference_amount), 0, ',', '.') }}
                        </span>
                    @endif
                </div>

                <!-- Card Footer & Actions -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">PIC: <strong class="text-slate-700">{{ $r->user->name ?? '-' }}</strong></span>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('kitchen-reports.show', $r->id) }}" class="px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">
                            Detail
                        </a>
                        <a href="{{ route('kitchen-reports.export-pdf', $r->id) }}" class="px-2.5 py-1 text-xs font-bold bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100">
                            PDF
                        </a>
                        @if(!auth()->user()->isViewer())
                            <a href="{{ route('kitchen-reports.edit', $r->id) }}" class="px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                Tidak ada laporan dapur yang sesuai filter.
            </div>
        @endforelse

        @if($reports->hasPages())
            <div class="pt-2">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Filter Scripts & Flatpickr Init -->
<script>
    let searchDebounceTimer;

    document.addEventListener('DOMContentLoaded', function() {
        // Init Flatpickr Indonesian
        flatpickr("#filterDateFrom", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        flatpickr("#filterDateTo", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true"
        });

        // Close dropdown popovers on outside click
        document.addEventListener('click', function(e) {
            const wrappers = [
                { wrapper: 'filterBranchWrapper', menu: 'filterBranchMenu' },
                { wrapper: 'filterDiffWrapper', menu: 'filterDiffMenu' },
                { wrapper: 'filterSortWrapper', menu: 'filterSortMenu' }
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

    function toggleFilterPopover(menuId) {
        const menu = document.getElementById(menuId);
        if (menu) {
            // Tutup popover lain jika sedang terbuka
            ['filterBranchMenu', 'filterDiffMenu', 'filterSortMenu'].forEach(id => {
                if (id !== menuId) {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                }
            });
            menu.classList.toggle('hidden');
        }
    }

    function selectFilterBranchOption(val, label) {
        document.getElementById('filterBranchInput').value = val;
        const labelEl = document.getElementById('selectedBranchLabel');
        if (labelEl) labelEl.innerText = label;
        document.getElementById('filterBranchMenu').classList.add('hidden');
        document.getElementById('kitchenFilterFormMain').submit();
    }

    function selectFilterDiffOption(val) {
        document.getElementById('filterDiffStatusInput').value = val;
        document.getElementById('filterDiffMenu').classList.add('hidden');
        document.getElementById('kitchenFilterFormMain').submit();
    }

    function selectFilterSortOption(val) {
        document.getElementById('filterSortInput').value = val;
        document.getElementById('filterSortMenu').classList.add('hidden');
        document.getElementById('kitchenFilterFormMain').submit();
    }

    function handleKitchenSearchDebounce(input) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            document.getElementById('kitchenFilterFormMain').submit();
        }, 600);
    }
</script>
@endsection
