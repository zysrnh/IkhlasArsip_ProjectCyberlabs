@extends('layouts.app')

@section('title', 'Dashboard Penjualan')

@section('content')
<div class="space-y-6 pb-6">

    <!-- Top Header & Filter Bar Terpadu -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Executive Sales & Kitchen Dashboard</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    Selamat datang, <strong class="text-slate-800 font-bold">{{ auth()->user()->name }}</strong> &mdash; Ringkasan performa penjualan, efisiensi biaya, dan analisis laba kotor.
                </p>
            </div>

            <!-- Integrated Filter Toolbar -->
            <form id="dashboardFilterForm" method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                
                <!-- Tanggal Dari & Sampai -->
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg p-1 space-x-1 text-xs">
                    <div class="flex items-center space-x-1.5 px-2 py-1">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Dari</span>
                        <input 
                            type="date" 
                            name="date_from" 
                            id="filterDateFrom" 
                            value="{{ $dateFrom }}"
                            class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none border-0 p-0 cursor-pointer"
                        >
                    </div>
                    <span class="text-slate-300 font-bold">&ndash;</span>
                    <div class="flex items-center space-x-1.5 px-2 py-1">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Sampai</span>
                        <input 
                            type="date" 
                            name="date_to" 
                            id="filterDateTo" 
                            value="{{ $dateTo }}"
                            class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none border-0 p-0 cursor-pointer"
                        >
                    </div>
                </div>

                <!-- Dropdown Cabang -->
                @if(auth()->user()->canAccessAllBranches() || auth()->user()->isKepalaCabang())
                    <div class="min-w-[180px]">
                        <select 
                            name="branch_id" 
                            id="filterBranchId"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-tealBrand cursor-pointer"
                        >
                            <option value="">Semua Cabang (Global)</option>
                            @foreach($allBranches as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="inline-flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                        <span>{{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
                    </div>
                @endif

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="bg-tealBrand hover:bg-teal-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition-colors shadow-xs cursor-pointer flex items-center space-x-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    <span>Terapkan</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Section: 8 KPI Cards Terstandarisasi & Konsisten -->
    <div class="space-y-4">
        
        <!-- Header Baris 1: Finansial -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                <h2 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Ringkasan Finansial Utama</h2>
            </div>
            <span class="text-[11px] font-semibold text-slate-400">
                Periode: {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
            </span>
        </div>

        <!-- Grid Baris 1: 4 Cards Finansial -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Omzet Kasir -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Omzet Kasir</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-tealBrand flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Penjualan Lauk</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($totalSalesFood, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 2: Total Biaya Operasional -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Biaya Operasional</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-2xl font-black text-rose-600 tracking-tight font-sans">
                        Rp {{ number_format($totalCost, 0, ',', '.') }}
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Tetap / Belanja</span>
                    <span class="font-bold text-slate-700">
                        Rp {{ number_format($totalFixedCost, 0, ',', '.') }} / Rp {{ number_format($totalDailyExpense, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Card 3: Gross Margin -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Gross Margin (Laba)</span>
                    <div class="w-8 h-8 rounded-lg {{ $grossMargin >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                </div>
                <div class="my-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black {{ $grossMargin >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight font-sans">
                        Rp {{ number_format($grossMargin, 0, ',', '.') }}
                    </div>
                    <span class="text-xs font-black px-2 py-0.5 rounded {{ $grossMargin >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ $grossMarginPercent }}%
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Kalkulasi</span>
                    <span class="font-bold text-slate-700">Omzet &minus; Total Biaya</span>
                </div>
            </div>

            <!-- Card 4: Summary Selisih Kasir -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Summary Selisih Kasir</span>
                    <div class="w-8 h-8 rounded-lg {{ $surplusKasir > 0 ? 'bg-emerald-50 text-emerald-600' : ($surplusKasir < 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600') }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <div class="my-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black {{ $surplusKasir > 0 ? 'text-emerald-600' : ($surplusKasir < 0 ? 'text-rose-600' : 'text-slate-800') }} tracking-tight font-sans">
                        {{ $surplusKasir < 0 ? '-' : ($surplusKasir > 0 ? '+' : '') }}Rp {{ number_format(abs($surplusKasir), 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded {{ $surplusKasir > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($surplusKasir < 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                        {{ $surplusKasir > 0 ? 'Surplus' : ($surplusKasir < 0 ? 'Defisit' : 'Seimbang') }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Status Setoran</span>
                    <span class="font-bold text-slate-700">
                        {{ $surplusKasir > 0 ? 'Uang Lebih' : ($surplusKasir < 0 ? 'Uang Kurang' : 'Klop Sesuai') }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Grid Baris 2: 4 Cards Operasional Dapur -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 5: Total Laporan Dapur -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Laporan Dapur</span>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                        {{ number_format($totalReports, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Laporan</span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Rata-rata Omzet / Hari</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($avgDailyOmset, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 6: Total Porsi Terjual -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Porsi Terjual</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                        {{ number_format($totalPortionsSold, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Porsi</span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Total Masak</span>
                    <span class="font-bold text-slate-700">{{ number_format($totalPortionsCooked, 0, ',', '.') }} Porsi</span>
                </div>
            </div>

            <!-- Card 7: Lauk Terbuang / Basi -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Lauk Terbuang / Basi</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-2xl font-black text-rose-600 tracking-tight font-sans">
                        Rp {{ number_format($totalWastedFood, 0, ',', '.') }}
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Sisa Layak Jual</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($totalRemainingSellable, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 8: Top Cabang Penjualan -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Top Cabang Penjualan</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                </div>
                <div class="my-3">
                    <div class="text-xl font-black text-slate-900 tracking-tight truncate" title="{{ $topBranchName }}">
                        {{ $topBranchName }}
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2.5 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">Omzet Tertinggi</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($topBranchAmount, 0, ',', '.') }}</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Section: Komparasi Kinerja Antar Cabang (1-3 Cabang) -->
    @if(auth()->user()->canAccessAllBranches() || (auth()->user()->isKepalaCabang() && count($allBranches) > 1))
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs">
            
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Perbandingan Kinerja Antar Cabang</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-tealBrand border border-teal-100 uppercase">Komparasi 1-3 Cabang</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih maksimal 3 cabang untuk membandingkan Omzet, Biaya Operasional, Gross Margin, dan Efisiensi Kasir secara berdampingan.</p>
                </div>

                <!-- Form Pemilihan Cabang (Pill Checkboxes) -->
                <form id="branchComparisonForm" method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    @if($selectedBranchId)
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @endif

                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($allBranches as $b)
                            @php
                                $isChecked = in_array($b->id, $compareBranchIds);
                            @endphp
                            <label class="inline-flex items-center space-x-1.5 px-2.5 py-1.5 rounded-lg border text-xs font-bold cursor-pointer transition-colors select-none {{ $isChecked ? 'bg-teal-50 border-tealBrand text-teal-900' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                <input 
                                    type="checkbox" 
                                    name="compare_branch_ids[]" 
                                    value="{{ $b->id }}"
                                    class="compare-branch-checkbox rounded text-tealBrand focus:ring-0 focus:ring-offset-0 cursor-pointer"
                                    {{ $isChecked ? 'checked' : '' }}
                                    onchange="handleCompareBranchChange(this)"
                                >
                                <span>{{ $b->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <button 
                        type="submit" 
                        class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer"
                    >
                        Bandingkan
                    </button>
                </form>
            </div>

            <!-- Comparison Cards Grid (Side-by-Side 1-3 Kolom) -->
            @if(count($comparisonData) > 0)
                <div class="grid grid-cols-1 md:grid-cols-{{ count($comparisonData) }} gap-4 mb-6">
                    @foreach($comparisonData as $cd)
                        <div class="bg-slate-50/70 rounded-xl border border-slate-200 p-4 flex flex-col justify-between space-y-3">
                            <!-- Header Cabang -->
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                                <div class="font-black text-sm text-slate-900">{{ $cd['name'] }}</div>
                                <span class="text-[10px] font-bold bg-white border border-slate-200 px-2 py-0.5 rounded text-slate-600">
                                    {{ $cd['total_reports'] }} Laporan
                                </span>
                            </div>

                            <!-- List Metrik -->
                            <div class="space-y-2 text-xs divide-y divide-slate-200/60">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-slate-500">Total Omzet Kasir</span>
                                    <span class="font-black text-slate-900 font-sans">Rp {{ number_format($cd['omset'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="text-slate-500">Total Biaya Operasional</span>
                                    <span class="font-black text-rose-600 font-sans">Rp {{ number_format($cd['total_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="text-slate-500 pl-2">&bull; Biaya Tetap Cabang</span>
                                    <span class="font-semibold text-slate-700">Rp {{ number_format($cd['fixed_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="text-slate-500 pl-2">&bull; Belanja Dapur</span>
                                    <span class="font-semibold text-slate-700">Rp {{ number_format($cd['daily_expense'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="font-bold text-slate-800">Gross Margin</span>
                                    <div class="text-right">
                                        <span class="font-black {{ $cd['gross_margin'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            Rp {{ number_format($cd['gross_margin'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $cd['gross_margin'] >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} ml-1">
                                            {{ $cd['gross_margin_percent'] }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="text-slate-500">Summary Selisih Kasir</span>
                                    <span class="font-bold {{ $cd['surplus_kasir'] > 0 ? 'text-emerald-600' : ($cd['surplus_kasir'] < 0 ? 'text-rose-600' : 'text-slate-700') }}">
                                        {{ $cd['surplus_kasir'] < 0 ? '-' : ($cd['surplus_kasir'] > 0 ? '+' : '') }}Rp {{ number_format(abs($cd['surplus_kasir']), 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-1.5">
                                    <span class="text-slate-500">Porsi Terjual / Dimasak</span>
                                    <span class="font-bold text-slate-800">
                                        {{ number_format($cd['portions_sold'], 0, ',', '.') }} / {{ number_format($cd['portions_cooked'], 0, ',', '.') }} Porsi
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Comparison Bar Chart Container -->
                <div class="relative h-64 sm:h-72 w-full pt-2">
                    <canvas id="branchComparisonBarChart"></canvas>
                </div>
            @else
                <div class="text-center py-6 text-xs text-slate-400">
                    Silakan pilih setidaknya 1 cabang di atas untuk melihat perbandingan.
                </div>
            @endif
        </div>
    @endif

    <!-- Section: Grafik Tren Omzet Harian & Komposisi Metode Pembayaran -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Tren Penjualan Harian (Line Chart 8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-2 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Tren Omzet Penjualan Harian</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-tealBrand border border-teal-100">Live Analytics</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dinamika omzet kasir harian sepanjang rentang waktu terpilih</p>
                </div>
                <div class="text-xs text-slate-600 font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 self-start sm:self-auto">
                    {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                </div>
            </div>

            <div class="relative h-64 sm:h-72 w-full pt-2">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Komposisi Pembayaran Kasir (Donut Chart 4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900 tracking-tight">Metode Pembayaran Kasir</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Persentase omzet per kanal penerimaan kasir</p>
            </div>

            <div class="relative h-44 w-full my-auto flex items-center justify-center pt-2">
                <canvas id="paymentDonutChart"></canvas>
            </div>

            <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                @foreach($paymentSummaries as $typeName => $tData)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tData['color'] }};"></span>
                            <span class="text-slate-600 font-medium text-[11px]">{{ $typeName }}</span>
                        </div>
                        <span class="font-bold text-slate-800 text-[11px]">Rp {{ number_format($tData['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Section: Top 5 Menu Masakan & Kontribusi Cabang -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Top 5 Menu Terlaris (6 Cols) -->
        <div class="lg:col-span-6 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Top 5 Menu Masakan Terlaris</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Menu masakan dengan porsi terjual dan omzet tertinggi</p>
                </div>
                <span class="text-[10px] font-bold text-tealBrand uppercase tracking-wider bg-teal-50 px-2 py-1 rounded border border-teal-100">Leaderboard</span>
            </div>

            <div class="space-y-2.5">
                @forelse($topMenus as $index => $item)
                    <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
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
        <div class="lg:col-span-6 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Performa & Kontribusi Cabang</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Perbandingan omzet seluruh unit cabang aktif</p>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ count($branchComparisons) }} Cabang</span>
            </div>

            <div class="space-y-3.5">
                @forelse($branchComparisons as $index => $bc)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded bg-navy-900 text-white flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-800">{{ $bc['name'] }}</span>
                            </div>
                            <span class="text-[11px] text-slate-600 font-semibold font-sans">
                                <strong class="text-slate-900 font-bold">Rp {{ number_format($bc['amount'], 0, ',', '.') }}</strong> ({{ $bc['count'] }} lap)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded h-2 overflow-hidden">
                            <div class="bg-tealBrand h-2 rounded transition-all duration-500 ease-out" style="width: {{ $bc['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-6 text-center">Belum ada data cabang aktif pada rentang ini.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Section: Laporan Dapur Terkini -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-black text-slate-900 tracking-tight">Laporan Dapur Harian Terbaru</h3>
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
                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                        <th class="py-3 px-3 rounded-l-lg">TANGGAL</th>
                        <th class="py-3 px-3">CABANG</th>
                        <th class="py-3 px-3">PIC INPUT</th>
                        <th class="py-3 px-3 text-right">PENJUALAN LAUK</th>
                        <th class="py-3 px-3 text-right">OMZET KASIR</th>
                        <th class="py-3 px-3 text-right">SELISIH</th>
                        <th class="py-3 px-3 text-center rounded-r-lg">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentReports as $report)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900 font-mono">{{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($report->report_date)->isoFormat('dddd') }}</div>
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $report->branch->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-700">
                                {{ $report->user->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900 font-sans">
                                Rp {{ number_format($report->grand_total_sales, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-black font-sans text-emerald-600">
                                Rp {{ number_format($report->total_omset, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold font-sans {{ $report->difference_amount < 0 ? 'text-rose-600' : ($report->difference_amount > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                {{ $report->difference_amount < 0 ? '-' : ($report->difference_amount > 0 ? '+' : '') }}Rp {{ number_format(abs($report->difference_amount), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('kitchen-reports.show', $report) }}" class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
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
    // Batasi pilihan checkbox komparasi maksimal 3 cabang
    function handleCompareBranchChange(checkbox) {
        const checkboxes = document.querySelectorAll('.compare-branch-checkbox:checked');
        if (checkboxes.length > 3) {
            checkbox.checked = false;
            alert('Maksimal hanya dapat memilih 3 cabang untuk dikomparasikan.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Daily Sales Area Line Chart
        const lineCtx = document.getElementById('dailySalesChart');
        if (lineCtx) {
            const chartLabels = @json($chartLabels);
            const chartAmounts = @json($chartAmounts);

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartLabels.length > 0 ? chartLabels : ['Belum Ada Data'],
                    datasets: [{
                        label: 'Omzet Kasir (Rp)',
                        data: chartAmounts.length > 0 ? chartAmounts : [0],
                        borderColor: '#0A97B0',
                        backgroundColor: 'rgba(10, 151, 176, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0A97B0',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#E0F7FA',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 10,
                            cornerRadius: 8,
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

            new Chart(donutCtx, {
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
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 8,
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

        // 3. Multi-Branch Comparison Bar Chart
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
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Total Biaya (Rp)',
                            data: costSeries,
                            backgroundColor: '#e11d48',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Gross Margin (Rp)',
                            data: marginSeries,
                            backgroundColor: '#10b981',
                            borderRadius: 4,
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
                            cornerRadius: 8,
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
</script>
@endsection
