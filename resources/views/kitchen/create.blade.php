@extends('layouts.app')

@section('title', 'Input Masakan Dapur Harian')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('kitchen-reports.index') }}" class="hover:text-[#0A97B0]">Input Dapur Harian</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">Form Input Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Input Masakan Dapur Harian</h1>
            <p class="text-sm text-gray-500 mt-1">Input porsi masak, porsi terjual, dan rekapan uang kasir cabang dengan mudah dan cepat.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kitchen-reports.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                Kembali ke Riwayat
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('kitchen-reports.store') }}" method="POST" id="kitchenReportForm" class="space-y-6">
        @csrf

        <!-- Top Header Card: Cabang & Tanggal -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-center">
                <!-- Pilihan Cabang -->
                <div class="relative" id="createBranchDropdownWrapper">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cabang Outlet</label>
                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                        <input type="hidden" name="branch_id" id="createBranchInput" value="{{ $activeBranch->id }}">
                        <button 
                            type="button" 
                            onclick="toggleKitchenDropdown('createBranchDropdownMenu')" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold hover:border-tealBrand focus:outline-none transition-colors"
                        >
                            <div class="flex items-center space-x-2 truncate">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span id="createSelectedBranchLabel" class="truncate font-bold text-slate-900">{{ $activeBranch->name }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="createBranchDropdownMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 py-1 max-h-56 overflow-y-auto">
                            @foreach($branches as $b)
                                <button 
                                    type="button" 
                                    onclick="selectCreateBranch('{{ $b->id }}', '{{ $b->name }}')" 
                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-slate-50 flex items-center justify-between {{ $activeBranch->id == $b->id ? 'font-bold text-tealBrand bg-teal-50/50' : 'text-slate-700' }}"
                                >
                                    <span>{{ $b->name }}</span>
                                    @if($activeBranch->id == $b->id)
                                        <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @else
                        <input type="hidden" name="branch_id" id="createBranchInput" value="{{ $activeBranch->id }}">
                        <div class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>{{ $activeBranch->name }}</span>
                        </div>
                    @endif
                </div>

                <!-- Pilihan Tanggal (Flatpickr Custom) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Laporan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="report_date" 
                            id="reportDateInput" 
                            value="{{ $dateString }}" 
                            class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-tealBrand cursor-pointer"
                        >
                    </div>
                </div>

                <!-- Sisa Kemarin Status Info -->
                <div class="bg-blue-50/75 border border-blue-100 rounded-xl p-3 text-xs text-blue-900">
                    <div class="font-bold flex items-center gap-1.5 text-blue-800 mb-0.5">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sisa Kemarin Terkoneksi
                    </div>
                    <p class="text-blue-700 text-[11px] leading-relaxed">
                        @if($previousReport)
                            Ditarik dari tanggal <strong>{{ $previousReport->report_date->translatedFormat('d M Y') }}</strong> (sayur/mie = 0).
                        @else
                            Awal periode: sisa kemarin bernilai <strong>0</strong>.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Section Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
            <button 
                type="button" 
                onclick="switchKitchenTab('tab-dishes')" 
                id="tabBtnDishes"
                class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition flex items-center gap-2 bg-[#0B192C] text-white shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>1. Porsi Masakan Dapur</span>
                <span id="filledDishesBadge" class="px-2 py-0.5 text-[10px] bg-tealBrand text-white rounded-full font-bold">0 Terisi</span>
            </button>

            <button 
                type="button" 
                onclick="switchKitchenTab('tab-settlement')" 
                id="tabBtnSettlement"
                class="px-5 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-slate-100 text-slate-600 hover:bg-slate-200"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>2. Rekapan Uang Kasir & Settlement</span>
                <span id="tabSettlementStatusBadge" class="px-2 py-0.5 text-[10px] bg-slate-300 text-slate-700 rounded-full font-bold">Pending</span>
            </button>
        </div>

        <!-- TAB 1: DAFTAR PORSI MASAKAN DAPUR -->
        <div id="tabContentDishes" class="space-y-4">
            <!-- Search & Quick Filter Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Search Input Live -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="menuSearchInput" 
                        oninput="filterMenuTable()" 
                        placeholder="Cari nama masakan (contoh: ayam, sop, rendang)..." 
                        class="w-full pl-10 pr-9 py-2.5 text-xs bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                    <button 
                        type="button" 
                        onclick="clearSearchInput()" 
                        id="clearSearchBtn" 
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Category Filter Pills -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button 
                        type="button" 
                        onclick="setCategoryFilter('all')" 
                        id="catBtnAll"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-900 text-white transition"
                    >
                        Semua ({{ count($preparedItems) }})
                    </button>
                    <button 
                        type="button" 
                        onclick="setCategoryFilter('non_perishable')" 
                        id="catBtnNonPerishable"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 transition"
                    >
                        Lauk Biasa
                    </button>
                    <button 
                        type="button" 
                        onclick="setCategoryFilter('perishable')" 
                        id="catBtnPerishable"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition"
                    >
                        Sayur (Cepat Basi)
                    </button>
                    <button 
                        type="button" 
                        onclick="setCategoryFilter('filled_only')" 
                        id="catBtnFilled"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 transition"
                    >
                        Hanya yang Diisi
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto max-h-[560px]">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 z-10 text-[11px] font-extrabold text-slate-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-3 text-center w-12">No</th>
                                <th class="py-3 px-4 min-w-[200px]">Nama Masakan</th>
                                <th class="py-3 px-3 text-center w-28 bg-slate-50">Sisa Kemarin</th>
                                <th class="py-3 px-3 text-center w-28 bg-amber-50/60 text-amber-900 font-black">Masak Hari Ini</th>
                                <th class="py-3 px-3 text-center w-28 bg-slate-50">Total Masakan</th>
                                <th class="py-3 px-3 text-center w-28 bg-blue-50/60 text-blue-900 font-black">Terjual</th>
                                <th class="py-3 px-3 text-center w-24 bg-slate-50">Sisa Hari Ini</th>
                                <th class="py-3 px-3 text-right w-28">Harga (Rp)</th>
                                <th class="py-3 px-4 text-right w-36 bg-emerald-50/40 text-emerald-900">Total Penjualan</th>
                                <th class="py-3 px-3 text-right w-32 bg-cyan-50/40 text-cyan-900">Sisa Bisa Dijual</th>
                                <th class="py-3 px-3 text-right w-32 bg-rose-50/40 text-rose-900">Lauk Terbuang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800 font-medium" id="dishesTableBody">
                            @foreach($preparedItems as $index => $item)
                            <tr class="hover:bg-slate-50/60 transition item-row" data-index="{{ $index }}" data-name="{{ strtolower($item['menu_name']) }}" data-perishable="{{ $item['is_perishable'] ? '1' : '0' }}">
                                <input type="hidden" name="items[{{ $index }}][menu_id]" value="{{ $item['menu_id'] }}">

                                <!-- No -->
                                <td class="py-2.5 px-3 text-center text-slate-400 font-semibold">
                                    {{ $item['order_number'] ?? ($index + 1) }}
                                </td>

                                <!-- Nama Masakan -->
                                <td class="py-2.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="menu-name-label">{{ $item['menu_name'] }}</span>
                                        @if($item['is_perishable'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-100">Basi</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Sisa Kemarin -->
                                <td class="py-2.5 px-3 text-center bg-slate-50/50">
                                    <input type="number" name="items[{{ $index }}][yesterday_remaining]" value="{{ $item['yesterday_remaining'] }}" class="w-20 text-center py-1.5 px-2 bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 cursor-not-allowed yesterday-rem-input" readonly>
                                </td>

                                <!-- Masak Hari Ini -->
                                <td class="py-2.5 px-3 text-center bg-amber-50/20">
                                    <input type="number" name="items[{{ $index }}][cooked_today]" value="{{ $item['cooked_today'] }}" min="0" oninput="calculateRow({{ $index }})" class="w-20 text-center py-1.5 px-2 bg-white border border-amber-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg text-xs font-bold text-slate-900 cooked-today-input">
                                </td>

                                <!-- Total Masakan -->
                                <td class="py-2.5 px-3 text-center bg-slate-50/50">
                                    <span class="font-bold text-slate-900 total-cooked-label">0</span>
                                </td>

                                <!-- Terjual -->
                                <td class="py-2.5 px-3 text-center bg-blue-50/20">
                                    <input type="number" name="items[{{ $index }}][sold]" value="{{ $item['sold'] }}" min="0" oninput="calculateRow({{ $index }})" class="w-20 text-center py-1.5 px-2 bg-white border border-blue-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg text-xs font-bold text-slate-900 sold-input">
                                </td>

                                <!-- Sisa Hari Ini -->
                                <td class="py-2.5 px-3 text-center bg-slate-50/50">
                                    <span class="font-bold text-slate-800 remaining-label">0</span>
                                </td>

                                <!-- Harga -->
                                <td class="py-2.5 px-3 text-right text-slate-600 font-semibold">
                                    <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" class="unit-price-val">
                                    {{ number_format($item['unit_price'], 0, ',', '.') }}
                                </td>

                                <!-- Total Penjualan -->
                                <td class="py-2.5 px-4 text-right font-bold text-emerald-700 bg-emerald-50/30 total-sales-cell">
                                    Rp 0
                                </td>

                                <!-- Sisa Bisa Dijual -->
                                <td class="py-2.5 px-3 text-right text-[#0A97B0] font-semibold bg-cyan-50/30 sellable-cell">
                                    Rp 0
                                </td>

                                <!-- Lauk Terbuang -->
                                <td class="py-2.5 px-3 text-right text-rose-600 font-semibold bg-rose-50/30 wasted-cell">
                                    Rp 0
                                </td>
                            </tr>
                            @endforeach
                            <!-- Empty Search Row -->
                            <tr id="emptySearchRow" class="hidden">
                                <td colspan="11" class="py-8 text-center text-slate-400">
                                    Tidak ada menu masakan yang cocok dengan kata kunci pencarian.
                                </td>
                            </tr>
                        </tbody>

                        <!-- Sticky Bottom Grand Total -->
                        <tfoot class="sticky bottom-0 bg-slate-900 text-white font-bold text-xs border-t-2 border-slate-800 z-10">
                            <tr>
                                <td colspan="4" class="py-3 px-4 uppercase tracking-wider text-right text-slate-300">
                                    GRAND TOTAL PENJUALAN MASAKAN:
                                </td>
                                <td class="py-3 px-3 text-center text-amber-300" id="grandTotalCooked">0</td>
                                <td class="py-3 px-3 text-center text-blue-300" id="grandTotalSold">0</td>
                                <td class="py-3 px-3 text-center text-slate-300" id="grandTotalRemaining">0</td>
                                <td class="py-3 px-3"></td>
                                <td class="py-3 px-4 text-right text-emerald-400 text-sm font-extrabold" id="grandTotalSalesLabel">Rp 0</td>
                                <td class="py-3 px-3 text-right text-cyan-300" id="grandTotalSellableLabel">Rp 0</td>
                                <td class="py-3 px-3 text-right text-rose-400" id="grandTotalWastedLabel">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Next Button to Step 2 -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Selesai input porsi masakan? Lanjut ke input rekapan uang kasir.</span>
                    <button 
                        type="button" 
                        onclick="switchKitchenTab('tab-settlement')" 
                        class="px-5 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2"
                    >
                        <span>Lanjut ke Rekapan Uang Kasir</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 2: REKAPAN UANG KASIR & SETTLEMENT (LAYOUT SEPERTI EXCEL) -->
        <div id="tabContentSettlement" class="hidden space-y-5">
            <!-- Excel Style Horizontal Cards Layout -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rekapan Pembayaran Uang Kasir Hari Ini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Input jumlah uang fisik & digital yang diterima kasir. Sistem akan mencocokkannya dengan grand total masakan yang laku.</p>
                </div>

                <!-- 4 Box Layout Grid (Sesuai Gambar Excel Mas Fuad) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- 1. PENDAPATAN CASH -->
                    <div class="bg-slate-50/75 p-4 rounded-xl border border-slate-200 focus-within:border-[#0A97B0] focus-within:bg-white transition">
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">1. Pendapatan Cash (Laci)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                            <input 
                                type="text" 
                                name="cash_income" 
                                id="cashIncomeInput" 
                                value="0" 
                                oninput="formatRupiahInput(this); calculateSettlement();" 
                                class="w-full text-base font-extrabold pl-10 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-tealBrand"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5">Uang tunai fisik di kasir</p>
                    </div>

                    <!-- 2. PENDAPATAN QRIS -->
                    <div class="bg-slate-50/75 p-4 rounded-xl border border-slate-200 focus-within:border-[#0A97B0] focus-within:bg-white transition">
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">2. Pendapatan QRIS / Transfer</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                            <input 
                                type="text" 
                                name="qris_income" 
                                id="qrisIncomeInput" 
                                value="0" 
                                oninput="formatRupiahInput(this); calculateSettlement();" 
                                class="w-full text-base font-extrabold pl-10 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-tealBrand"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5">Settlement EDC / QRIS</p>
                    </div>

                    <!-- 3. PENDAPATAN ONLINE FOOD -->
                    <div class="bg-slate-50/75 p-4 rounded-xl border border-slate-200 focus-within:border-[#0A97B0] focus-within:bg-white transition">
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">3. Pendapatan Online Food</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                            <input 
                                type="text" 
                                name="online_food_income" 
                                id="onlineFoodIncomeInput" 
                                value="0" 
                                oninput="formatRupiahInput(this); calculateSettlement();" 
                                class="w-full text-base font-extrabold pl-10 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-tealBrand"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5">GrabFood / GoFood / Shopee</p>
                    </div>

                    <!-- 4. TOTAL OMSET HARI INI -->
                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-[11px] font-black text-emerald-900 uppercase tracking-wider">4. TOTAL OMSET HARI INI</span>
                            <div class="text-xl font-black text-emerald-700 mt-1" id="totalOmsetLabel">
                                Rp 0
                            </div>
                        </div>
                        <p class="text-[10px] text-emerald-600 mt-2">Akumulasi Cash + QRIS + Online</p>
                    </div>
                </div>

                <!-- Discrepancy & Validation Banner (Box Kuning / Hijau / Merah Excel) -->
                <div id="discrepancyBox" class="p-5 rounded-2xl border transition duration-200 bg-amber-50 border-amber-200 text-amber-900">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div id="discrepancyIcon" class="mt-0.5 text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm" id="discrepancyTitle">Validasi Selisih Uang Kasir vs Penjualan Masakan</h4>
                                <p class="text-xs mt-0.5 leading-relaxed" id="discrepancyMsg">
                                    Memeriksa kecocokan antara uang kasir dengan total porsi masakan terjual.
                                </p>
                            </div>
                        </div>

                        <!-- Difference Tag -->
                        <div class="bg-white/80 backdrop-blur-xs px-4 py-2.5 rounded-xl border border-amber-200 text-right shrink-0">
                            <div class="text-[10px] uppercase font-extrabold text-slate-500">Nilai Selisih</div>
                            <div id="discrepancyDiffValue" class="text-base font-black text-amber-800">Rp 0</div>
                        </div>
                    </div>
                </div>

                <!-- Notes Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Catatan Dapur / Keterangan Tambahan (Opsional)</label>
                    <textarea 
                        name="notes" 
                        rows="2" 
                        placeholder="Contoh: Ada 2 porsi rendang retur komplain, sisa sayur dibuang pukul 21:00..." 
                        class="w-full text-xs border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-tealBrand transition"
                    ></textarea>
                </div>
            </div>

            <!-- Bottom Actions Bar -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <button 
                    type="button" 
                    onclick="switchKitchenTab('tab-dishes')" 
                    class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span>Kembali ke Porsi Masakan</span>
                </button>

                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-7 py-3 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl shadow-xs transition duration-150 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Simpan Laporan Masakan Dapur</span>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- JavaScript Live Calculation, Search, Filter & Tabs -->
<script>
    let currentCategoryFilter = 'all';

    function switchKitchenTab(tabId) {
        const tabDishes = document.getElementById('tabContentDishes');
        const tabSettlement = document.getElementById('tabContentSettlement');
        const btnDishes = document.getElementById('tabBtnDishes');
        const btnSettlement = document.getElementById('tabBtnSettlement');

        if (tabId === 'tab-dishes') {
            tabDishes.classList.remove('hidden');
            tabSettlement.classList.add('hidden');

            btnDishes.className = "px-5 py-2.5 text-xs font-extrabold rounded-xl transition flex items-center gap-2 bg-[#0B192C] text-white shadow-xs";
            btnSettlement.className = "px-5 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-slate-100 text-slate-600 hover:bg-slate-200";
        } else {
            tabDishes.classList.add('hidden');
            tabSettlement.classList.remove('hidden');

            btnDishes.className = "px-5 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-slate-100 text-slate-600 hover:bg-slate-200";
            btnSettlement.className = "px-5 py-2.5 text-xs font-extrabold rounded-xl transition flex items-center gap-2 bg-[#0B192C] text-white shadow-xs";
        }
    }

    function setCategoryFilter(cat) {
        currentCategoryFilter = cat;

        const catButtons = {
            'all': document.getElementById('catBtnAll'),
            'non_perishable': document.getElementById('catBtnNonPerishable'),
            'perishable': document.getElementById('catBtnPerishable'),
            'filled_only': document.getElementById('catBtnFilled')
        };

        for (const [key, btn] of Object.entries(catButtons)) {
            if (key === cat) {
                btn.className = "px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-900 text-white transition";
            } else {
                if (key === 'perishable') btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition";
                else if (key === 'non_perishable') btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 transition";
                else if (key === 'filled_only') btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 transition";
                else btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition";
            }
        }

        filterMenuTable();
    }

    function filterMenuTable() {
        const query = (document.getElementById('menuSearchInput').value || '').trim().toLowerCase();
        const clearBtn = document.getElementById('clearSearchBtn');
        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        const rows = document.querySelectorAll('.item-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const isPerishable = row.getAttribute('data-perishable') === '1';
            const cookedToday = cleanNumber(row.querySelector('.cooked-today-input').value);
            const sold = cleanNumber(row.querySelector('.sold-input').value);
            const isFilled = cookedToday > 0 || sold > 0;

            let matchesQuery = query === '' || name.includes(query);
            let matchesCategory = true;

            if (currentCategoryFilter === 'perishable') {
                matchesCategory = isPerishable;
            } else if (currentCategoryFilter === 'non_perishable') {
                matchesCategory = !isPerishable;
            } else if (currentCategoryFilter === 'filled_only') {
                matchesCategory = isFilled;
            }

            if (matchesQuery && matchesCategory) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        const emptyRow = document.getElementById('emptySearchRow');
        if (visibleCount === 0) {
            emptyRow.classList.remove('hidden');
        } else {
            emptyRow.classList.add('hidden');
        }
    }

    function clearSearchInput() {
        document.getElementById('menuSearchInput').value = '';
        filterMenuTable();
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function cleanNumber(str) {
        if (!str) return 0;
        const cleaned = str.toString().replace(/\./g, '').replace(/,/g, '.');
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value === '') {
            input.value = '0';
        } else {
            input.value = parseInt(value, 10).toLocaleString('id-ID');
        }
    }

    let grandTotalSales = 0;
    let grandTotalSellable = 0;
    let grandTotalWasted = 0;
    let grandTotalCooked = 0;
    let grandTotalSold = 0;
    let grandTotalRemaining = 0;

    function calculateRow(index) {
        const row = document.querySelector(`.item-row[data-index="${index}"]`);
        if (!row) return;

        const isPerishable = row.getAttribute('data-perishable') === '1';
        const yesterdayRem = cleanNumber(row.querySelector('.yesterday-rem-input').value);
        const cookedToday = cleanNumber(row.querySelector('.cooked-today-input').value);
        let sold = cleanNumber(row.querySelector('.sold-input').value);
        const unitPrice = cleanNumber(row.querySelector('.unit-price-val').value);

        const totalCooked = yesterdayRem + cookedToday;

        // Auto-correct check: Peringatan jika terjual > total masakan
        const soldInput = row.querySelector('.sold-input');
        if (sold > totalCooked && totalCooked > 0) {
            soldInput.classList.add('border-rose-500', 'bg-rose-50');
            soldInput.title = `Peringatan: Jumlah terjual (${sold}) melebihi total masakan (${totalCooked})!`;
        } else {
            soldInput.classList.remove('border-rose-500', 'bg-rose-50');
            soldInput.title = '';
        }

        const remaining = Math.max(0, totalCooked - sold);
        const totalSales = sold * unitPrice;
        const sellableAmount = isPerishable ? 0 : (remaining * unitPrice);
        const wastedAmount = isPerishable ? (remaining * unitPrice) : 0;

        row.querySelector('.total-cooked-label').innerText = formatNumber(totalCooked);
        row.querySelector('.remaining-label').innerText = formatNumber(remaining);
        row.querySelector('.total-sales-cell').innerText = 'Rp ' + formatNumber(totalSales);
        row.querySelector('.sellable-cell').innerText = 'Rp ' + formatNumber(sellableAmount);
        row.querySelector('.wasted-cell').innerText = 'Rp ' + formatNumber(wastedAmount);

        calculateAllTotals();
    }

    function calculateAllTotals() {
        grandTotalSales = 0;
        grandTotalSellable = 0;
        grandTotalWasted = 0;
        grandTotalCooked = 0;
        grandTotalSold = 0;
        grandTotalRemaining = 0;
        let filledCount = 0;

        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const isPerishable = row.getAttribute('data-perishable') === '1';
            const yesterdayRem = cleanNumber(row.querySelector('.yesterday-rem-input').value);
            const cookedToday = cleanNumber(row.querySelector('.cooked-today-input').value);
            const sold = cleanNumber(row.querySelector('.sold-input').value);
            const unitPrice = cleanNumber(row.querySelector('.unit-price-val').value);

            if (cookedToday > 0 || sold > 0) {
                filledCount++;
            }

            const totalCooked = yesterdayRem + cookedToday;
            const remaining = Math.max(0, totalCooked - sold);
            const totalSales = sold * unitPrice;
            const sellableAmount = isPerishable ? 0 : (remaining * unitPrice);
            const wastedAmount = isPerishable ? (remaining * unitPrice) : 0;

            grandTotalCooked += totalCooked;
            grandTotalSold += sold;
            grandTotalRemaining += remaining;
            grandTotalSales += totalSales;
            grandTotalSellable += sellableAmount;
            grandTotalWasted += wastedAmount;
        });

        document.getElementById('grandTotalCooked').innerText = formatNumber(grandTotalCooked);
        document.getElementById('grandTotalSold').innerText = formatNumber(grandTotalSold);
        document.getElementById('grandTotalRemaining').innerText = formatNumber(grandTotalRemaining);
        document.getElementById('grandTotalSalesLabel').innerText = 'Rp ' + formatNumber(grandTotalSales);
        document.getElementById('grandTotalSellableLabel').innerText = 'Rp ' + formatNumber(grandTotalSellable);
        document.getElementById('grandTotalWastedLabel').innerText = 'Rp ' + formatNumber(grandTotalWasted);

        // Update badge
        document.getElementById('filledDishesBadge').innerText = `${filledCount} Terisi`;

        calculateSettlement();
    }

    function calculateSettlement() {
        const cash = cleanNumber(document.getElementById('cashIncomeInput').value);
        const qris = cleanNumber(document.getElementById('qrisIncomeInput').value);
        const online = cleanNumber(document.getElementById('onlineFoodIncomeInput').value);

        const totalOmset = cash + qris + online;
        document.getElementById('totalOmsetLabel').innerText = 'Rp ' + formatNumber(totalOmset);

        const diff = totalOmset - grandTotalSales;
        const discrepancyBox = document.getElementById('discrepancyBox');
        const discrepancyTitle = document.getElementById('discrepancyTitle');
        const discrepancyMsg = document.getElementById('discrepancyMsg');
        const discrepancyDiffValue = document.getElementById('discrepancyDiffValue');
        const tabStatusBadge = document.getElementById('tabSettlementStatusBadge');

        if (totalOmset === 0 && grandTotalSales === 0) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-slate-50 border-slate-200 text-slate-700';
            discrepancyTitle.innerText = 'Menunggu Input Data';
            discrepancyMsg.innerText = 'Silakan isi jumlah masakan yang dimasak & terjual serta rekapan uang kasir.';
            discrepancyDiffValue.innerText = 'Rp 0';
            discrepancyDiffValue.className = 'text-base font-black text-slate-700';
            tabStatusBadge.innerText = 'Pending';
            tabStatusBadge.className = 'px-2 py-0.5 text-[10px] bg-slate-300 text-slate-700 rounded-full font-bold';
        } else if (Math.abs(diff) < 1) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-emerald-50 border-emerald-200 text-emerald-900';
            discrepancyTitle.innerText = 'Sempurna: Omset Kasir Cocok!';
            discrepancyMsg.innerText = 'Total uang kasir (Cash + QRIS + Online) 100% cocok dengan grand total nilai porsi masakan yang laku terjual.';
            discrepancyDiffValue.innerText = 'Rp 0 (Cocok)';
            discrepancyDiffValue.className = 'text-base font-black text-emerald-700';
            tabStatusBadge.innerText = 'Cocok';
            tabStatusBadge.className = 'px-2 py-0.5 text-[10px] bg-emerald-600 text-white rounded-full font-bold';
        } else if (diff > 0) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-amber-50 border-amber-200 text-amber-900';
            discrepancyTitle.innerText = 'Perhatian: Ada Kelebihan Uang Kasir';
            discrepancyMsg.innerText = `Total uang di kasir LEBIH Rp ${formatNumber(diff)} dibanding nilai porsi masakan yang tercatat terjual.`;
            discrepancyDiffValue.innerText = '+ Rp ' + formatNumber(diff);
            discrepancyDiffValue.className = 'text-base font-black text-amber-800';
            tabStatusBadge.innerText = `+Rp ${formatNumber(diff)}`;
            tabStatusBadge.className = 'px-2 py-0.5 text-[10px] bg-amber-500 text-white rounded-full font-bold';
        } else {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-rose-50 border-rose-200 text-rose-900';
            discrepancyTitle.innerText = 'Peringatan: Selisih Kurang (Minus)';
            discrepancyMsg.innerText = `Total uang kasir KURANG Rp ${formatNumber(Math.abs(diff))} dari nilai porsi masakan yang tercatat terjual. Cek kembali perhitungan kasir.`;
            discrepancyDiffValue.innerText = '- Rp ' + formatNumber(Math.abs(diff));
            discrepancyDiffValue.className = 'text-base font-black text-rose-700';
            tabStatusBadge.innerText = `-Rp ${formatNumber(Math.abs(diff))}`;
            tabStatusBadge.className = 'px-2 py-0.5 text-[10px] bg-rose-600 text-white rounded-full font-bold';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#reportDateInput", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: false,
            disableMobile: "true",
            onChange: function(selectedDates, dateStr) {
                changeBranchOrDate(dateStr);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('createBranchDropdownMenu');
            const wrapper = document.getElementById('createBranchDropdownWrapper');
            if (dropdown && wrapper && !wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        calculateAllTotals();
    });

    function toggleKitchenDropdown(id) {
        const menu = document.getElementById(id);
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function selectCreateBranch(id, name) {
        document.getElementById('createBranchInput').value = id;
        document.getElementById('createSelectedBranchLabel').innerText = name;
        document.getElementById('createBranchDropdownMenu').classList.add('hidden');
        changeBranchOrDate();
    }

    function changeBranchOrDate(selectedDate) {
        const branchId = document.getElementById('createBranchInput').value;
        const reportDate = selectedDate || document.getElementById('reportDateInput').value;

        window.location.href = `{{ route('kitchen-reports.create') }}?branch_id=${branchId}&report_date=${reportDate}`;
    }
</script>
@endsection
