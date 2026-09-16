@extends('layouts.app')

@section('title', 'Riwayat Input Dapur Harian')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Input Dapur Harian</h1>
            <p class="text-sm text-gray-500 mt-1">Pencatatan porsi masakan dapur harian, sisa otomatis, dan rekapan omset kasir per cabang.</p>
        </div>
        @if(!auth()->user()->isViewer())
        <div class="flex items-center gap-3">
            <a href="{{ route('kitchen-reports.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-sm font-medium rounded-xl shadow-sm transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Input Masakan Hari Ini
            </a>
        </div>
        @endif
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Laporan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Laporan</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_reports']) }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Catatan harian</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Total Omset -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Omset Kasir</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Cash + QRIS + Online</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Estimasi Sisa Bisa Dijual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sisa Lauk Bisa Dijual</p>
                <h3 class="text-2xl font-bold text-[#0A97B0] mt-1">Rp {{ number_format($stats['total_sellable'], 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Lauk protein tersimpan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-[#0A97B0] flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>

        <!-- Total Lauk Terbuang (Basi) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lauk Terbuang (Basi)</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($stats['total_wasted'], 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Kerugian sayur/makanan basi</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Card with Custom Flatpickr & Custom Dropdown -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
        <form action="{{ route('kitchen-reports.index') }}" method="GET" id="kitchenFilterForm" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                
                <!-- Filter Cabang (jika Superadmin / Kepala Cabang) -->
                @if(auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1))
                <div class="lg:col-span-4 relative" id="branchDropdownWrapper">
                    <input type="hidden" name="branch_id" id="filterBranchInput" value="{{ request('branch_id') }}">
                    <button 
                        type="button" 
                        onclick="toggleKitchenDropdown('branchDropdownMenu')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-medium hover:border-tealBrand focus:outline-none transition-colors"
                    >
                        <div class="flex items-center space-x-2 truncate">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span id="selectedBranchLabel" class="truncate font-semibold">
                                @if(request('branch_id'))
                                    {{ $branches->firstWhere('id', request('branch_id'))?->name ?? 'Semua Cabang' }}
                                @else
                                    Semua Cabang
                                @endif
                            </span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Content -->
                    <div id="branchDropdownMenu" class="hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 py-1 max-h-56 overflow-y-auto">
                        <button 
                            type="button" 
                            onclick="selectKitchenFilter('branch', '', 'Semua Cabang')" 
                            class="w-full text-left px-3.5 py-2 text-xs hover:bg-slate-50 flex items-center justify-between {{ !request('branch_id') ? 'font-bold text-tealBrand bg-teal-50/50' : 'text-slate-700' }}"
                        >
                            <span>Semua Cabang</span>
                            @if(!request('branch_id'))
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                        @foreach($branches as $b)
                            <button 
                                type="button" 
                                onclick="selectKitchenFilter('branch', '{{ $b->id }}', '{{ $b->name }}')" 
                                class="w-full text-left px-3.5 py-2 text-xs hover:bg-slate-50 flex items-center justify-between {{ request('branch_id') == $b->id ? 'font-bold text-tealBrand bg-teal-50/50' : 'text-slate-700' }}"
                            >
                                <span>{{ $b->name }}</span>
                                @if(request('branch_id') == $b->id)
                                    <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Tanggal Dari (Flatpickr Custom) -->
                <div class="{{ (auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1)) ? 'lg:col-span-3' : 'lg:col-span-4' }} relative">
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
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:border-tealBrand cursor-pointer"
                    >
                </div>

                <!-- Tanggal Sampai (Flatpickr Custom) -->
                <div class="{{ (auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1)) ? 'lg:col-span-3' : 'lg:col-span-4' }} relative">
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
                        class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:border-tealBrand cursor-pointer"
                    >
                </div>

                <!-- Action Filter & Reset Buttons -->
                <div class="{{ (auth()->user()->isSuperAdmin() || (auth()->user()->isKepalaCabang() && count($branches) > 1)) ? 'lg:col-span-2' : 'lg:col-span-4' }} flex items-center gap-2">
                    <button type="submit" class="w-full px-4 py-2.5 bg-[#0B192C] hover:bg-[#142B4D] text-white text-xs font-bold rounded-xl transition duration-150 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['branch_id', 'date_from', 'date_to']))
                    <a href="{{ route('kitchen-reports.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition shrink-0" title="Reset Filter">
                        Reset
                    </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Cabang</th>
                        <th class="py-3.5 px-4 text-right">Penjualan Masakan</th>
                        <th class="py-3.5 px-4 text-right">Total Omset Kasir</th>
                        <th class="py-3.5 px-4 text-center">Status Selisih</th>
                        <th class="py-3.5 px-4 text-right">Lauk Basi / Terbuang</th>
                        <th class="py-3.5 px-4">Penginput</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($reports as $r)
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Tanggal -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ $r->report_date->translatedFormat('d F Y') }}</div>
                            <div class="text-xs text-slate-400">{{ $r->report_date->translatedFormat('l') }}</div>
                        </td>

                        <!-- Cabang -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $r->branch->name ?? '-' }}
                            </span>
                        </td>

                        <!-- Penjualan Masakan -->
                        <td class="py-3.5 px-4 text-right font-medium text-slate-900 whitespace-nowrap">
                            Rp {{ number_format($r->grand_total_sales, 0, ',', '.') }}
                        </td>

                        <!-- Total Omset Kasir -->
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 whitespace-nowrap">
                            Rp {{ number_format($r->total_omset, 0, ',', '.') }}
                        </td>

                        <!-- Status Selisih (sesuai box kuning) -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if(abs($r->difference_amount) < 1)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Cocok (Match)
                                </span>
                            @elseif($r->difference_amount > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200" title="Uang kasir lebih banyak dari masakan terjual">
                                    Lebih +Rp {{ number_format($r->difference_amount, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200" title="Uang kasir kurang dari masakan terjual">
                                    Selisih -Rp {{ number_format(abs($r->difference_amount), 0, ',', '.') }}
                                </span>
                            @endif
                        </td>

                        <!-- Lauk Terbuang -->
                        <td class="py-3.5 px-4 text-right text-rose-600 font-semibold whitespace-nowrap">
                            Rp {{ number_format($r->total_wasted_food, 0, ',', '.') }}
                        </td>

                        <!-- Penginput -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                            {{ $r->user->name ?? 'Sistem' }}
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('kitchen-reports.show', $r->id) }}" class="p-2 text-slate-500 hover:text-[#0A97B0] hover:bg-slate-100 rounded-xl transition" title="Lihat Rincian Laporan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                @if(!auth()->user()->isViewer())
                                <a href="{{ route('kitchen-reports.edit', $r->id) }}" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-xl transition" title="Edit Laporan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form action="{{ route('kitchen-reports.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan dapur ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded-xl transition" title="Hapus Laporan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="font-bold text-slate-600">Belum ada catatan laporan dapur.</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Input Masakan Hari Ini" untuk memulai input baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Flatpickr & Dropdown Logic -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Custom Flatpickr Indonesian Theme
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

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('branchDropdownMenu');
            const wrapper = document.getElementById('branchDropdownWrapper');
            if (dropdown && wrapper && !wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    function toggleKitchenDropdown(id) {
        const menu = document.getElementById(id);
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function selectKitchenFilter(type, value, label) {
        if (type === 'branch') {
            document.getElementById('filterBranchInput').value = value;
            document.getElementById('selectedBranchLabel').innerText = label;
            document.getElementById('branchDropdownMenu').classList.add('hidden');
        }
    }
</script>
@endsection
