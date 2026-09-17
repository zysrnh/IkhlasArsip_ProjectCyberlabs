@extends('layouts.app')

@section('title', 'Dashboard Penjualan')

@section('content')
<div class="space-y-6 animate-fadeIn pb-6">

    <!-- Top Header & Filter Cabang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Executive Sales Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Selamat datang, <strong class="text-slate-800 font-bold">{{ auth()->user()->name }}</strong> &mdash; ringkasan performa penjualan dan operasional dapur per {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}.
            </p>
        </div>

        <!-- Filter Cabang Custom Dropdown Pill -->
        @if(auth()->user()->canAccessAllBranches())
            <div class="relative w-full sm:w-auto" id="dashboardBranchDropdownContainer">
                <form id="dashboardBranchFilterForm" method="GET" action="{{ route('dashboard') }}" class="hidden">
                    <input type="hidden" name="branch_id" id="selectedBranchInput" value="{{ $selectedBranchId }}">
                </form>

                <button 
                    type="button" 
                    id="dashboardBranchDropdownBtn"
                    onclick="toggleDashboardBranchDropdown()"
                    class="w-full sm:w-auto flex items-center justify-between space-x-3 bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl py-2.5 pl-4 pr-3.5 text-xs font-bold text-slate-700 shadow-sm transition-all duration-150 cursor-pointer"
                >
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                        <span id="dashboardCurrentBranchLabel">
                            @if($selectedBranchId)
                                {{ $allBranches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                            @else
                                Semua Cabang (Global)
                            @endif
                        </span>
                    </div>
                    <svg id="dashboardBranchChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Popover Menu -->
                <div 
                    id="dashboardBranchDropdownMenu" 
                    class="hidden absolute right-0 top-full mt-1.5 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn"
                >
                    <div class="px-3 py-1.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        Pilih Filter Cabang
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
            <div class="inline-flex items-center space-x-2 bg-white border border-slate-200 rounded-xl px-4 py-2 shadow-sm text-xs font-bold text-slate-700 w-fit">
                <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                <span>Unit: {{ auth()->user()->branch->name ?? 'Cabang Anda' }}</span>
            </div>
        @endif
    </div>

    <!-- 4 Primary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Omzet Uang Kasir -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
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
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Rata-rata Omzet / Hari</span>
                <span class="font-bold text-slate-700">Rp {{ number_format($avgDailyOmset, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Total Hari / Laporan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-cyan-600 transition-colors">
                    Total Laporan Harian
                </span>
                <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ number_format($totalReports, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">laporan</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Selisih Kasir Dapur</span>
                <span class="font-bold {{ $totalDifference < 0 ? 'text-rose-600' : ($totalDifference > 0 ? 'text-emerald-600' : 'text-slate-700') }}">
                    {{ $totalDifference < 0 ? '-' : '' }}Rp {{ number_format(abs($totalDifference), 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Total Porsi Terjual -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">
                    Total Porsi Terjual
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ number_format($totalPortionsSold, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">porsi</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Total Dimasak</span>
                <span class="font-bold text-slate-700">{{ number_format($totalPortionsCooked, 0, ',', '.') }} porsi</span>
            </div>
        </div>

        <!-- Cabang Teratas / Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
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
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2 border-t border-slate-100">
                <span>Omzet Cabang Top</span>
                <span class="font-bold text-emerald-600">Rp {{ number_format($topBranchAmount, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    <!-- Section Grafik Utama: Tren Omzet Harian & Komposisi Penjualan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Tren Penjualan Harian (Area Line Chart 8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-2 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Tren Omzet Penjualan Harian</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-tealBrand">Live Analytics</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Grafik dinamika omzet kasir sepanjang periode berjalan</p>
                </div>
                <div class="text-xs text-slate-500 font-bold bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 self-start sm:self-auto">
                    Bulan: {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                </div>
            </div>

            <div class="relative h-64 sm:h-72 w-full pt-2">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Komposisi Pembayaran Kasir (Donut Chart 4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Metode Pembayaran Kasir</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Persentase omzet per kanal penerimaan kasir</p>
            </div>

            <div class="relative h-48 w-full my-auto flex items-center justify-center pt-2">
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

    <!-- Section Middle: Leaderboard Top 5 Menu Masakan & Kontribusi Cabang -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Top 5 Menu Terlaris (6 Cols) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Top 5 Menu Masakan Terlaris</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Menu masakan dengan porsi terjual dan omzet tertinggi</p>
                </div>
                <span class="text-[10px] font-bold text-tealBrand uppercase tracking-wider bg-teal-50 px-2 py-1 rounded-lg">Leaderboard</span>
            </div>

            <div class="space-y-3">
                @forelse($topMenus as $index => $item)
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50/80 transition-colors border border-transparent hover:border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-lg {{ $index === 0 ? 'bg-amber-100 text-amber-700 font-black' : ($index === 1 ? 'bg-slate-200 text-slate-700 font-bold' : 'bg-slate-100 text-slate-500 font-bold') }} flex items-center justify-center text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-slate-900 leading-tight">{{ $item->menu->name ?? 'Menu #' . $item->menu_id }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">Terjual {{ number_format($item->total_sold, 0, ',', '.') }} Porsi</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-extrabold text-xs text-emerald-600 font-sans">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-6 text-center">Belum ada data penjualan menu dapur.</div>
                @endforelse
            </div>
        </div>

        <!-- Performa Penjualan Tiap Cabang (6 Cols) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 mb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Performa & Kontribusi Cabang</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Perbandingan omzet seluruh unit outlet aktif</p>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ count($branchComparisons) }} Cabang</span>
            </div>

            <div class="space-y-4">
                @forelse($branchComparisons as $index => $bc)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded-md bg-navy-900 text-white flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-800">{{ $bc['name'] }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500 font-semibold font-sans">
                                <strong class="text-slate-800 font-bold">Rp {{ number_format($bc['amount'], 0, ',', '.') }}</strong> ({{ $bc['count'] }} lap)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-tealBrand h-2 rounded-full transition-all duration-700 ease-out" style="width: {{ $bc['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-6 text-center">Belum ada data cabang aktif.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Section Bottom: Laporan Dapur Terkini -->
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

        <!-- Desktop & Mobile Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100 bg-slate-50/50">
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
                        <tr class="hover:bg-slate-50/60 transition-colors">
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
                                {{ $report->difference_amount < 0 ? '-' : '' }}Rp {{ number_format(abs($report->difference_amount), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('kitchen-reports.show', $report) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada laporan dapur harian yang tersimpan.</td>
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
    // Inisialisasi Chart.js Area Line & Donut
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
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0B192C',
                            titleColor: '#ffffff',
                            bodyColor: '#E0F7FA',
                            titleFont: { size: 12, weight: 'bold', family: '"Plus Jakarta Sans", sans-serif' },
                            bodyFont: { size: 11, family: '"Plus Jakarta Sans", sans-serif' },
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
                            ticks: {
                                font: { size: 10, family: '"Plus Jakarta Sans", sans-serif' },
                                color: '#94a3b8'
                            }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10, family: '"Plus Jakarta Sans", sans-serif' },
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
    });

    // Custom Dropdown Filter Cabang
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
        document.getElementById('dashboardBranchFilterForm').submit();
    }

    // Close Dropdown on Outside Click
    document.addEventListener('click', function(e) {
        const container = document.getElementById('dashboardBranchDropdownContainer');
        const menu = document.getElementById('dashboardBranchDropdownMenu');
        const chevron = document.getElementById('dashboardBranchChevron');
        if (container && !container.contains(e.target)) {
            if (menu) menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
</script>
@endsection
