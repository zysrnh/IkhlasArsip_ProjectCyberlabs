@extends('layouts.app')

@section('title', 'Detail Laporan Dapur - ' . ($kitchenReport->branch->name ?? 'Cabang') . ' - ' . $kitchenReport->report_date->translatedFormat('d M Y'))

@section('content')
<div class="space-y-6">
    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('kitchen-reports.index') }}" class="hover:text-[#0A97B0]">Input Dapur Harian</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">Detail Laporan</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                Laporan Dapur {{ $kitchenReport->branch->name ?? 'Cabang' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">Tanggal: <strong class="text-gray-800">{{ $kitchenReport->report_date->translatedFormat('l, d F Y') }}</strong> | Penginput: <strong class="text-gray-800">{{ $kitchenReport->user->name ?? 'Sistem' }}</strong></p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak / Print
            </button>

            @if(!auth()->user()->isViewer())
            <a href="{{ route('kitchen-reports.edit', $kitchenReport->id) }}" class="px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-sm font-medium rounded-xl shadow-sm transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Laporan
            </a>
            @endif
        </div>
    </div>

    <!-- Settlement Overview Banner -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Grand Total Penjualan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Penjualan Masakan</span>
            <h3 class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($kitchenReport->grand_total_sales, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Nilai porsi terjual x harga</p>
        </div>

        <!-- Total Omset Kasir -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Omset Kasir</span>
            <h3 class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($kitchenReport->total_omset, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Cash + QRIS + Online</p>
        </div>

        <!-- Status Selisih -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status Selisih</span>
            <div class="mt-1">
                @if(abs($kitchenReport->difference_amount) < 1)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Match (Rp 0)
                    </span>
                @elseif($kitchenReport->difference_amount > 0)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        Lebih +Rp {{ number_format($kitchenReport->difference_amount, 0, ',', '.') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                        Selisih -Rp {{ number_format(abs($kitchenReport->difference_amount), 0, ',', '.') }}
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-gray-500 mt-0.5">Omset vs Penjualan</p>
        </div>

        <!-- Lauk Terbuang (Basi) -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lauk Terbuang (Basi)</span>
            <h3 class="text-xl font-bold text-rose-600 mt-1">Rp {{ number_format($kitchenReport->total_wasted_food, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Kerugian sayur/mie basi</p>
        </div>
    </div>

    <!-- Table of Report Items -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Rincian Menu Masakan Dapur</h3>
            <span class="text-xs text-gray-500">{{ $kitchenReport->items->count() }} Menu Terdata</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-100/75 border-b border-gray-200 text-[11px] font-bold text-gray-700 uppercase tracking-wider">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-4 min-w-[180px]">Nama Masakan</th>
                        <th class="py-3 px-3 text-center w-24">Sisa Kemarin</th>
                        <th class="py-3 px-3 text-center w-24">Masak Hari Ini</th>
                        <th class="py-3 px-3 text-center w-24">Total Masakan</th>
                        <th class="py-3 px-3 text-center w-24">Terjual</th>
                        <th class="py-3 px-3 text-center w-24">Sisa Hari Ini</th>
                        <th class="py-3 px-3 text-right w-24">Harga (Rp)</th>
                        <th class="py-3 px-4 text-right w-32">Total Penjualan</th>
                        <th class="py-3 px-3 text-right w-32">Sisa Bisa Dijual</th>
                        <th class="py-3 px-3 text-right w-32">Lauk Terbuang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800 font-medium">
                    @foreach($kitchenReport->items as $idx => $item)
                    <tr class="hover:bg-gray-50/60 transition">
                        <!-- No -->
                        <td class="py-2.5 px-3 text-center text-gray-400">
                            {{ $item->menu->order_number ?? ($idx + 1) }}
                        </td>

                        <!-- Menu -->
                        <td class="py-2.5 px-4 font-semibold text-gray-900">
                            <div class="flex items-center gap-2">
                                <span>{{ $item->menu->name ?? 'Menu' }}</span>
                                @if($item->menu && $item->menu->is_perishable)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-100">Basi</span>
                                @endif
                            </div>
                        </td>

                        <!-- Sisa Kemarin -->
                        <td class="py-2.5 px-3 text-center text-gray-600">
                            {{ $item->yesterday_remaining }}
                        </td>

                        <!-- Masak Hari Ini -->
                        <td class="py-2.5 px-3 text-center font-bold text-amber-900">
                            {{ $item->cooked_today }}
                        </td>

                        <!-- Total Masakan -->
                        <td class="py-2.5 px-3 text-center font-bold text-gray-900">
                            {{ $item->total_cooked }}
                        </td>

                        <!-- Terjual -->
                        <td class="py-2.5 px-3 text-center font-bold text-blue-900">
                            {{ $item->sold }}
                        </td>

                        <!-- Sisa Hari Ini -->
                        <td class="py-2.5 px-3 text-center font-bold text-gray-800">
                            {{ $item->remaining }}
                        </td>

                        <!-- Unit Price -->
                        <td class="py-2.5 px-3 text-right text-gray-600">
                            {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>

                        <!-- Total Sales -->
                        <td class="py-2.5 px-4 text-right font-bold text-emerald-700 bg-emerald-50/20">
                            Rp {{ number_format($item->total_sales, 0, ',', '.') }}
                        </td>

                        <!-- Sisa Bisa Dijual -->
                        <td class="py-2.5 px-3 text-right text-[#0A97B0] bg-cyan-50/20">
                            Rp {{ number_format($item->remaining_sellable_amount, 0, ',', '.') }}
                        </td>

                        <!-- Lauk Terbuang -->
                        <td class="py-2.5 px-3 text-right text-rose-600 bg-rose-50/20">
                            Rp {{ number_format($item->wasted_food_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-900 text-white font-bold text-xs border-t-2 border-gray-800">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right uppercase tracking-wider text-gray-300">
                            GRAND TOTAL LAPORAN:
                        </td>
                        <td class="py-3.5 px-3 text-center text-amber-300">
                            {{ number_format($kitchenReport->items->sum('total_cooked')) }}
                        </td>
                        <td class="py-3.5 px-3 text-center text-blue-300">
                            {{ number_format($kitchenReport->items->sum('sold')) }}
                        </td>
                        <td class="py-3.5 px-3 text-center text-gray-300">
                            {{ number_format($kitchenReport->items->sum('remaining')) }}
                        </td>
                        <td class="py-3.5 px-3"></td>
                        <td class="py-3.5 px-4 text-right text-emerald-400 text-sm font-extrabold">
                            Rp {{ number_format($kitchenReport->grand_total_sales, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right text-cyan-300">
                            Rp {{ number_format($kitchenReport->total_remaining_sellable, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-right text-rose-400">
                            Rp {{ number_format($kitchenReport->total_wasted_food, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Settlement Breakdown Bottom Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <!-- Rincian Kasir -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
            <h4 class="font-bold text-sm text-gray-900 border-b border-gray-100 pb-2.5">Rincian Pembayaran Kasir</h4>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Pendapatan Cash (Laci):</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($kitchenReport->cash_income, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Pendapatan QRIS / Transfer:</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($kitchenReport->qris_income, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Pendapatan Online Food:</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($kitchenReport->online_food_income, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2.5 bg-emerald-50/75 rounded-xl px-3 text-emerald-950 font-bold">
                    <span>TOTAL OMSET HARI INI:</span>
                    <span class="text-emerald-700 text-sm">Rp {{ number_format($kitchenReport->total_omset, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Catatan & Discrepancy Info -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
            <h4 class="font-bold text-sm text-gray-900 border-b border-gray-100 pb-2.5">Catatan & Evaluasi Laporan</h4>
            @if($kitchenReport->notes)
                <p class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-100">
                    {{ $kitchenReport->notes }}
                </p>
            @else
                <p class="text-xs text-gray-400 italic">Tidak ada catatan khusus pada laporan ini.</p>
            @endif

            <div class="pt-2 text-xs text-gray-500 space-y-1">
                <div>Dibuat pada: <strong>{{ $kitchenReport->created_at->translatedFormat('d F Y H:i') }}</strong></div>
                <div>Terakhir diperbarui: <strong>{{ $kitchenReport->updated_at->translatedFormat('d F Y H:i') }}</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection
