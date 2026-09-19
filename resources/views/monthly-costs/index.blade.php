@extends('layouts.app')

@section('title', 'Cost Cabang (Biaya Bulanan & Belanja)')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cost Cabang</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Pencatatan biaya operasional tetap bulanan dan rekapitulasi total belanja operasional cabang.
            </p>
        </div>
        @if(!auth()->user()->isViewer())
        <button 
            type="button" 
            onclick="openCreateModal()" 
            class="inline-flex items-center justify-center space-x-1.5 px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-colors cursor-pointer"
        >
            <span class="text-sm leading-none">+</span>
            <span>Catat Cost Bulanan</span>
        </button>
        @endif
    </div>

    <!-- 3 Stat KPI Cards (Biaya Bulanan, Belanja Harian, Grand Total) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        
        <!-- Card 1: Total Biaya Tetap Bulanan -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm border-l-4 border-l-tealBrand">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">BIAYA BULANAN (FIXED)</span>
                <span class="text-[9px] font-extrabold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full border border-teal-200/80">Rutin</span>
            </div>
            <div class="text-lg sm:text-xl font-black text-slate-900 font-mono tracking-tight">
                Rp {{ number_format($statsMonthlyTotal, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">
                Sewa, Wifi, Sampah, Air & Listrik, Netflix, IPL, Gaji.
            </p>
        </div>

        <!-- Card 2: Total Belanja Harian -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">TOTAL BELANJA HARIAN</span>
                <span class="text-[9px] font-extrabold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full border border-amber-200/80">Operasional</span>
            </div>
            <div class="text-lg sm:text-xl font-black text-slate-900 font-mono tracking-tight">
                Rp {{ number_format($statsDailyTotal, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">
                Bahan baku, non-bahan baku, dan kebutuhan harian.
            </p>
        </div>

        <!-- Card 3: Grand Total Keseluruhan Pengeluaran -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm border-l-4 border-l-navy-900">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">GRAND TOTAL PENGELUARAN</span>
                <span class="text-[9px] font-extrabold bg-navy-900 text-white px-2 py-0.5 rounded-full">Bulanan + Harian</span>
            </div>
            <div class="text-lg sm:text-xl font-black text-tealBrand font-mono tracking-tight">
                Rp {{ number_format($statsGrandTotal, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">
                Akumulasi seluruh biaya cabang pada periode terpilih.
            </p>
        </div>

    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm relative z-30">
        <form method="GET" action="{{ route('monthly-costs.index') }}" id="costFilterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ request('branch_id') }}">
            <input type="hidden" name="month" id="filterMonthInput" value="{{ request('month') }}">
            <input type="hidden" name="year" id="filterYearInput" value="{{ request('year') }}">

            <!-- 1. Custom Dropdown Filter Cabang -->
            <div class="relative" id="filterBranchDropdownContainer">
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">CABANG</label>
                <button 
                    type="button" 
                    onclick="toggleFilterPopover('filterBranchMenu', 'filterBranchChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                >
                    <span id="filterBranchLabel" class="truncate">
                        @if(request('branch_id'))
                            {{ $branches->firstWhere('id', request('branch_id'))->name ?? 'Semua Cabang' }}
                        @else
                            Semua Cabang
                        @endif
                    </span>
                    <svg id="filterBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>

                <div id="filterBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-40 max-h-52 overflow-y-auto animate-fadeIn">
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

            <!-- 2. Custom Dropdown Filter Bulan -->
            <div class="relative" id="filterMonthDropdownContainer">
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">BULAN</label>
                <button 
                    type="button" 
                    onclick="toggleFilterPopover('filterMonthMenu', 'filterMonthChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                >
                    <span id="filterMonthLabel" class="truncate">
                        @if(request('month') && isset($monthNames[request('month')]))
                            {{ $monthNames[request('month')] }}
                        @else
                            Semua Bulan
                        @endif
                    </span>
                    <svg id="filterMonthChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>

                <div id="filterMonthMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-40 max-h-52 overflow-y-auto animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectFilterMonth('', 'Semua Bulan')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty(request('month')) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Bulan</span>
                        @if(empty(request('month')))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    @foreach($monthNames as $mNum => $mName)
                        <button 
                            type="button" 
                            onclick="selectFilterMonth('{{ $mNum }}', '{{ $mName }}')"
                            class="w-full text-left px-3.5 py-1.5 text-xs flex items-center justify-between transition-colors {{ request('month') == $mNum ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $mName }}</span>
                            @if(request('month') == $mNum)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Custom Dropdown Filter Tahun -->
            <div class="relative" id="filterYearDropdownContainer">
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">TAHUN</label>
                <button 
                    type="button" 
                    onclick="toggleFilterPopover('filterYearMenu', 'filterYearChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                >
                    <span id="filterYearLabel" class="truncate">
                        @if(request('year'))
                            {{ request('year') }}
                        @else
                            Semua Tahun
                        @endif
                    </span>
                    <svg id="filterYearChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>

                <div id="filterYearMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-40 max-h-52 overflow-y-auto animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectFilterYear('', 'Semua Tahun')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty(request('year')) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Tahun</span>
                        @if(empty(request('year')))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    @foreach($years as $yr)
                        <button 
                            type="button" 
                            onclick="selectFilterYear('{{ $yr }}', '{{ $yr }}')"
                            class="w-full text-left px-3.5 py-1.5 text-xs flex items-center justify-between transition-colors {{ request('year') == $yr ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $yr }}</span>
                            @if(request('year') == $yr)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Filter Reset / Aksi -->
            <div class="flex items-end space-x-2">
                @if(request()->hasAny(['branch_id', 'month', 'year']))
                    <a 
                        href="{{ route('monthly-costs.index') }}" 
                        class="w-full px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1 cursor-pointer"
                        title="Reset Filter"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span>Reset Filter</span>
                    </a>
                @else
                    <div class="w-full px-3.5 py-2.5 bg-slate-50 text-slate-400 rounded-xl text-xs font-semibold flex items-center justify-center border border-dashed border-slate-200">
                        Filter Aktif
                    </div>
                @endif
            </div>

        </form>
    </div>

    <!-- Data Container: Desktop Table & Mobile Cards -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Rincian Cost Cabang & Belanja Harian</h3>
            <span class="text-xs text-slate-400 font-medium font-mono">Total: {{ $costs->total() }} Periode</span>
        </div>

        <!-- 1. Mobile Cards View (< md) -->
        <div class="block md:hidden space-y-3">
            @forelse($costs as $cost)
                <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200/90 rounded-xl p-3.5 space-y-2.5 transition-all">
                    
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-900">{{ $cost->branch->name }}</div>
                            <div class="text-[11px] text-tealBrand font-bold mt-0.5">{{ $cost->period_name }}</div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded text-[9.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $cost->year }}
                        </span>
                    </div>

                    <div class="space-y-1.5 pt-2 border-t border-slate-200/60 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Biaya Bulanan (Fixed):</span>
                            <span class="font-bold font-mono text-slate-900">Rp {{ number_format($cost->total_monthly_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Belanja Harian:</span>
                            <span class="font-bold font-mono text-amber-700">Rp {{ number_format($cost->daily_expense_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-900 pt-1 border-t border-slate-200 font-bold">
                            <span>Grand Total:</span>
                            <span class="font-black font-mono text-tealBrand text-sm">Rp {{ number_format($cost->grand_total_cost, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-end space-x-2">
                        <button 
                            type="button" 
                            onclick="openDetailModal({{ json_encode($cost) }})"
                            class="px-2.5 py-1 bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                        >
                            Detail Rekap
                        </button>
                        @if(!auth()->user()->isViewer())
                            <button 
                                type="button" 
                                onclick="openEditModal({{ json_encode($cost) }})"
                                class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                            >
                                Edit
                            </button>
                            <form action="{{ route('monthly-costs.destroy', $cost->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Cost Cabang?', text: 'Data cost cabang {{ $cost->branch->name }} periode {{ $cost->period_name }} akan dihapus.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus', form: this });" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="px-2.5 py-1 bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                                >
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Belum ada data cost bulanan cabang untuk filter yang dipilih.
                </div>
            @endforelse
        </div>

        <!-- 2. Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3.5 w-12 text-center">#</th>
                        <th class="py-3 px-3.5">Cabang</th>
                        <th class="py-3 px-3.5">Periode</th>
                        <th class="py-3 px-3.5 text-right">Biaya Bulanan (Fixed)</th>
                        <th class="py-3 px-3.5 text-right">Belanja Harian</th>
                        <th class="py-3 px-3.5 text-right">Grand Total Pengeluaran</th>
                        <th class="py-3 px-3.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($costs as $index => $cost)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-3.5 text-center font-mono text-slate-400">
                                {{ $costs->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-3.5 font-bold text-slate-900">
                                {{ $cost->branch->name }}
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-50 text-teal-800 border border-teal-200/80">
                                    {{ $cost->period_name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($cost->total_monthly_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-mono font-bold text-amber-700">
                                Rp {{ number_format($cost->daily_expense_total, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-mono font-black text-tealBrand text-sm">
                                Rp {{ number_format($cost->grand_total_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    
                                    <!-- Detail Rekap Button -->
                                    <button 
                                        type="button" 
                                        onclick="openDetailModal({{ json_encode($cost) }})"
                                        class="p-1 text-slate-400 hover:text-tealBrand transition-colors cursor-pointer"
                                        title="Lihat Detail Rekapitulasi Biaya & Belanja"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @if(!auth()->user()->isViewer())
                                        <!-- Edit Button -->
                                        <button 
                                            type="button" 
                                            onclick="openEditModal({{ json_encode($cost) }})"
                                            class="p-1 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
                                            title="Edit Cost Bulanan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('monthly-costs.destroy', $cost->id) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Cost Cabang?', text: 'Data cost cabang {{ $cost->branch->name }} periode {{ $cost->period_name }} akan dihapus.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus', form: this });" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                                title="Hapus Data"
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
                                Belum ada data cost bulanan cabang untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($costs->hasPages())
            <div class="pt-5 border-t border-slate-100">
                {{ $costs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Catat Cost Bulanan Baru -->
<div id="createModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Catat Cost Bulanan Cabang</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Input biaya operasional rutin bulanan cabang.</p>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('monthly-costs.store') }}" method="POST" id="createForm" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf

            <!-- Cabang, Bulan, Tahun (Custom Popover Dropdowns) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 relative z-20">
                
                <!-- 1. Custom Dropdown Cabang -->
                <div class="relative" id="createBranchDropdownContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">CABANG</label>
                    <input type="hidden" name="branch_id" id="create_branch_id" value="{{ $selectedBranchId ?: ($branches->first()->id ?? '') }}">
                    
                    <button 
                        type="button" 
                        onclick="toggleCreatePopover('createBranchMenu', 'createBranchChevron')"
                        class="w-full px-3 py-2 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-lg text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="createBranchText" class="truncate">
                            {{ $branches->firstWhere('id', $selectedBranchId)->name ?? ($branches->first()->name ?? '-- Pilih Cabang --') }}
                        </span>
                        <svg id="createBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div id="createBranchMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 max-h-48 overflow-y-auto animate-fadeIn">
                        @foreach($branches as $branch)
                            <button 
                                type="button" 
                                onclick="selectCreateBranch('{{ $branch->id }}', '{{ $branch->name }}')"
                                class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-teal-50 hover:text-teal-900 font-medium transition-colors flex items-center justify-between"
                            >
                                <span>{{ $branch->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Custom Dropdown Bulan -->
                <div class="relative" id="createMonthDropdownContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BULAN</label>
                    <input type="hidden" name="month" id="create_month" value="{{ (int) date('n') }}">
                    
                    <button 
                        type="button" 
                        onclick="toggleCreatePopover('createMonthMenu', 'createMonthChevron')"
                        class="w-full px-3 py-2 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-lg text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="createMonthText" class="truncate">{{ $monthNames[(int) date('n')] ?? 'Januari' }}</span>
                        <svg id="createMonthChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div id="createMonthMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 max-h-52 overflow-y-auto animate-fadeIn">
                        @foreach($monthNames as $mNum => $mName)
                            <button 
                                type="button" 
                                onclick="selectCreateMonth('{{ $mNum }}', '{{ $mName }}')"
                                class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-teal-50 hover:text-teal-900 font-medium transition-colors flex items-center justify-between"
                            >
                                <span>{{ $mName }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Custom Dropdown Tahun -->
                <div class="relative" id="createYearDropdownContainer">
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">TAHUN</label>
                    <input type="hidden" name="year" id="create_year" value="{{ (int) date('Y') }}">
                    
                    <button 
                        type="button" 
                        onclick="toggleCreatePopover('createYearMenu', 'createYearChevron')"
                        class="w-full px-3 py-2 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-lg text-slate-800 font-medium flex items-center justify-between transition cursor-pointer"
                    >
                        <span id="createYearText" class="truncate">{{ (int) date('Y') }}</span>
                        <svg id="createYearChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div id="createYearMenu" class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 max-h-48 overflow-y-auto animate-fadeIn">
                        @foreach($years as $yr)
                            <button 
                                type="button" 
                                onclick="selectCreateYear('{{ $yr }}', '{{ $yr }}')"
                                class="w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-teal-50 hover:text-teal-900 font-medium transition-colors flex items-center justify-between"
                            >
                                <span>{{ $yr }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Existing Data Alert Banner -->
            <div id="createExistingNotice" class="hidden p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs font-medium flex items-center justify-between animate-fadeIn">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Data untuk periode ini sudah pernah diinput. Form otomatis memuat data yang ada untuk diperbarui.</span>
                </div>
            </div>

            <!-- 8 Rincian Biaya -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                
                <!-- 1. Sewa Lokasi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">SEWA LOKASI</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="rent_cost" id="create_rent_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 2. Biaya Wifi (Internet) -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA WIFI (INTERNET)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="wifi_cost" id="create_wifi_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 3. Biaya Sampah -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA SAMPAH</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="trash_cost" id="create_trash_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 4. Biaya Air & Listrik -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA AIR & LISTRIK</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="utilities_cost" id="create_utilities_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 5. Biaya Netflix -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA NETFLIX</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="netflix_cost" id="create_netflix_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 6. IPL -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">IPL (IURAN PENGELOLAAN)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="ipl_cost" id="create_ipl_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 7. Gaji Karyawan -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">GAJI KARYAWAN</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="salary_cost" id="create_salary_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 8. Biaya Lainnya -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA LAINNYA</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="other_cost" id="create_other_cost" placeholder="0" oninput="formatCurrency(this); recalculateCreateTotal();" class="cost-create-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

            </div>

            <!-- Subtotal Live Banner -->
            <div class="bg-teal-50/70 border border-teal-200/80 p-3 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-teal-900">Total Biaya Bulanan:</span>
                <span id="createTotalPreview" class="text-sm font-black text-tealBrand font-mono">Rp 0</span>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">CATATAN / KETERANGAN</label>
                <textarea name="notes" id="create_notes" rows="2" placeholder="Catatan tambahan bila ada..." class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand"></textarea>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="createSubmitBtn" class="px-5 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                    Simpan Cost Cabang
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Modal Edit Cost Bulanan -->
<div id="editModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Edit Cost Bulanan Cabang</h3>
                <p id="editPeriodLabel" class="text-[11px] text-tealBrand font-bold mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            @csrf
            @method('PUT')

            <!-- 8 Rincian Biaya -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                
                <!-- 1. Sewa Lokasi -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">SEWA LOKASI</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="rent_cost" id="edit_rent_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 2. Biaya Wifi (Internet) -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA WIFI (INTERNET)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="wifi_cost" id="edit_wifi_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 3. Biaya Sampah -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA SAMPAH</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="trash_cost" id="edit_trash_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 4. Biaya Air & Listrik -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA AIR & LISTRIK</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="utilities_cost" id="edit_utilities_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 5. Biaya Netflix -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA NETFLIX</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="netflix_cost" id="edit_netflix_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 6. IPL -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">IPL (IURAN PENGELOLAAN)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="ipl_cost" id="edit_ipl_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 7. Gaji Karyawan -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">GAJI KARYAWAN</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="salary_cost" id="edit_salary_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

                <!-- 8. Biaya Lainnya -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">BIAYA LAINNYA</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" name="other_cost" id="edit_other_cost" placeholder="0" oninput="formatCurrency(this); recalculateEditTotal();" class="cost-edit-input w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-mono font-bold focus:outline-none focus:border-tealBrand">
                    </div>
                </div>

            </div>

            <!-- Subtotal Live Banner -->
            <div class="bg-teal-50/70 border border-teal-200/80 p-3 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-teal-900">Total Biaya Bulanan:</span>
                <span id="editTotalPreview" class="text-sm font-black text-tealBrand font-mono">Rp 0</span>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-500 tracking-wider mb-1">CATATAN / KETERANGAN</label>
                <textarea name="notes" id="edit_notes" rows="2" placeholder="Catatan tambahan bila ada..." class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand"></textarea>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                    Perbarui Cost Cabang
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Modal Detail Rekapitulasi Komprehensif (Bulanan + Belanja Harian) -->
<div id="detailModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-visible animate-fadeIn">
        
        <!-- Header -->
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0 rounded-t-2xl">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Detail Rekap Pengeluaran Cabang</h3>
                <p id="detailBranchPeriodLabel" class="text-[11px] text-tealBrand font-bold mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            
            <!-- Section 1: Rincian Biaya Tetap Bulanan -->
            <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">1. BIAYA TETAP BULANAN (FIXED COST)</span>
                    <span id="detailSubtotalMonthly" class="text-xs font-black font-mono text-tealBrand">Rp 0</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-[11px]">
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Sewa Lokasi</span>
                        <span id="detail_rent" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Wifi (Internet)</span>
                        <span id="detail_wifi" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Sampah</span>
                        <span id="detail_trash" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Air & Listrik</span>
                        <span id="detail_utilities" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Netflix</span>
                        <span id="detail_netflix" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">IPL</span>
                        <span id="detail_ipl" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Gaji Karyawan</span>
                        <span id="detail_salary" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Biaya Lainnya</span>
                        <span id="detail_other" class="font-bold font-mono text-slate-800">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Rincian Belanja Harian di Bulan yang Sama -->
            <div class="bg-amber-50/40 border border-amber-200/80 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-amber-200/60 pb-2">
                    <span class="text-xs font-extrabold text-amber-950 uppercase tracking-wider">2. TOTAL BELANJA HARIAN BULAN INI</span>
                    <span id="detailSubtotalDaily" class="text-xs font-black font-mono text-amber-800">Rp 0</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-[11px]">
                    <div class="bg-white p-2.5 rounded-lg border border-amber-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Bahan Baku Dapur</span>
                        <span id="detail_daily_raw" class="font-bold font-mono text-slate-900 text-xs">Rp 0</span>
                    </div>
                    <div class="bg-white p-2.5 rounded-lg border border-amber-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Non Bahan Baku / Ops</span>
                        <span id="detail_daily_non_raw" class="font-bold font-mono text-slate-900 text-xs">Rp 0</span>
                    </div>
                    <div class="bg-white p-2.5 rounded-lg border border-amber-200/60">
                        <span class="text-slate-400 text-[10px] font-bold block uppercase">Kebutuhan Pribadi</span>
                        <span id="detail_daily_personal" class="font-bold font-mono text-slate-900 text-xs">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Grand Total Keseluruhan -->
            <div class="bg-navy-900 text-white p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider block text-slate-300">TOTAL SELURUH PENGELUARAN CABANG</span>
                    <span class="text-[11px] text-slate-400">Akumulasi Biaya Bulanan + Belanja Harian</span>
                </div>
                <span id="detailGrandTotal" class="text-base sm:text-lg font-black font-mono text-tealBrand">Rp 0</span>
            </div>

            <!-- Keterangan -->
            <div id="detailNotesContainer" class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="font-bold text-slate-700 block mb-0.5">Catatan:</span>
                <span id="detailNotes">-</span>
            </div>

        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end rounded-b-2xl">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '';
            return;
        }
        input.value = new Intl.NumberFormat('id-ID').format(value);
    }

    function parseFormattedValue(val) {
        if (!val) return 0;
        return parseFloat(val.toString().replace(/\./g, '')) || 0;
    }

    function recalculateCreateTotal() {
        let sum = 0;
        document.querySelectorAll('.cost-create-input').forEach(inp => {
            sum += parseFormattedValue(inp.value);
        });
        document.getElementById('createTotalPreview').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sum);
    }

    function recalculateEditTotal() {
        let sum = 0;
        document.querySelectorAll('.cost-edit-input').forEach(inp => {
            sum += parseFormattedValue(inp.value);
        });
        document.getElementById('editTotalPreview').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sum);
    }

    // Custom Dropdown Popover Logic for Filter Bar
    function toggleFilterPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const allMenus = ['filterBranchMenu', 'filterMonthMenu', 'filterYearMenu'];
        const allChevrons = ['filterBranchChevron', 'filterMonthChevron', 'filterYearChevron'];

        allMenus.forEach((id, idx) => {
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

    function selectFilterBranch(id, name) {
        document.getElementById('filterBranchInput').value = id;
        document.getElementById('filterBranchLabel').textContent = name;
        document.getElementById('filterBranchMenu').classList.add('hidden');
        const ch = document.getElementById('filterBranchChevron');
        if (ch) ch.classList.remove('rotate-180');
        document.getElementById('costFilterForm').submit();
    }

    function selectFilterMonth(mNum, mName) {
        document.getElementById('filterMonthInput').value = mNum;
        document.getElementById('filterMonthLabel').textContent = mName;
        document.getElementById('filterMonthMenu').classList.add('hidden');
        const ch = document.getElementById('filterMonthChevron');
        if (ch) ch.classList.remove('rotate-180');
        document.getElementById('costFilterForm').submit();
    }

    function selectFilterYear(yr, yrLabel) {
        document.getElementById('filterYearInput').value = yr;
        document.getElementById('filterYearLabel').textContent = yrLabel;
        document.getElementById('filterYearMenu').classList.add('hidden');
        const ch = document.getElementById('filterYearChevron');
        if (ch) ch.classList.remove('rotate-180');
        document.getElementById('costFilterForm').submit();
    }

    // Custom Dropdown Popover Logic for Create Modal
    function toggleCreatePopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const allMenus = ['createBranchMenu', 'createMonthMenu', 'createYearMenu'];
        const allChevrons = ['createBranchChevron', 'createMonthChevron', 'createYearChevron'];

        allMenus.forEach((id, idx) => {
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

    function selectCreateBranch(id, name) {
        document.getElementById('create_branch_id').value = id;
        document.getElementById('createBranchText').textContent = name;
        document.getElementById('createBranchMenu').classList.add('hidden');
        const ch = document.getElementById('createBranchChevron');
        if (ch) ch.classList.remove('rotate-180');
        checkAndLoadExistingCost();
    }

    function selectCreateMonth(mNum, mName) {
        document.getElementById('create_month').value = mNum;
        document.getElementById('createMonthText').textContent = mName;
        document.getElementById('createMonthMenu').classList.add('hidden');
        const ch = document.getElementById('createMonthChevron');
        if (ch) ch.classList.remove('rotate-180');
        checkAndLoadExistingCost();
    }

    function selectCreateYear(yr, yrLabel) {
        document.getElementById('create_year').value = yr;
        document.getElementById('createYearText').textContent = yrLabel;
        document.getElementById('createYearMenu').classList.add('hidden');
        const ch = document.getElementById('createYearChevron');
        if (ch) ch.classList.remove('rotate-180');
        checkAndLoadExistingCost();
    }

    // Close popovers on click outside
    document.addEventListener('click', function(e) {
        const popovers = [
            { container: 'filterBranchDropdownContainer', menu: 'filterBranchMenu', chevron: 'filterBranchChevron' },
            { container: 'filterMonthDropdownContainer', menu: 'filterMonthMenu', chevron: 'filterMonthChevron' },
            { container: 'filterYearDropdownContainer', menu: 'filterYearMenu', chevron: 'filterYearChevron' },
            { container: 'createBranchDropdownContainer', menu: 'createBranchMenu', chevron: 'createBranchChevron' },
            { container: 'createMonthDropdownContainer', menu: 'createMonthMenu', chevron: 'createMonthChevron' },
            { container: 'createYearDropdownContainer', menu: 'createYearMenu', chevron: 'createYearChevron' },
        ];

        popovers.forEach(p => {
            const containerEl = document.getElementById(p.container);
            const menuEl = document.getElementById(p.menu);
            const chevronEl = document.getElementById(p.chevron);
            if (containerEl && menuEl && !containerEl.contains(e.target)) {
                menuEl.classList.add('hidden');
                if (chevronEl) chevronEl.classList.remove('rotate-180');
            }
        });
    });

    // Auto-load data jika cabang, bulan, dan tahun sudah pernah diinput sebelumnya
    function checkAndLoadExistingCost() {
        const branchId = document.getElementById('create_branch_id').value;
        const month = document.getElementById('create_month').value;
        const year = document.getElementById('create_year').value;
        const noticeEl = document.getElementById('createExistingNotice');
        const submitBtn = document.getElementById('createSubmitBtn');

        if (!branchId || !month || !year) return;

        fetch(`/monthly-costs/get-data?branch_id=${branchId}&month=${month}&year=${year}`)
            .then(res => res.json())
            .then(res => {
                const fields = ['rent_cost', 'wifi_cost', 'trash_cost', 'utilities_cost', 'netflix_cost', 'ipl_cost', 'salary_cost', 'other_cost'];
                
                if (res.found && res.data) {
                    noticeEl.classList.remove('hidden');
                    submitBtn.textContent = 'Perbarui Cost Cabang';
                    
                    fields.forEach(f => {
                        const inp = document.getElementById('create_' + f);
                        if (inp) {
                            const val = res.data[f] ? parseInt(res.data[f]) : 0;
                            inp.value = val > 0 ? new Intl.NumberFormat('id-ID').format(val) : '';
                        }
                    });
                    
                    const notesInp = document.getElementById('create_notes');
                    if (notesInp) notesInp.value = res.data.notes || '';
                } else {
                    noticeEl.classList.add('hidden');
                    submitBtn.textContent = 'Simpan Cost Cabang';
                    
                    fields.forEach(f => {
                        const inp = document.getElementById('create_' + f);
                        if (inp) inp.value = '';
                    });
                    
                    const notesInp = document.getElementById('create_notes');
                    if (notesInp) notesInp.value = '';
                }
                recalculateCreateTotal();
            })
            .catch(err => {
                console.error('Error fetching cost data:', err);
            });
    }

    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        checkAndLoadExistingCost();
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal(cost) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/monthly-costs/${cost.id}`;

        document.getElementById('editPeriodLabel').textContent = `${cost.branch.name} — ${cost.period_name}`;

        const fields = ['rent_cost', 'wifi_cost', 'trash_cost', 'utilities_cost', 'netflix_cost', 'ipl_cost', 'salary_cost', 'other_cost'];
        fields.forEach(f => {
            const input = document.getElementById('edit_' + f);
            if (input) {
                const val = cost[f] ? parseInt(cost[f]) : 0;
                input.value = val > 0 ? new Intl.NumberFormat('id-ID').format(val) : '';
            }
        });

        document.getElementById('edit_notes').value = cost.notes || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        recalculateEditTotal();
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openDetailModal(cost) {
        const modal = document.getElementById('detailModal');
        document.getElementById('detailBranchPeriodLabel').textContent = `${cost.branch.name} — ${cost.period_name}`;

        const formatRupiah = (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);

        document.getElementById('detail_rent').textContent = formatRupiah(cost.rent_cost);
        document.getElementById('detail_wifi').textContent = formatRupiah(cost.wifi_cost);
        document.getElementById('detail_trash').textContent = formatRupiah(cost.trash_cost);
        document.getElementById('detail_utilities').textContent = formatRupiah(cost.utilities_cost);
        document.getElementById('detail_netflix').textContent = formatRupiah(cost.netflix_cost);
        document.getElementById('detail_ipl').textContent = formatRupiah(cost.ipl_cost);
        document.getElementById('detail_salary').textContent = formatRupiah(cost.salary_cost);
        document.getElementById('detail_other').textContent = formatRupiah(cost.other_cost);

        document.getElementById('detailSubtotalMonthly').textContent = formatRupiah(cost.total_monthly_cost);
        document.getElementById('detailSubtotalDaily').textContent = formatRupiah(cost.daily_expense_total);
        document.getElementById('detail_daily_raw').textContent = formatRupiah(cost.daily_expense_raw);
        document.getElementById('detail_daily_non_raw').textContent = formatRupiah(cost.daily_expense_non_raw);
        document.getElementById('detail_daily_personal').textContent = formatRupiah(cost.daily_expense_personal);
        document.getElementById('detailGrandTotal').textContent = formatRupiah(cost.grand_total_cost);

        const notesEl = document.getElementById('detailNotes');
        const notesContainer = document.getElementById('detailNotesContainer');
        if (cost.notes && cost.notes.trim() !== '') {
            notesEl.textContent = cost.notes;
            notesContainer.classList.remove('hidden');
        } else {
            notesContainer.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
