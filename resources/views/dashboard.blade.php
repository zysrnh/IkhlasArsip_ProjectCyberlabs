@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fadeIn">

    <!-- Top Header & Filter Cabang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Selamat datang, <strong class="text-slate-800 font-bold">{{ auth()->user()->name }}</strong> &mdash; data per {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}.
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
                    class="w-full sm:w-auto flex items-center justify-between space-x-3 bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-full py-2 pl-4 pr-3.5 text-xs font-bold text-slate-700 shadow-sm transition-all duration-150 cursor-pointer"
                >
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-tealBrand shrink-0"></span>
                        <span id="dashboardCurrentBranchLabel">
                            @if($selectedBranchId)
                                {{ $allBranches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                            @else
                                Semua Cabang
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
                    class="hidden absolute right-0 top-full mt-1.5 w-52 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 animate-fadeIn"
                >
                    <div class="px-3 py-1.5 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        Pilih Cabang
                    </div>
                    
                    <button 
                        type="button" 
                        onclick="selectDashboardBranchOption('', 'Semua Cabang')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedBranchId) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Cabang</span>
                        @if(empty($selectedBranchId))
                            <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
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
                                <svg class="w-4 h-4 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="inline-flex items-center space-x-2 bg-white border border-slate-200 rounded-full px-4 py-2 shadow-sm text-xs font-bold text-slate-700 w-fit">
                <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                <span>{{ auth()->user()->branch->name ?? 'Cabang' }}</span>
            </div>
        @endif
    </div>

    <!-- 4 Summary Stat Cards (Carousel di Mobile, 4 Cols Grid di Desktop) -->
    <div class="relative">
        <div id="statCardsCarousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-none gap-3.5 -mx-4 px-4 pb-2 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0 lg:pb-0 lg:grid lg:grid-cols-4 lg:gap-4 lg:overflow-visible">
            
            <!-- Total Pendapatan -->
            <div class="min-w-[78vw] sm:min-w-[42vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-tealBrand flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-default group">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-tealBrand transition-colors">
                    Total Pendapatan
                </div>
                <div class="my-2">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-sans">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium">
                    {{ $selectedBranchId ? 'Cabang terpilih' : 'Seluruh cabang' }}
                </div>
            </div>

            <!-- Total Transaksi -->
            <div class="min-w-[78vw] sm:min-w-[42vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-cyan-500 flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-default group">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-cyan-600 transition-colors">
                    Total Transaksi
                </div>
                <div class="my-2">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $totalTransactions }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium">
                    Entri data
                </div>
            </div>

            <!-- Total Qty -->
            <div class="min-w-[78vw] sm:min-w-[42vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-emerald-500 flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-default group">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">
                    Total QTY
                </div>
                <div class="my-2">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $totalQty }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium">
                    Unit terjual
                </div>
            </div>

            <!-- Cabang Aktif -->
            <div class="min-w-[78vw] sm:min-w-[42vw] lg:min-w-0 snap-center shrink-0 lg:shrink bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-amber-500 flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-default group">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition-colors">
                    Cabang Aktif
                </div>
                <div class="my-2">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $activeBranchesCount }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium">
                    Terhubung ke sistem
                </div>
            </div>

        </div>

        <!-- Carousel Indicators (Mobile only) -->
        <div class="flex items-center justify-center space-x-1.5 pt-2 lg:hidden" id="statCardsDots">
            <button type="button" onclick="scrollStatCarousel(0)" class="w-6 h-1.5 rounded-full bg-tealBrand transition-all duration-300 stat-dot" data-index="0" aria-label="Slide 1"></button>
            <button type="button" onclick="scrollStatCarousel(1)" class="w-1.5 h-1.5 rounded-full bg-slate-300 transition-all duration-300 stat-dot" data-index="1" aria-label="Slide 2"></button>
            <button type="button" onclick="scrollStatCarousel(2)" class="w-1.5 h-1.5 rounded-full bg-slate-300 transition-all duration-300 stat-dot" data-index="2" aria-label="Slide 3"></button>
            <button type="button" onclick="scrollStatCarousel(3)" class="w-1.5 h-1.5 rounded-full bg-slate-300 transition-all duration-300 stat-dot" data-index="3" aria-label="Slide 4"></button>
        </div>
    </div>

    <!-- Middle Section: 2 Analytics Cards (Perbandingan Cabang & Jenis Transaksi) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Card Kiri: Perbandingan Cabang (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="mb-5">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Perbandingan Cabang</h3>
            </div>

            <div class="space-y-5">
                @forelse($branchComparisons as $bc)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800">{{ $bc['name'] }}</span>
                            <span class="text-[11px] text-slate-400 font-medium font-mono">
                                {{ $bc['count'] }} trx &bull; Rp {{ number_format($bc['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-tealBrand h-2 rounded-full transition-all duration-700 ease-out" style="width: {{ $bc['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-4 text-center">Belum ada data cabang.</div>
                @endforelse
            </div>
        </div>

        <!-- Card Kanan: Jenis Transaksi (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="mb-5">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Jenis Transaksi</h3>
            </div>

            <div class="space-y-4">
                <!-- Penjualan Tunai -->
                <div class="flex items-center justify-between text-xs p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700">Penjualan Tunai</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Penjualan Tunai']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Penjualan Tunai']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Penjualan Kredit -->
                <div class="flex items-center justify-between text-xs p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                        <span class="font-semibold text-slate-700">Penjualan Kredit</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Penjualan Kredit']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Penjualan Kredit']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Retur Penjualan -->
                <div class="flex items-center justify-between text-xs p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="font-semibold text-slate-700">Retur Penjualan</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Retur Penjualan']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Retur Penjualan']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Transfer Cabang -->
                <div class="flex items-center justify-between text-xs p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-slate-700">Transfer Cabang</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Transfer Cabang']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Transfer Cabang']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Section: Transaksi Terkini Table (Sesuai Mockup) -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Transaksi Terkini</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-tealBrand hover:text-tealBrand-hover inline-flex items-center space-x-1 group">
                <span>Lihat Semua</span>
                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto -mx-5 sm:mx-0 px-5 sm:px-0">
            <table class="w-full text-left text-xs text-slate-700 min-w-[600px] sm:min-w-full">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Cabang</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 font-mono text-slate-500">{{ $trx->code }}</td>
                            <td class="py-3 px-3 text-slate-500">{{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800">{{ $trx->branch->name ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @if($trx->type === 'Penjualan Tunai')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        Penjualan Tunai
                                    </span>
                                @elseif($trx->type === 'Penjualan Kredit')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">
                                        Penjualan Kredit
                                    </span>
                                @elseif($trx->type === 'Retur Penjualan')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                        Retur Penjualan
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        Transfer Cabang
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600">{{ $trx->customer_name }}</td>
                            <td class="py-3 px-3 text-right font-bold {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada transaksi terkini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function scrollStatCarousel(index) {
        const carousel = document.getElementById('statCardsCarousel');
        if (!carousel) return;
        const cards = carousel.querySelectorAll('.snap-center');
        if (cards[index]) {
            cards[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    const statCarousel = document.getElementById('statCardsCarousel');
    if (statCarousel) {
        statCarousel.addEventListener('scroll', function() {
            const scrollLeft = statCarousel.scrollLeft;
            const firstCard = statCarousel.querySelector('.snap-center');
            const cardWidth = firstCard ? firstCard.offsetWidth : 280;
            const activeIndex = Math.min(3, Math.max(0, Math.round(scrollLeft / (cardWidth + 14))));
            
            const dots = document.querySelectorAll('.stat-dot');
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.remove('w-1.5', 'bg-slate-300');
                    dot.classList.add('w-6', 'bg-tealBrand');
                } else {
                    dot.classList.remove('w-6', 'bg-tealBrand');
                    dot.classList.add('w-1.5', 'bg-slate-300');
                }
            });
        }, { passive: true });
    }

    function toggleDashboardBranchDropdown() {
        const menu = document.getElementById('dashboardBranchDropdownMenu');
        const chevron = document.getElementById('dashboardBranchChevron');
        if (!menu) return;
        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function selectDashboardBranchOption(branchId, branchName) {
        document.getElementById('selectedBranchInput').value = branchId;
        document.getElementById('dashboardCurrentBranchLabel').textContent = branchName;
        document.getElementById('dashboardBranchFilterForm').submit();
    }

    document.addEventListener('click', function(event) {
        const container = document.getElementById('dashboardBranchDropdownContainer');
        const menu = document.getElementById('dashboardBranchDropdownMenu');
        const chevron = document.getElementById('dashboardBranchChevron');
        if (container && menu && !container.contains(event.target)) {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
</script>
@endpush
@endsection
