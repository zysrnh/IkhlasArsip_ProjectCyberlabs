@extends('layouts.app')

@section('title', 'Dashboard Penjualan')

@section('content')
<div class="space-y-7 animate-fadeIn pb-8">

    <!-- Top Header & Quick Filter Toolbar -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Executive Sales Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Selamat datang, <strong class="text-slate-800 font-bold">{{ auth()->user()->name }}</strong> &mdash; ringkasan performa penjualan, efisiensi operasional dapur, dan margin laba.
            </p>
        </div>

        <!-- Filter Controls Bar (Pill Container) -->
        <form id="dashboardFilterForm" method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2.5 self-start xl:self-auto">
            
            <!-- Date Range Inputs Pill -->
            <div class="flex items-center bg-white border border-slate-200 hover:border-slate-300 rounded-2xl px-3.5 py-2 shadow-xs space-x-2 text-xs transition">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="flex items-center space-x-1.5 font-semibold text-slate-700">
                    <input 
                        type="date" 
                        name="date_from" 
                        id="filterDateFrom" 
                        value="{{ $dateFrom }}"
                        class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none border-0 p-0 cursor-pointer"
                        title="Tanggal Mulai"
                    >
                    <span class="text-slate-300 font-bold">&ndash;</span>
                    <input 
                        type="date" 
                        name="date_to" 
                        id="filterDateTo" 
                        value="{{ $dateTo }}"
                        class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none border-0 p-0 cursor-pointer"
                        title="Tanggal Selesai"
                    >
                </div>
            </div>

            <!-- Branch Dropdown Popover / Pill -->
            @if(auth()->user()->canAccessAllBranches() || auth()->user()->isKepalaCabang())
                <div class="relative" id="dashboardBranchDropdownContainer">
                    <input type="hidden" name="branch_id" id="selectedBranchInput" value="{{ $selectedBranchId }}">

                    <button 
                        type="button" 
                        id="dashboardBranchDropdownBtn"
                        onclick="toggleDashboardBranchDropdown()"
                        class="flex items-center justify-between space-x-2.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-2xl py-2 px-3.5 text-xs font-bold text-slate-700 shadow-xs transition cursor-pointer"
                    >
                        <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                        <span id="dashboardCurrentBranchLabel" class="max-w-[140px] truncate">
                            @if($selectedBranchId)
                                {{ $allBranches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                            @else
                                Semua Cabang (Global)
                            @endif
                        </span>
                        <svg id="dashboardBranchChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        id="dashboardBranchDropdownMenu" 
                        class="hidden absolute right-0 top-full mt-1.5 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn"
                    >
                        <div class="px-3 py-1.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            Pilih Cabang
                        </div>
                        
                        <button 
                            type="button" 
                            onclick="selectDashboardBranchOption('', 'Semua Cabang (Global)')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedBranchId) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>Semua Cabang (Global)</span>
                            @if(empty($selectedBranchId))
                                <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>

                        @foreach($allBranches as $branch)
                            <button 
                                type="button" 
                                onclick="selectDashboardBranchOption('{{ $branch->id }}', '{{ $branch->name }}')"
                                class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $selectedBranchId == $branch->id ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                            >
                                <span>{{ $branch->name }}</span>
                                @if($selectedBranchId == $branch->id)
                                    <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="inline-flex items-center space-x-2 bg-white border border-slate-200 rounded-2xl px-3.5 py-2 shadow-xs text-xs font-bold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                    <span>{{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
                </div>
            @endif

            <!-- Submit Filter Button -->
            <button 
                type="submit" 
                class="bg-[#0B192C] hover:bg-[#142B4D] text-white font-bold text-xs px-4 py-2 rounded-2xl transition shadow-xs cursor-pointer inline-flex items-center space-x-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filter</span>
            </button>
        </form>
    </div>

    <!-- Section Carousel: 8 Cards Interaktif (Slide 1: Finansial, Slide 2: Operasional Dapur) -->
    <div class="space-y-3.5">
        
        <!-- Carousel Header with Navigation & Tab Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-1">
            
            <!-- Left: Tab Switcher (Finansial vs Operasional) -->
            <div class="inline-flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200 shadow-2xs self-start">
                <button 
                    type="button" 
                    id="carouselTabFinancial"
                    onclick="switchKpiCarousel(0)"
                    class="px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 bg-white text-slate-900 shadow-xs"
                >
                    <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                    <span>Ringkasan Finansial</span>
                </button>

                <button 
                    type="button" 
                    id="carouselTabKitchen"
                    onclick="switchKpiCarousel(1)"
                    class="px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 text-slate-500 hover:text-slate-800"
                >
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Operasional Dapur</span>
                </button>
            </div>

            <!-- Right: Navigation Arrows & Indicator Dots -->
            <div class="flex items-center space-x-3 self-end sm:self-auto">
                <span class="text-[11px] font-bold text-slate-400 hidden sm:inline" id="carouselSlideLabel">
                    Slide 1 dari 2 &bull; Ringkasan Finansial
                </span>

                <!-- Indicator Dots -->
                <div class="flex items-center space-x-1.5">
                    <button type="button" onclick="switchKpiCarousel(0)" id="carouselDot0" class="w-6 h-2 rounded-full bg-tealBrand transition-all duration-300 cursor-pointer" aria-label="Slide 1"></button>
                    <button type="button" onclick="switchKpiCarousel(1)" id="carouselDot1" class="w-2 h-2 rounded-full bg-slate-300 transition-all duration-300 cursor-pointer" aria-label="Slide 2"></button>
                </div>

                <!-- Prev & Next Button Controls -->
                <div class="flex items-center space-x-1.5">
                    <button 
                        type="button" 
                        onclick="prevKpiCarousel()" 
                        class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-900 transition cursor-pointer"
                        title="Slide Sebelumnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button 
                        type="button" 
                        onclick="nextKpiCarousel()" 
                        class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-900 transition cursor-pointer"
                        title="Slide Berikutnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Carousel Track Viewport (Mendukung Touch Swipe & Mouse Drag) -->
        <div id="kpiCarouselViewport" class="relative overflow-hidden rounded-2xl cursor-grab active:cursor-grabbing select-none">
            <div 
                id="kpiCarouselTrack" 
                class="flex transition-transform duration-500 ease-in-out w-full"
                style="transform: translateX(0%);"
            >
                
                <!-- Slide 1: 4 Primary Financial Cards -->
                <div class="w-full shrink-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    
                    <!-- Card 1: Total Omzet Kasir -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-tealBrand transition-colors">
                                Total Omzet Kasir
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-teal-50 text-tealBrand flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                                Rp {{ number_format($totalIncome, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Penjualan Lauk</span>
                            <span class="font-bold text-slate-700">Rp {{ number_format($totalSalesFood, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Card 2: Total Biaya Operasional -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                                Total Biaya Operasional
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-2xl font-black text-rose-600 tracking-tight font-sans">
                                Rp {{ number_format($totalCost, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Tetap / Belanja</span>
                            <span class="font-bold text-slate-700">
                                Rp {{ number_format($totalFixedCost, 0, ',', '.') }} / Rp {{ number_format($totalDailyExpense, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Card 3: Gross Margin -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">
                                Gross Margin (Laba)
                            </span>
                            <div class="w-8 h-8 rounded-xl {{ $grossMargin >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </div>
                        </div>
                        <div class="my-3 flex items-baseline justify-between">
                            <div class="text-2xl font-black {{ $grossMargin >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight font-sans">
                                Rp {{ number_format($grossMargin, 0, ',', '.') }}
                            </div>
                            <span class="text-xs font-black px-2 py-0.5 rounded-lg {{ $grossMargin >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $grossMarginPercent }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Formula</span>
                            <span class="font-bold text-slate-700">Omzet &minus; Total Biaya</span>
                        </div>
                    </div>

                    <!-- Card 4: Summary Selisih Kasir -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-cyan-600 transition-colors">
                                Summary Selisih Kasir
                            </span>
                            <div class="w-8 h-8 rounded-xl {{ $surplusKasir > 0 ? 'bg-emerald-50 text-emerald-600' : ($surplusKasir < 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600') }} flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="my-3 flex items-baseline justify-between">
                            <div class="text-2xl font-black {{ $surplusKasir > 0 ? 'text-emerald-600' : ($surplusKasir < 0 ? 'text-rose-600' : 'text-slate-800') }} tracking-tight font-sans">
                                {{ $surplusKasir < 0 ? '-' : ($surplusKasir > 0 ? '+' : '') }}Rp {{ number_format(abs($surplusKasir), 0, ',', '.') }}
                            </div>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-lg {{ $surplusKasir > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($surplusKasir < 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                {{ $surplusKasir > 0 ? 'Surplus' : ($surplusKasir < 0 ? 'Defisit' : 'Seimbang') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Status Kasir</span>
                            <span class="font-bold text-slate-700">
                                {{ $surplusKasir > 0 ? 'Uang Lebih' : ($surplusKasir < 0 ? 'Uang Kurang' : 'Klop Sesuai') }}
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Slide 2: 4 Operasional Dapur Cards -->
                <div class="w-full shrink-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    
                    <!-- Card 5: Total Laporan Dapur -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-blue-600 transition-colors">
                                Total Laporan Dapur
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                                {{ number_format($totalReports, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">laporan</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Rata-rata Omzet / Hari</span>
                            <span class="font-bold text-slate-700">Rp {{ number_format($avgDailyOmset, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Card 6: Total Porsi Terjual -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">
                                Total Porsi Terjual
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                                {{ number_format($totalPortionsSold, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">porsi</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Total Dimasak</span>
                            <span class="font-bold text-slate-700">{{ number_format($totalPortionsCooked, 0, ',', '.') }} Porsi</span>
                        </div>
                    </div>

                    <!-- Card 7: Lauk Terbuang (Basi) -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                                Lauk Terbuang (Basi)
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-2xl font-black text-rose-600 tracking-tight font-sans">
                                Rp {{ number_format($totalWastedFood, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Sisa Layak Jual</span>
                            <span class="font-bold text-slate-700">Rp {{ number_format($totalRemainingSellable, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Card 8: Top Cabang Penjualan -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition-colors">
                                Top Cabang Penjualan
                            </span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                        </div>
                        <div class="my-3">
                            <div class="text-xl font-black text-slate-900 tracking-tight truncate" title="{{ $topBranchName }}">
                                {{ $topBranchName }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                            <span>Omzet Tertinggi</span>
                            <span class="font-bold text-emerald-600">Rp {{ number_format($topBranchAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <!-- Section 3: Komparasi Kinerja Multi-Cabang (1-3 Cabang) -->
    @if(auth()->user()->canAccessAllBranches() || (auth()->user()->isKepalaCabang() && count($allBranches) > 1))
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-5 border-b border-slate-100 gap-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Perbandingan Kinerja Antar Cabang</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">Komparasi Multi-Cabang</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bandingkan performa Omzet, Biaya Operasional, Gross Margin, dan Selisih Kasir antar cabang secara berdampingan.</p>
                </div>

                <!-- Tombol Popover Pemilihan Cabang -->
                <div class="relative self-start sm:self-auto" id="compareBranchPopoverContainer">
                    <button 
                        type="button" 
                        id="compareBranchBtn"
                        onclick="toggleCompareBranchPopover()"
                        class="flex items-center space-x-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-2xl py-2 px-3.5 text-xs font-bold text-slate-700 shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Pilih Cabang ({{ count($comparisonData) }} Terpilih)</span>
                        <svg id="compareBranchChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Popover Dropdown Panel -->
                    <div 
                        id="compareBranchPopover" 
                        class="hidden absolute right-0 top-full mt-2 w-72 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 z-50 animate-fadeIn"
                    >
                        <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-slate-100">
                            <div class="text-[11px] font-extrabold text-slate-800 uppercase tracking-wider">
                                Pilih Cabang (Maks. 3)
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold" id="compareCountLabel">{{ count($comparisonData) }}/3 Dipilih</span>
                        </div>

                        <form id="branchComparisonForm" method="GET" action="{{ route('dashboard') }}" class="space-y-3">
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                            @if($selectedBranchId)
                                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                            @endif

                            <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                                @foreach($allBranches as $b)
                                    @php
                                        $isChecked = in_array($b->id, $compareBranchIds);
                                    @endphp
                                    <label class="flex items-center justify-between p-2 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition select-none">
                                        <div class="flex items-center space-x-2.5">
                                            <input 
                                                type="checkbox" 
                                                name="compare_branch_ids[]" 
                                                value="{{ $b->id }}"
                                                class="compare-branch-checkbox rounded text-tealBrand focus:ring-0 focus:ring-offset-0 cursor-pointer"
                                                {{ $isChecked ? 'checked' : '' }}
                                                onchange="handleCompareBranchChange(this)"
                                            >
                                            <span class="text-xs font-bold text-slate-800">{{ $b->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-[#0B192C] hover:bg-[#142B4D] text-white font-bold text-xs py-2.5 rounded-xl transition shadow-xs cursor-pointer text-center"
                            >
                                Terapkan Komparasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Comparison Cards Grid & Graphs Container -->
            @if(count($comparisonData) > 0)
                
                <!-- Side-by-Side Branch Cards -->
                <div class="grid grid-cols-1 md:grid-cols-{{ count($comparisonData) }} gap-4 sm:gap-5 mb-6">
                    @foreach($comparisonData as $cd)
                        @php
                            $soldRatio = $cd['portions_cooked'] > 0 ? round(($cd['portions_sold'] / $cd['portions_cooked']) * 100, 1) : 0;
                        @endphp
                        <div class="bg-slate-50/70 rounded-2xl border border-slate-200 p-5 flex flex-col justify-between space-y-4 hover:border-slate-300 transition-colors">
                            <!-- Header Cabang -->
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                                <div class="font-black text-sm text-slate-900 flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-tealBrand"></span>
                                    <span>{{ $cd['name'] }}</span>
                                </div>
                                <span class="text-[10px] font-extrabold bg-white border border-slate-200 px-2.5 py-0.5 rounded-lg text-slate-600 shadow-2xs">
                                    {{ $cd['total_reports'] }} Laporan
                                </span>
                            </div>

                            <!-- List Metrik Bersih & Lega -->
                            <div class="space-y-2.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-medium">Total Omzet Kasir</span>
                                    <span class="font-black text-slate-900 font-sans text-sm">Rp {{ number_format($cd['omset'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-medium">Total Biaya Operasional</span>
                                    <span class="font-black text-rose-600 font-sans">Rp {{ number_format($cd['total_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="pl-2.5 border-l-2 border-slate-200 space-y-1 text-[11px] text-slate-400">
                                    <div class="flex justify-between">
                                        <span>Biaya Tetap:</span>
                                        <span class="font-bold text-slate-700">Rp {{ number_format($cd['fixed_cost'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Belanja Dapur:</span>
                                        <span class="font-bold text-slate-700">Rp {{ number_format($cd['daily_expense'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/80">
                                    <span class="font-bold text-slate-800">Gross Margin</span>
                                    <div class="text-right">
                                        <span class="font-black {{ $cd['gross_margin'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-sans">
                                            Rp {{ number_format($cd['gross_margin'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md {{ $cd['gross_margin'] >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} ml-1">
                                            {{ $cd['gross_margin_percent'] }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-medium">Summary Selisih Kasir</span>
                                    <span class="font-bold {{ $cd['surplus_kasir'] > 0 ? 'text-emerald-600' : ($cd['surplus_kasir'] < 0 ? 'text-rose-600' : 'text-slate-700') }}">
                                        {{ $cd['surplus_kasir'] < 0 ? '-' : ($cd['surplus_kasir'] > 0 ? '+' : '') }}Rp {{ number_format(abs($cd['surplus_kasir']), 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-medium">Porsi Terjual / Masak</span>
                                    <span class="font-bold text-slate-800">
                                        {{ number_format($cd['portions_sold'], 0, ',', '.') }} / {{ number_format($cd['portions_cooked'], 0, ',', '.') }} Porsi
                                        <span class="text-[10px] text-slate-400 font-semibold">({{ $soldRatio }}%)</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Multi-Chart Tabs Toolbar -->
                <div class="pt-4 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 gap-3">
                        <div class="text-xs font-black text-slate-800 uppercase tracking-wider">
                            Visualisasi Komparasi Antar Cabang
                        </div>

                        <!-- Chart Tab Switcher -->
                        <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 self-start sm:self-auto text-xs">
                            <button 
                                type="button" 
                                id="compareChartTabFin" 
                                onclick="switchCompareChartTab('fin')"
                                class="px-3 py-1.5 rounded-lg font-extrabold transition-all cursor-pointer bg-white text-slate-900 shadow-2xs"
                            >
                                Finansial (Omzet & Biaya)
                            </button>
                            <button 
                                type="button" 
                                id="compareChartTabPortion" 
                                onclick="switchCompareChartTab('portion')"
                                class="px-3 py-1.5 rounded-lg font-extrabold transition-all cursor-pointer text-slate-500 hover:text-slate-800"
                            >
                                Porsi Dapur (Masak vs Jual)
                            </button>
                            <button 
                                type="button" 
                                id="compareChartTabPayment" 
                                onclick="switchCompareChartTab('payment')"
                                class="px-3 py-1.5 rounded-lg font-extrabold transition-all cursor-pointer text-slate-500 hover:text-slate-800"
                            >
                                Kanal Pembayaran
                            </button>
                        </div>
                    </div>

                    <!-- 1. Finansial Grouped Bar Chart -->
                    <div id="compareFinChartContainer" class="relative h-72 sm:h-80 w-full pt-2">
                        <canvas id="branchComparisonBarChart"></canvas>
                    </div>

                    <!-- 2. Porsi Dapur Grouped Bar Chart -->
                    <div id="comparePortionChartContainer" class="hidden relative h-72 sm:h-80 w-full pt-2">
                        <canvas id="branchComparisonPortionChart"></canvas>
                    </div>

                    <!-- 3. Kanal Pembayaran Grouped Bar Chart -->
                    <div id="comparePaymentChartContainer" class="hidden relative h-72 sm:h-80 w-full pt-2">
                        <canvas id="branchComparisonPaymentChart"></canvas>
                    </div>
                </div>

            @else
                <!-- Empty State: Belum Ada Cabang yang Dipilih -->
                <div class="py-12 px-4 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 flex flex-col items-center justify-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-tealBrand flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900">Belum Ada Cabang yang Dipilih</h4>
                        <p class="text-xs text-slate-400 mt-0.5 max-w-sm">Pilih 1 sampai 3 cabang melalui tombol di atas untuk melihat perbandingan performa finansial, porsi masakan, dan grafik komparasi berdampingan.</p>
                    </div>
                    <button 
                        type="button" 
                        onclick="toggleCompareBranchPopover()" 
                        class="mt-1 px-4 py-2 bg-tealBrand hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer inline-flex items-center space-x-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Pilih Cabang Komparasi</span>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Section 4: Grafik Tren Omzet Harian & Komposisi Metode Pembayaran -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Tren Penjualan Harian (Line Chart 8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-2 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Tren Omzet Penjualan Harian</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200">Live Analytics</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dinamika omzet kasir harian sepanjang rentang waktu terpilih</p>
                </div>
                <div class="text-xs text-slate-600 font-bold bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-200 self-start sm:self-auto">
                    {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                </div>
            </div>

            <div class="relative h-64 sm:h-72 w-full pt-2">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Komposisi Pembayaran Kasir (Donut Chart 4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Metode Pembayaran Kasir</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5" id="paymentDonutSubtitle">Persentase omzet per kanal penerimaan kasir</p>
                </div>
                @if($hasCompareBranches && count($comparisonData) > 0)
                    <div class="flex items-center p-0.5 bg-slate-100 rounded-lg border border-slate-200 text-[10px] font-bold overflow-x-auto max-w-full">
                        <button 
                            type="button" 
                            id="donutTab_all" 
                            onclick="switchPaymentDonut('all')" 
                            class="donut-branch-tab px-2 py-1 rounded-md transition cursor-pointer bg-white text-slate-900 shadow-2xs shrink-0"
                        >
                            Semua
                        </button>
                        @foreach($comparisonData as $cd)
                            <button 
                                type="button" 
                                id="donutTab_{{ $cd['id'] }}" 
                                onclick="switchPaymentDonut('{{ $cd['id'] }}')" 
                                class="donut-branch-tab px-2 py-1 rounded-md transition cursor-pointer text-slate-500 hover:text-slate-800 shrink-0"
                            >
                                {{ Str::limit($cd['name'], 10) }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="relative h-48 w-full my-auto flex items-center justify-center pt-2">
                <canvas id="paymentDonutChart"></canvas>
            </div>

            @php
                $totalCash = $totalCash ?? ($paymentSummaries['Penjualan Tunai']['amount'] ?? 0);
                $totalQris = $totalQris ?? ($paymentSummaries['QRIS / Transfer']['amount'] ?? 0);
                $totalOnline = $totalOnline ?? ($paymentSummaries['Online Food (Grab/Gojek/Shopee)']['amount'] ?? 0);
                $totalAllPay = $totalCash + $totalQris + $totalOnline;
                $pctCash = $totalAllPay > 0 ? round(($totalCash / $totalAllPay) * 100, 1) : 0;
                $pctQris = $totalAllPay > 0 ? round(($totalQris / $totalAllPay) * 100, 1) : 0;
                $pctOnline = $totalAllPay > 0 ? round(($totalOnline / $totalAllPay) * 100, 1) : 0;
            @endphp
            <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0A97B0]"></span>
                        <span class="text-slate-600 font-medium text-[11px]">Penjualan Tunai</span>
                        <span id="donutPct_cash" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-teal-50 text-tealBrand">({{ $pctCash }}%)</span>
                    </div>
                    <span id="donutVal_cash" class="font-bold text-slate-800 text-[11px]">Rp {{ number_format($totalCash, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0284c7]"></span>
                        <span class="text-slate-600 font-medium text-[11px]">QRIS / Transfer</span>
                        <span id="donutPct_qris" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700">({{ $pctQris }}%)</span>
                    </div>
                    <span id="donutVal_qris" class="font-bold text-slate-800 text-[11px]">Rp {{ number_format($totalQris, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                        <span class="text-slate-600 font-medium text-[11px]">Online Food</span>
                        <span id="donutPct_online" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">({{ $pctOnline }}%)</span>
                    </div>
                    <span id="donutVal_online" class="font-bold text-slate-800 text-[11px]">Rp {{ number_format($totalOnline, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 5: Top 5 Menu Masakan & Kontribusi Cabang -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Top 5 Menu Terlaris (6 Cols) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Top 5 Menu Masakan Terlaris</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Menu masakan dengan porsi terjual dan omzet tertinggi</p>
                </div>
                <span class="text-[10px] font-bold text-tealBrand uppercase tracking-wider bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-100">Leaderboard</span>
            </div>

            <div class="space-y-2.5">
                @forelse($topMenus as $index => $item)
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-lg {{ $index === 0 ? 'bg-amber-100 text-amber-800 font-black' : ($index === 1 ? 'bg-slate-200 text-slate-700 font-bold' : 'bg-slate-100 text-slate-600 font-bold') }} flex items-center justify-center text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-slate-900 leading-tight">{{ $item->menu->name ?? 'Menu #' . $item->menu_id }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">Terjual {{ number_format($item->total_sold, 0, ',', '.') }} Porsi</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-xs text-emerald-600 font-sans">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-6 text-center">Belum ada data penjualan menu dapur pada rentang ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Performa Penjualan Tiap Cabang (6 Cols) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Performa & Kontribusi Cabang</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Perbandingan omzet seluruh unit cabang aktif</p>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ count($branchComparisons) }} Cabang</span>
            </div>

            <div class="space-y-4">
                @forelse($branchComparisons as $index => $bc)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded-md bg-[#0B192C] text-white flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-800">{{ $bc['name'] }}</span>
                            </div>
                            <span class="text-[11px] text-slate-600 font-semibold font-sans">
                                <strong class="text-slate-900 font-bold">Rp {{ number_format($bc['amount'], 0, ',', '.') }}</strong> ({{ $bc['count'] }} lap)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-tealBrand h-2 rounded-full transition-all duration-500 ease-out" style="width: {{ $bc['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-6 text-center">Belum ada data cabang aktif pada rentang ini.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Section 6: Laporan Dapur Terkini -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Laporan Dapur Harian Terbaru</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Entri laporan penjualan porsi masakan dan setoran kasir terbaru</p>
            </div>
            <a href="{{ route('kitchen-reports.index') }}" class="text-xs font-bold text-tealBrand hover:underline flex items-center space-x-1 cursor-pointer">
                <span>Lihat Semua</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-200 bg-slate-50/60">
                        <th class="py-3 px-3.5 rounded-l-xl">TANGGAL</th>
                        <th class="py-3 px-3.5">CABANG</th>
                        <th class="py-3 px-3.5">PIC INPUT</th>
                        <th class="py-3 px-3.5 text-right">PENJUALAN LAUK</th>
                        <th class="py-3 px-3.5 text-right">OMZET KASIR</th>
                        <th class="py-3 px-3.5 text-right">SELISIH</th>
                        <th class="py-3 px-3.5 text-center rounded-r-xl">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentReports as $report)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 font-mono">{{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($report->report_date)->isoFormat('dddd') }}</div>
                            </td>
                            <td class="py-3.5 px-3.5 font-semibold text-slate-800">
                                {{ $report->branch->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-3.5 font-medium text-slate-700">
                                {{ $report->user->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-bold text-slate-900 font-sans">
                                Rp {{ number_format($report->grand_total_sales, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-black font-sans text-emerald-600">
                                Rp {{ number_format($report->total_omset, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-bold font-sans {{ $report->difference_amount < 0 ? 'text-rose-600' : ($report->difference_amount > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                {{ $report->difference_amount < 0 ? '-' : ($report->difference_amount > 0 ? '+' : '') }}Rp {{ number_format(abs($report->difference_amount), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-center">
                                <a href="{{ route('kitchen-reports.show', $report) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada laporan dapur harian pada rentang waktu ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    // State Carousel KPI Card
    let currentKpiSlide = 0;
    const totalKpiSlides = 2;

    function switchKpiCarousel(slideIndex) {
        currentKpiSlide = slideIndex;
        const track = document.getElementById('kpiCarouselTrack');
        const tabFinancial = document.getElementById('carouselTabFinancial');
        const tabKitchen = document.getElementById('carouselTabKitchen');
        const dot0 = document.getElementById('carouselDot0');
        const dot1 = document.getElementById('carouselDot1');
        const label = document.getElementById('carouselSlideLabel');

        if (track) {
            track.style.transform = `translateX(-${slideIndex * 100}%)`;
        }

        if (slideIndex === 0) {
            // Aktifkan tab Financial
            if (tabFinancial) {
                tabFinancial.className = "px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 bg-white text-slate-900 shadow-xs";
            }
            if (tabKitchen) {
                tabKitchen.className = "px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 text-slate-500 hover:text-slate-800";
            }
            if (dot0) {
                dot0.className = "w-6 h-2 rounded-full bg-tealBrand transition-all duration-300 cursor-pointer";
            }
            if (dot1) {
                dot1.className = "w-2 h-2 rounded-full bg-slate-300 transition-all duration-300 cursor-pointer";
            }
            if (label) {
                label.innerText = "Slide 1 dari 2 \u2022 Ringkasan Finansial";
            }
        } else {
            // Aktifkan tab Kitchen
            if (tabFinancial) {
                tabFinancial.className = "px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 text-slate-500 hover:text-slate-800";
            }
            if (tabKitchen) {
                tabKitchen.className = "px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer flex items-center space-x-2 bg-white text-slate-900 shadow-xs";
            }
            if (dot0) {
                dot0.className = "w-2 h-2 rounded-full bg-slate-300 transition-all duration-300 cursor-pointer";
            }
            if (dot1) {
                dot1.className = "w-6 h-2 rounded-full bg-tealBrand transition-all duration-300 cursor-pointer";
            }
            if (label) {
                label.innerText = "Slide 2 dari 2 \u2022 Operasional Dapur";
            }
        }
    }

    function nextKpiCarousel() {
        const nextSlide = (currentKpiSlide + 1) % totalKpiSlides;
        switchKpiCarousel(nextSlide);
    }

    function prevKpiCarousel() {
        const prevSlide = (currentKpiSlide - 1 + totalKpiSlides) % totalKpiSlides;
        switchKpiCarousel(prevSlide);
    }

    // Inisialisasi Gesture Touch Swipe & Mouse Drag pada Carousel Viewport
    document.addEventListener('DOMContentLoaded', function() {
        const viewport = document.getElementById('kpiCarouselViewport');
        if (!viewport) return;

        let startX = 0;
        let isDragging = false;

        // 1. Touch Events (HP / Layar Sentuh)
        viewport.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            isDragging = true;
        }, { passive: true });

        viewport.addEventListener('touchend', function(e) {
            if (!isDragging) return;
            isDragging = false;
            const endX = e.changedTouches[0].clientX;
            const diffX = startX - endX;
            // Ambang batas geser minimal 45px
            if (Math.abs(diffX) > 45) {
                if (diffX > 0) {
                    nextKpiCarousel();
                } else {
                    prevKpiCarousel();
                }
            }
        }, { passive: true });

        // 2. Mouse Drag Events (Desktop / Laptop)
        viewport.addEventListener('mousedown', function(e) {
            startX = e.clientX;
            isDragging = true;
            viewport.classList.add('cursor-grabbing');
            viewport.classList.remove('cursor-grab');
        });

        viewport.addEventListener('mouseup', function(e) {
            if (!isDragging) return;
            isDragging = false;
            viewport.classList.remove('cursor-grabbing');
            viewport.classList.add('cursor-grab');
            const endX = e.clientX;
            const diffX = startX - endX;
            if (Math.abs(diffX) > 45) {
                if (diffX > 0) {
                    nextKpiCarousel();
                } else {
                    prevKpiCarousel();
                }
            }
        });

        viewport.addEventListener('mouseleave', function() {
            if (isDragging) {
                isDragging = false;
                viewport.classList.remove('cursor-grabbing');
                viewport.classList.add('cursor-grab');
            }
        });
    });

    // Custom Dropdown Filter Cabang Popover
    function toggleDashboardBranchDropdown() {
        const menu = document.getElementById('dashboardBranchDropdownMenu');
        const chevron = document.getElementById('dashboardBranchChevron');
        if (menu) {
            menu.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }
    }

    function selectDashboardBranchOption(val, label) {
        document.getElementById('selectedBranchInput').value = val;
        document.getElementById('dashboardFilterForm').submit();
    }

    // Toggle Popover Pemilihan Cabang Komparasi
    function toggleCompareBranchPopover() {
        const popover = document.getElementById('compareBranchPopover');
        const chevron = document.getElementById('compareBranchChevron');
        if (popover) {
            popover.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }
    }

    // Close Dropdowns on Outside Click
    document.addEventListener('click', function(e) {
        // 1. Dashboard Branch Filter Dropdown
        const branchContainer = document.getElementById('dashboardBranchDropdownContainer');
        const branchMenu = document.getElementById('dashboardBranchDropdownMenu');
        const branchChevron = document.getElementById('dashboardBranchChevron');
        if (branchContainer && !branchContainer.contains(e.target)) {
            if (branchMenu) branchMenu.classList.add('hidden');
            if (branchChevron) branchChevron.classList.remove('rotate-180');
        }

        // 2. Compare Branch Popover
        const compareContainer = document.getElementById('compareBranchPopoverContainer');
        const comparePopover = document.getElementById('compareBranchPopover');
        const compareChevron = document.getElementById('compareBranchChevron');
        if (compareContainer && !compareContainer.contains(e.target)) {
            if (comparePopover) comparePopover.classList.add('hidden');
            if (compareChevron) compareChevron.classList.remove('rotate-180');
        }
    });

    // Batasi pilihan checkbox komparasi maksimal 3 cabang dan update label
    function handleCompareBranchChange(checkbox) {
        const checkboxes = document.querySelectorAll('.compare-branch-checkbox:checked');
        if (checkboxes.length > 3) {
            checkbox.checked = false;
            alert('Maksimal hanya dapat memilih 3 cabang untuk dikomparasikan.');
            return;
        }
        const countLabel = document.getElementById('compareCountLabel');
        if (countLabel) {
            countLabel.innerText = checkboxes.length + '/3 Dipilih';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Daily Sales Area Line Chart
        const lineCtx = document.getElementById('dailySalesChart');
        if (lineCtx) {
            const chartLabels = @json($chartLabels);
            const multiLineDatasets = @json($multiLineDatasets);
            const hasCompare = @json($hasCompareBranches);

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartLabels.length > 0 ? chartLabels : ['Belum Ada Data'],
                    datasets: multiLineDatasets.length > 0 ? multiLineDatasets : [{
                        label: 'Omzet Kasir (Rp)',
                        data: [0],
                        borderColor: '#0A97B0',
                        backgroundColor: 'rgba(10, 151, 176, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#0A97B0',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: hasCompare,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11, weight: 'bold' },
                                color: '#334155'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#E0F7FA',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, color: '#94a3b8' }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                color: '#94a3b8',
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(0) + ' Jt';
                                    }
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Payment Channel Donut Chart
        const donutCtx = document.getElementById('paymentDonutChart');
        if (donutCtx) {
            const donutLabels = @json($paymentChartLabels);
            const donutData = @json($paymentChartData);

            paymentDonutChartInstance = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: ['#0A97B0', '#0284c7', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 10,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Multi-Branch Comparison Bar Chart (Finansial & Porsi)
        const compareBarCtx = document.getElementById('branchComparisonBarChart');
        if (compareBarCtx) {
            const compData = @json($comparisonData);
            const branchNames = compData.map(d => d.name);
            const omsetSeries = compData.map(d => d.omset);
            const costSeries = compData.map(d => d.total_cost);
            const marginSeries = compData.map(d => d.gross_margin);

            new Chart(compareBarCtx, {
                type: 'bar',
                data: {
                    labels: branchNames,
                    datasets: [
                        {
                            label: 'Total Omzet (Rp)',
                            data: omsetSeries,
                            backgroundColor: '#0A97B0',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Total Biaya (Rp)',
                            data: costSeries,
                            backgroundColor: '#e11d48',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Gross Margin (Rp)',
                            data: marginSeries,
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11, weight: 'bold' },
                                color: '#334155'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 10,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: 'bold' }, color: '#334155' }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                color: '#94a3b8',
                                callback: function(value) {
                                    if (Math.abs(value) >= 1000000) {
                                        return (value / 1000000).toFixed(0) + ' Jt';
                                    }
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 4. Multi-Branch Portion Comparison Bar Chart
        const comparePortionCtx = document.getElementById('branchComparisonPortionChart');
        if (comparePortionCtx) {
            const compData = @json($comparisonData);
            const branchNames = compData.map(d => d.name);
            const cookedSeries = compData.map(d => d.portions_cooked);
            const soldSeries = compData.map(d => d.portions_sold);

            new Chart(comparePortionCtx, {
                type: 'bar',
                data: {
                    labels: branchNames,
                    datasets: [
                        {
                            label: 'Total Dimasak (Porsi)',
                            data: cookedSeries,
                            backgroundColor: '#0284c7',
                            borderRadius: 6,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Total Terjual (Porsi)',
                            data: soldSeries,
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11, weight: 'bold' },
                                color: '#334155'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 10,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' Porsi';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: 'bold' }, color: '#334155' }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                color: '#94a3b8',
                                callback: function(value) {
                                    return value + ' Porsi';
                                }
                            }
                        }
                    }
                }
            });
        }

        // 5. Multi-Branch Payment Channel Bar Chart
        const comparePaymentCtx = document.getElementById('branchComparisonPaymentChart');
        if (comparePaymentCtx) {
            const compData = @json($comparisonData);
            const branchNames = compData.map(d => d.name);
            const cashSeries = compData.map(d => d.cash || 0);
            const qrisSeries = compData.map(d => d.qris || 0);
            const onlineSeries = compData.map(d => d.online || 0);

            new Chart(comparePaymentCtx, {
                type: 'bar',
                data: {
                    labels: branchNames,
                    datasets: [
                        {
                            label: 'Penjualan Tunai (Rp)',
                            data: cashSeries,
                            backgroundColor: '#0A97B0',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'QRIS / Transfer (Rp)',
                            data: qrisSeries,
                            backgroundColor: '#0284c7',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Online Food (Rp)',
                            data: onlineSeries,
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11, weight: 'bold' },
                                color: '#334155'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 10,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: 'bold' }, color: '#334155' }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                color: '#94a3b8',
                                callback: function(value) {
                                    if (Math.abs(value) >= 1000000) {
                                        return (value / 1000000).toFixed(0) + ' Jt';
                                    }
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // Tab Switcher Grafik Komparasi
    function switchCompareChartTab(tabType) {
        const finContainer = document.getElementById('compareFinChartContainer');
        const portionContainer = document.getElementById('comparePortionChartContainer');
        const paymentContainer = document.getElementById('comparePaymentChartContainer');
        const tabFin = document.getElementById('compareChartTabFin');
        const tabPortion = document.getElementById('compareChartTabPortion');
        const tabPayment = document.getElementById('compareChartTabPayment');

        const activeClass = "px-3 py-1.5 rounded-lg font-extrabold transition-all cursor-pointer bg-white text-slate-900 shadow-2xs";
        const inactiveClass = "px-3 py-1.5 rounded-lg font-extrabold transition-all cursor-pointer text-slate-500 hover:text-slate-800";

        if (finContainer) finContainer.classList.add('hidden');
        if (portionContainer) portionContainer.classList.add('hidden');
        if (paymentContainer) paymentContainer.classList.add('hidden');

        if (tabFin) tabFin.className = inactiveClass;
        if (tabPortion) tabPortion.className = inactiveClass;
        if (tabPayment) tabPayment.className = inactiveClass;

        if (tabType === 'fin') {
            if (finContainer) finContainer.classList.remove('hidden');
            if (tabFin) tabFin.className = activeClass;
        } else if (tabType === 'portion') {
            if (portionContainer) portionContainer.classList.remove('hidden');
            if (tabPortion) tabPortion.className = activeClass;
        } else if (tabType === 'payment') {
            if (paymentContainer) paymentContainer.classList.remove('hidden');
            if (tabPayment) tabPayment.className = activeClass;
        }
    }

    // Global chart instances
    let paymentDonutChartInstance = null;

    // Data Map Pembayaran Cabang untuk Donut Switcher
    const branchPaymentMap = {
        'all': {
            name: 'Semua Cabang',
            cash: @json((float) $totalCash),
            qris: @json((float) $totalQris),
            online: @json((float) $totalOnline),
            total: @json((float) ($totalCash + $totalQris + $totalOnline))
        },
        @if($hasCompareBranches)
            @foreach($comparisonData as $cd)
            '{{ $cd['id'] }}': {
                name: @json($cd['name']),
                cash: @json((float) ($cd['cash'] ?? 0)),
                qris: @json((float) ($cd['qris'] ?? 0)),
                online: @json((float) ($cd['online'] ?? 0)),
                total: @json((float) (($cd['cash'] ?? 0) + ($cd['qris'] ?? 0) + ($cd['online'] ?? 0)))
            },
            @endforeach
        @endif
    };

    function switchPaymentDonut(branchKey) {
        const item = branchPaymentMap[branchKey];
        if (!item) return;

        // Styling Tab
        document.querySelectorAll('.donut-branch-tab').forEach(tab => {
            tab.className = "donut-branch-tab px-2 py-1 rounded-md transition cursor-pointer text-slate-500 hover:text-slate-800 shrink-0";
        });
        const activeTab = document.getElementById('donutTab_' + branchKey);
        if (activeTab) {
            activeTab.className = "donut-branch-tab px-2 py-1 rounded-md transition cursor-pointer bg-white text-slate-900 shadow-2xs shrink-0";
        }

        // Subtitle
        const subtitle = document.getElementById('paymentDonutSubtitle');
        if (subtitle) {
            subtitle.innerText = branchKey === 'all' 
                ? 'Persentase omzet per kanal penerimaan kasir' 
                : 'Proporsi penerimaan: ' + item.name;
        }

        // Hitung persentase
        const total = item.total || 0;
        const pctCash = total > 0 ? ((item.cash / total) * 100).toFixed(1) : 0;
        const pctQris = total > 0 ? ((item.qris / total) * 100).toFixed(1) : 0;
        const pctOnline = total > 0 ? ((item.online / total) * 100).toFixed(1) : 0;

        const valCash = document.getElementById('donutVal_cash');
        const valQris = document.getElementById('donutVal_qris');
        const valOnline = document.getElementById('donutVal_online');

        const labelCash = document.getElementById('donutPct_cash');
        const labelQris = document.getElementById('donutPct_qris');
        const labelOnline = document.getElementById('donutPct_online');

        if (valCash) valCash.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(item.cash);
        if (valQris) valQris.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(item.qris);
        if (valOnline) valOnline.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(item.online);

        if (labelCash) labelCash.innerText = '(' + pctCash + '%)';
        if (labelQris) labelQris.innerText = '(' + pctQris + '%)';
        if (labelOnline) labelOnline.innerText = '(' + pctOnline + '%)';

        // Update Chart
        if (paymentDonutChartInstance) {
            paymentDonutChartInstance.data.datasets[0].data = [item.cash, item.qris, item.online];
            paymentDonutChartInstance.update();
        }
    }
</script>
@endsection
