@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Filter Cabang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Selamat datang, <strong class="text-slate-800 font-bold">{{ auth()->user()->name }}</strong> &mdash; data per {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}.
            </p>
        </div>

        <!-- Filter Cabang Dropdown Pill -->
        @if(auth()->user()->canAccessAllBranches())
            <div>
                <form method="GET" action="{{ route('dashboard') }}" class="inline-block">
                    <div class="relative">
                        <select 
                            name="branch_id" 
                            onchange="this.form.submit()" 
                            class="appearance-none bg-white border border-slate-200 rounded-full py-2 pl-8 pr-8 text-xs font-bold text-slate-700 shadow-sm focus:outline-none focus:border-tealBrand cursor-pointer"
                        >
                            <option value="">Semua Cabang</option>
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Teal Indicator Dot -->
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-tealBrand pointer-events-none"></div>
                        <!-- Caret Icon -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="inline-flex items-center space-x-2 bg-white border border-slate-200 rounded-full px-4 py-1.5 shadow-sm text-xs font-bold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-tealBrand"></span>
                <span>{{ auth()->user()->branch->name ?? 'Cabang' }}</span>
            </div>
        @endif
    </div>

    <!-- 4 Summary Stat Cards (Sesuai Mockup) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Pendapatan -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-tealBrand flex flex-col justify-between">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
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
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-cyan-500 flex flex-col justify-between">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
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
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-emerald-500 flex flex-col justify-between">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
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
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm border-l-4 border-l-amber-500 flex flex-col justify-between">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
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

    <!-- Middle Section: 2 Analytics Cards (Perbandingan Cabang & Jenis Transaksi) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Card Kiri: Perbandingan Cabang (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
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
                            <div class="bg-tealBrand h-2 rounded-full transition-all duration-500" style="width: {{ $bc['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-4 text-center">Belum ada data cabang.</div>
                @endforelse
            </div>
        </div>

        <!-- Card Kanan: Jenis Transaksi (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="mb-5">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Jenis Transaksi</h3>
            </div>

            <div class="space-y-4">
                <!-- Penjualan Tunai -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700">Penjualan Tunai</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Penjualan Tunai']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Penjualan Tunai']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Penjualan Kredit -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                        <span class="font-semibold text-slate-700">Penjualan Kredit</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Penjualan Kredit']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Penjualan Kredit']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Retur Penjualan -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="font-semibold text-slate-700">Retur Penjualan</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ $typeSummaries['Retur Penjualan']['count'] ?? 0 }}x &bull; Rp {{ number_format($typeSummaries['Retur Penjualan']['amount'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Transfer Cabang -->
                <div class="flex items-center justify-between text-xs">
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
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Transaksi Terkini</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-tealBrand hover:text-tealBrand-hover inline-flex items-center space-x-1">
                <span>Lihat Semua</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
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
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-3 font-mono text-slate-500">{{ $trx->code }}</td>
                            <td class="py-3 px-3 text-slate-500">{{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800">{{ $trx->branch->name ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @if($trx->type === 'Penjualan Tunai')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        Penjualan Tunai
                                    </span>
                                @elseif($trx->type === 'Penjualan Kredit')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">
                                        Penjualan Kredit
                                    </span>
                                @elseif($trx->type === 'Retur Penjualan')
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                        Retur Penjualan
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
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
@endsection
