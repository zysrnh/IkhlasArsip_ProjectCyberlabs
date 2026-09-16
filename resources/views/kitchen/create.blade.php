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
            <p class="text-sm text-gray-500 mt-1">Cukup isi kolom <strong class="text-gray-800">Masak Hari Ini</strong> dan <strong class="text-gray-800">Terjual</strong>. Sisa kemarin dan perhitungan total dihitung otomatis oleh sistem.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kitchen-reports.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">
                Kembali ke Riwayat
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('kitchen-reports.store') }}" method="POST" id="kitchenReportForm" class="space-y-6">
        @csrf

        <!-- Top Header Card: Cabang & Tanggal -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <!-- Pilihan Cabang -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Cabang Outlet</label>
                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                        <select name="branch_id" id="branchSelector" onchange="changeBranchOrDate()" class="w-full text-sm font-medium border-gray-200 rounded-xl px-4 py-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $activeBranch->id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="branch_id" value="{{ $activeBranch->id }}">
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800">
                            {{ $activeBranch->name }}
                        </div>
                    @endif
                </div>

                <!-- Pilihan Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Tanggal Laporan</label>
                    <input type="date" name="report_date" id="reportDateInput" value="{{ $dateString }}" onchange="changeBranchOrDate()" class="w-full text-sm font-medium border-gray-200 rounded-xl px-4 py-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition">
                </div>

                <!-- Info Box Sisa Kemarin -->
                <div class="bg-blue-50/75 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-900">
                    <div class="font-semibold flex items-center gap-1.5 text-blue-800 mb-1">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Logika Sisa Otomatis
                    </div>
                    <p class="text-blue-700 leading-relaxed">
                        @if($previousReport)
                            Sisa kemarin otomatis ditarik dari laporan tanggal <strong>{{ $previousReport->report_date->translatedFormat('d M Y') }}</strong>. Khusus menu cepat basi (sayur/mie), sisa kemarin di-set <strong>0</strong>.
                        @else
                            Belum ada laporan hari sebelumnya untuk cabang ini. Sisa kemarin diawali dari <strong>0</strong>.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Table of 57 Menu Items -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-gray-900">Daftar Porsi Masakan Dapur ({{ count($preparedItems) }} Menu)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Semua kalkulasi dilakukan secara live tanpa perlu me-reload halaman.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-100">
                        Merah: Cepat Basi (Sayur/Mie)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-teal-50 text-teal-700 border border-teal-100">
                        Hijau: Lauk Biasa (Bisa Diinepin)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="sticky top-0 bg-gray-100 border-b border-gray-200 z-10 text-[11px] font-bold text-gray-700 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-4 min-w-[200px]">Nama Masakan</th>
                            <th class="py-3 px-3 text-center w-28 bg-gray-50">Sisa Kemarin</th>
                            <th class="py-3 px-3 text-center w-28 bg-amber-50/50 text-amber-900">Masak Hari Ini</th>
                            <th class="py-3 px-3 text-center w-28 bg-gray-50">Total Masakan</th>
                            <th class="py-3 px-3 text-center w-28 bg-blue-50/50 text-blue-900">Terjual</th>
                            <th class="py-3 px-3 text-center w-24 bg-gray-50">Sisa Hari Ini</th>
                            <th class="py-3 px-3 text-right w-28">Harga (Rp)</th>
                            <th class="py-3 px-4 text-right w-36 bg-emerald-50/40 text-emerald-900">Total Penjualan</th>
                            <th class="py-3 px-3 text-right w-32 bg-cyan-50/40 text-cyan-900">Sisa Bisa Dijual</th>
                            <th class="py-3 px-3 text-right w-32 bg-rose-50/40 text-rose-900">Lauk Terbuang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-800 font-medium">
                        @foreach($preparedItems as $index => $item)
                        <tr class="hover:bg-gray-50/60 transition item-row" data-index="{{ $index }}" data-perishable="{{ $item['is_perishable'] ? '1' : '0' }}">
                            <!-- Hidden ID -->
                            <input type="hidden" name="items[{{ $index }}][menu_id]" value="{{ $item['menu_id'] }}">

                            <!-- No -->
                            <td class="py-2.5 px-3 text-center text-gray-400 font-semibold">
                                {{ $item['order_number'] ?? ($index + 1) }}
                            </td>

                            <!-- Nama Masakan -->
                            <td class="py-2.5 px-4 font-semibold text-gray-900">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item['menu_name'] }}</span>
                                    @if($item['is_perishable'])
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-100">Basi</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Sisa Kemarin (Readonly/Disabled) -->
                            <td class="py-2.5 px-3 text-center bg-gray-50/50">
                                <input type="number" name="items[{{ $index }}][yesterday_remaining]" value="{{ $item['yesterday_remaining'] }}" class="w-20 text-center py-1.5 px-2 bg-gray-100 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 cursor-not-allowed yesterday-rem-input" readonly>
                            </td>

                            <!-- Masak Hari Ini (Interactive Input) -->
                            <td class="py-2.5 px-3 text-center bg-amber-50/20">
                                <input type="number" name="items[{{ $index }}][cooked_today]" value="{{ $item['cooked_today'] }}" min="0" oninput="calculateRow({{ $index }})" class="w-20 text-center py-1.5 px-2 bg-white border border-amber-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg text-xs font-bold text-gray-900 cooked-today-input">
                            </td>

                            <!-- Total Masakan (Auto Calculated) -->
                            <td class="py-2.5 px-3 text-center bg-gray-50/50">
                                <span class="font-bold text-gray-900 total-cooked-label">0</span>
                            </td>

                            <!-- Terjual (Interactive Input) -->
                            <td class="py-2.5 px-3 text-center bg-blue-50/20">
                                <input type="number" name="items[{{ $index }}][sold]" value="{{ $item['sold'] }}" min="0" oninput="calculateRow({{ $index }})" class="w-20 text-center py-1.5 px-2 bg-white border border-blue-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg text-xs font-bold text-gray-900 sold-input">
                            </td>

                            <!-- Sisa Hari Ini (Auto Calculated) -->
                            <td class="py-2.5 px-3 text-center bg-gray-50/50">
                                <span class="font-bold text-gray-800 remaining-label">0</span>
                            </td>

                            <!-- Harga (Hidden + Display) -->
                            <td class="py-2.5 px-3 text-right text-gray-600">
                                <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" class="unit-price-val">
                                {{ number_format($item['unit_price'], 0, ',', '.') }}
                            </td>

                            <!-- Total Penjualan (Terjual x Harga) -->
                            <td class="py-2.5 px-4 text-right font-bold text-emerald-700 bg-emerald-50/30 total-sales-cell">
                                Rp 0
                            </td>

                            <!-- Sisa Bisa Dijual (Lauk Biasa) -->
                            <td class="py-2.5 px-3 text-right text-[#0A97B0] bg-cyan-50/30 sellable-cell">
                                Rp 0
                            </td>

                            <!-- Lauk Terbuang (Cepat Basi) -->
                            <td class="py-2.5 px-3 text-right text-rose-600 bg-rose-50/30 wasted-cell">
                                Rp 0
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Sticky Bottom Grand Total -->
                    <tfoot class="sticky bottom-0 bg-gray-900 text-white font-bold text-xs border-t-2 border-gray-800 z-10">
                        <tr>
                            <td colspan="4" class="py-3 px-4 uppercase tracking-wider text-right text-gray-300">
                                GRAND TOTAL PERHITUNGAN MASAKAN:
                            </td>
                            <td class="py-3 px-3 text-center text-amber-300" id="grandTotalCooked">0</td>
                            <td class="py-3 px-3 text-center text-blue-300" id="grandTotalSold">0</td>
                            <td class="py-3 px-3 text-center text-gray-300" id="grandTotalRemaining">0</td>
                            <td class="py-3 px-3"></td>
                            <td class="py-3 px-4 text-right text-emerald-400 text-sm font-extrabold" id="grandTotalSalesLabel">Rp 0</td>
                            <td class="py-3 px-3 text-right text-cyan-300" id="grandTotalSellableLabel">Rp 0</td>
                            <td class="py-3 px-3 text-right text-rose-400" id="grandTotalWastedLabel">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Settlement & Discrepancy Warning Section (Sesuai Box Kuning Excel) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Rekapan Uang Kasir (Cash, QRIS, Online) -->
            <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Rekapan Pembayaran Uang Kasir</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Input jumlah fisik uang yang diterima di kasir cabang hari ini.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Pendapatan Cash -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pendapatan Cash (Laci)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-400 font-semibold">Rp</span>
                            <input type="text" name="cash_income" id="cashIncomeInput" value="0" oninput="formatRupiahInput(this); calculateSettlement();" class="w-full text-sm font-bold pl-9 pr-3 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl focus:ring-1 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition">
                        </div>
                    </div>

                    <!-- Pendapatan QRIS -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pendapatan QRIS / Transfer</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-400 font-semibold">Rp</span>
                            <input type="text" name="qris_income" id="qrisIncomeInput" value="0" oninput="formatRupiahInput(this); calculateSettlement();" class="w-full text-sm font-bold pl-9 pr-3 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl focus:ring-1 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition">
                        </div>
                    </div>

                    <!-- Pendapatan Online Food -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pendapatan Online Food</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-400 font-semibold">Rp</span>
                            <input type="text" name="online_food_income" id="onlineFoodIncomeInput" value="0" oninput="formatRupiahInput(this); calculateSettlement();" class="w-full text-sm font-bold pl-9 pr-3 py-2.5 bg-gray-50 focus:bg-white border border-gray-200 rounded-xl focus:ring-1 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition">
                        </div>
                    </div>
                </div>

                <!-- Total Omset Bar -->
                <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">TOTAL OMSET HARI INI:</span>
                        <p class="text-[11px] text-emerald-700">Cash + QRIS + Online Food</p>
                    </div>
                    <div class="text-xl font-extrabold text-emerald-700" id="totalOmsetLabel">
                        Rp 0
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Catatan Dapur / Keterangan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Tulis catatan jika ada lauk rusak, retur, atau kendala dapur..." class="w-full text-xs border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-1 focus:ring-[#0A97B0] focus:border-[#0A97B0] transition"></textarea>
                </div>
            </div>

            <!-- Right: Discrepancy Notification Card (Box Kuning/Merah Sesuai Excel) -->
            <div class="lg:col-span-5 space-y-4">
                <div id="discrepancyBox" class="p-5 rounded-2xl border transition duration-200 bg-amber-50 border-amber-200 text-amber-900">
                    <div class="flex items-start gap-3">
                        <div id="discrepancyIcon" class="mt-0.5 text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm" id="discrepancyTitle">Validasi Selisih Kasir vs Masakan</h4>
                            <p class="text-xs mt-1 leading-relaxed" id="discrepancyMsg">
                                Masukkan rincian uang kasir (Cash, QRIS, Online). Sistem akan otomatis mencocokkan total omset dengan nilai porsi masakan yang terjual.
                            </p>
                            <div class="mt-3 pt-3 border-t border-amber-200 flex items-center justify-between text-xs font-semibold">
                                <span>Selisih (Omset - Penjualan):</span>
                                <span id="discrepancyDiffValue" class="font-bold text-sm">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
                    <button type="submit" class="w-full py-3.5 px-6 bg-[#0B192C] hover:bg-[#142B4D] text-white font-bold text-sm rounded-xl shadow-sm transition duration-150 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Laporan Masakan Dapur
                    </button>
                    <p class="text-[11px] text-gray-400 text-center">Pastikan data porsi masak dan penjualan sudah dicek sebelum disimpan.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript Live Calculation -->
<script>
    function changeBranchOrDate() {
        const branchSelect = document.getElementById('branchSelector');
        const branchId = branchSelect ? branchSelect.value : "{{ $activeBranch->id }}";
        const reportDate = document.getElementById('reportDateInput').value;

        window.location.href = `{{ route('kitchen-reports.create') }}?branch_id=${branchId}&report_date=${reportDate}`;
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
        const sold = cleanNumber(row.querySelector('.sold-input').value);
        const unitPrice = cleanNumber(row.querySelector('.unit-price-val').value);

        const totalCooked = yesterdayRem + cookedToday;
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

        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const isPerishable = row.getAttribute('data-perishable') === '1';
            const yesterdayRem = cleanNumber(row.querySelector('.yesterday-rem-input').value);
            const cookedToday = cleanNumber(row.querySelector('.cooked-today-input').value);
            const sold = cleanNumber(row.querySelector('.sold-input').value);
            const unitPrice = cleanNumber(row.querySelector('.unit-price-val').value);

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

        if (totalOmset === 0 && grandTotalSales === 0) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-gray-50 border-gray-200 text-gray-700';
            discrepancyTitle.innerText = 'Menunggu Input Data';
            discrepancyMsg.innerText = 'Silakan isi jumlah masakan yang dimasak & terjual serta rekapan uang kasir.';
            discrepancyDiffValue.innerText = 'Rp 0';
            discrepancyDiffValue.className = 'font-bold text-sm text-gray-700';
        } else if (Math.abs(diff) < 1) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-emerald-50 border-emerald-200 text-emerald-900';
            discrepancyTitle.innerText = 'Sempurna: Omset Kasir Cocok!';
            discrepancyMsg.innerText = 'Total uang fisik di kasir (Cash + QRIS + Online) 100% cocok dengan total nilai porsi masakan yang laku terjual.';
            discrepancyDiffValue.innerText = 'Rp 0 (Match)';
            discrepancyDiffValue.className = 'font-bold text-sm text-emerald-700';
        } else if (diff > 0) {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-amber-50 border-amber-200 text-amber-900';
            discrepancyTitle.innerText = 'Perhatian: Ada Kelebihan Kasir';
            discrepancyMsg.innerText = `Total uang di kasir LEBIH Rp ${formatNumber(diff)} dibanding nilai porsi masakan yang tercatat terjual.`;
            discrepancyDiffValue.innerText = '+ Rp ' + formatNumber(diff);
            discrepancyDiffValue.className = 'font-bold text-sm text-amber-700';
        } else {
            discrepancyBox.className = 'p-5 rounded-2xl border bg-rose-50 border-rose-200 text-rose-900';
            discrepancyTitle.innerText = 'Peringatan: Selisih Kurang (Minus)';
            discrepancyMsg.innerText = `Total uang kasir KURANG Rp ${formatNumber(Math.abs(diff))} dari nilai porsi masakan yang tercatat terjual. Cek kembali perhitungan kasir.`;
            discrepancyDiffValue.innerText = '- Rp ' + formatNumber(Math.abs(diff));
            discrepancyDiffValue.className = 'font-bold text-sm text-rose-700';
        }
    }

    // Jalankan kalkulasi awal saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            calculateRow(idx);
        });
    });
</script>
@endsection
