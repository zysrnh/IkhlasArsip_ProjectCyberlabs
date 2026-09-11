@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Branch Indicator -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Data Transaksi</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Manajemen resume transaksi, filtering laporan, bulk delete, dan export dokumen cabang.
            </p>
        </div>

        @if(auth()->user()->canAccessAllBranches())
            <div>
                <form method="GET" action="{{ route('transactions.index') }}" class="inline-block">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">

                    <div class="relative">
                        <select 
                            name="branch_id" 
                            onchange="this.form.submit()" 
                            class="appearance-none bg-white border border-slate-200 rounded-full py-2 pl-8 pr-8 text-xs font-bold text-slate-700 shadow-sm focus:outline-none focus:border-tealBrand cursor-pointer"
                        >
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-tealBrand pointer-events-none"></div>
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

    <!-- Search & Action Buttons Bar (Sesuai Mockup) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        
        <!-- Search Input -->
        <div class="flex-1 max-w-xl">
            <form method="GET" action="{{ route('transactions.index') }}" class="relative">
                <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">

                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari ID, deskripsi, atau customer..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-tealBrand focus:ring-1 focus:ring-tealBrand shadow-sm transition"
                >
            </form>
        </div>

        <!-- Action Buttons (Import Excel, Export PDF, + Tambah) -->
        <div class="flex items-center space-x-2.5">
            <!-- Import Excel -->
            <button 
                type="button" 
                onclick="openImportModal()"
                class="px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-2 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import Excel</span>
            </button>

            <!-- Export PDF -->
            <a 
                href="{{ route('transactions.export-pdf', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-white hover:bg-rose-50 border border-rose-400 text-rose-600 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors"
                title="Download Laporan PDF Ber-KOP Resmi"
            >
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Export PDF</span>
            </a>

            <!-- Export Excel -->
            <a 
                href="{{ route('transactions.export-excel', request()->query()) }}" 
                class="px-3.5 py-2.5 bg-white hover:bg-emerald-50 border border-emerald-500 text-emerald-600 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors"
                title="Download Laporan Format Excel (.xlsx)"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export Excel</span>
            </a>

            <!-- + Tambah -->
            <button 
                type="button" 
                onclick="openCreateModal()"
                class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors"
            >
                <span>+</span>
                <span>Tambah</span>
            </button>
        </div>

    </div>

    <!-- Filter & Sorting Card (Sesuai Mockup) -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-3">
        
        <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>FILTER & SORTING</span>
        </div>

        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="search" value="{{ request('search') }}">

            <!-- Filter Cabang -->
            <div>
                <select 
                    name="branch_id" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:border-tealBrand"
                    {{ !auth()->user()->canAccessAllBranches() ? 'disabled' : '' }}
                >
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenis -->
            <div>
                <select 
                    name="type" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:border-tealBrand"
                >
                    <option value="">Semua Jenis</option>
                    <option value="Penjualan Tunai" {{ request('type') === 'Penjualan Tunai' ? 'selected' : '' }}>Penjualan Tunai</option>
                    <option value="Penjualan Kredit" {{ request('type') === 'Penjualan Kredit' ? 'selected' : '' }}>Penjualan Kredit</option>
                    <option value="Retur Penjualan" {{ request('type') === 'Retur Penjualan' ? 'selected' : '' }}>Retur Penjualan</option>
                    <option value="Transfer Cabang" {{ request('type') === 'Transfer Cabang' ? 'selected' : '' }}>Transfer Cabang</option>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="relative">
                <input 
                    type="date" 
                    name="date_from" 
                    value="{{ request('date_from') }}" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:border-tealBrand"
                >
            </div>

            <!-- Tanggal Sampai -->
            <div class="relative">
                <input 
                    type="date" 
                    name="date_to" 
                    value="{{ request('date_to') }}" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:border-tealBrand"
                >
            </div>

            <!-- Sorting (Sesuai Brief) -->
            <div>
                <select 
                    name="sort" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:outline-none focus:border-tealBrand"
                >
                    <option value="terbaru" {{ request('sort') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="terbanyak" {{ request('sort') === 'terbanyak' ? 'selected' : '' }}>Transaksi Terbanyak (Nominal)</option>
                    <option value="tersedikit" {{ request('sort') === 'tersedikit' ? 'selected' : '' }}>Transaksi Paling Sedikit</option>
                </select>
            </div>
        </form>

    </div>

    <!-- Bulk Action Toolbar (Muncul saat ada checkbox dicentang) -->
    <div id="bulkTrxToolbar" class="hidden bg-navy-900 text-white p-3.5 rounded-xl shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fadeIn">
        <div class="flex items-center space-x-2 text-xs font-bold px-2">
            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
            <span><span id="selectedTrxCount">0</span> transaksi dipilih</span>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Form Bulk Delete -->
            <form id="bulkDeleteForm" action="{{ route('transactions.bulk-delete') }}" method="POST" class="inline">
                @csrf
                <div id="bulkDeleteInputs"></div>
                <button 
                    type="submit" 
                    onclick="return confirm('Apakah Anda yakin ingin memindahkan transaksi yang dipilih ke tempat sampah?');" 
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center space-x-1.5 shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Terpilih (Pindah ke Sampah)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Info Bar -->
    <div class="flex items-center justify-between text-xs px-1">
        <div class="text-slate-500 font-medium">
            Menampilkan <strong class="text-slate-800 font-bold">{{ $transactions->total() }}</strong> transaksi
            @if(request()->hasAny(['search', 'type', 'date_from', 'date_to', 'sort', 'branch_id']))
                &bull; <a href="{{ route('transactions.index') }}" class="text-tealBrand hover:underline font-semibold">Reset Filter</a>
            @endif
        </div>
        <div class="text-sm font-bold text-slate-700">
            Total : <span class="text-emerald-600 font-extrabold font-sans">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Data Table (Sesuai Mockup) -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Transaksi</h3>
            <span class="text-xs text-slate-400 font-medium font-mono">Page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3 w-8 text-center">
                            <input type="checkbox" id="selectAllTrx" onchange="toggleSelectAllTrx(this)" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                        </th>
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Cabang</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Deskripsi</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3 text-center">QTY</th>
                        <th class="py-3 px-3 text-right">Jumlah</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Checkbox Row -->
                            <td class="py-3 px-3 text-center">
                                @if(auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id)
                                    <input type="checkbox" name="trx_ids[]" value="{{ $trx->id }}" onchange="updateTrxSelection()" class="trx-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                                @else
                                    <input type="checkbox" disabled class="rounded border-slate-200 text-slate-300 cursor-not-allowed opacity-40">
                                @endif
                            </td>

                            <!-- ID -->
                            <td class="py-3 px-3 font-mono text-slate-500 font-bold">{{ $trx->code }}</td>
                            
                            <!-- Tanggal -->
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">
                                {{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}
                            </td>
                            
                            <!-- Cabang -->
                            <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">
                                {{ $trx->branch->name ?? '-' }}
                            </td>
                            
                            <!-- Jenis (Badge) -->
                            <td class="py-3 px-3 whitespace-nowrap">
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

                            <!-- Deskripsi -->
                            <td class="py-3 px-3 text-slate-600 max-w-xs truncate" title="{{ $trx->notes }}">
                                {{ $trx->notes ?: '-' }}
                            </td>

                            <!-- Customer -->
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap font-medium">
                                {{ $trx->customer_name }}
                            </td>

                            <!-- Qty -->
                            <td class="py-3 px-3 text-center text-slate-700 font-bold font-mono">
                                {{ $trx->qty }}
                            </td>

                            <!-- Jumlah -->
                            <td class="py-3 px-3 text-right font-extrabold whitespace-nowrap {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </td>

                            <!-- Aksi (CRUD Sesuai Izin) -->
                            <td class="py-3 px-3 text-center">
                                @if(auth()->user()->canAccessAllBranches() || auth()->user()->branch_id === $trx->branch_id)
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button 
                                            type="button" 
                                            onclick="openEditModal({{ json_encode($trx) }})"
                                            class="p-1 text-slate-400 hover:text-slate-800 transition-colors"
                                            title="Edit Transaksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Pindahkan transaksi {{ $trx->code }} ke tempat sampah?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                                title="Hapus Transaksi (Pindah ke Sampah)"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-[10px] italic">Read-only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 font-medium">
                                Tidak ada transaksi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="pt-5 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

</div>

<!-- Modal Tambah Transaksi -->
<div id="createModal" class="fixed inset-0 z-50 bg-navy-950/70 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-navy-900 text-white flex items-center justify-between">
            <h3 class="font-extrabold text-sm tracking-tight">Tambah Transaksi Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
        </div>

        <form action="{{ route('transactions.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- ID & Cabang -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">ID Transaksi</label>
                    <input type="text" name="code" value="{{ $nextCode }}" placeholder="Contoh: TRX-013" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg font-mono font-bold focus:bg-white focus:outline-none focus:border-tealBrand">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Cabang</label>
                    @if(auth()->user()->canAccessAllBranches())
                        <select name="branch_id" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        <input type="text" disabled value="{{ auth()->user()->branch->name ?? 'Cabang' }}" class="w-full px-3.5 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-lg font-bold text-slate-600">
                    @endif
                </div>
            </div>

            <!-- Tanggal & Jenis -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Jenis Transaksi</label>
                    <select name="type" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                        <option value="Penjualan Tunai">Penjualan Tunai</option>
                        <option value="Penjualan Kredit">Penjualan Kredit</option>
                        <option value="Retur Penjualan">Retur Penjualan</option>
                        <option value="Transfer Cabang">Transfer Cabang</option>
                    </select>
                </div>
            </div>

            <!-- Customer -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Nama Customer / Tujuan</label>
                <input type="text" name="customer_name" required placeholder="Contoh: CV Bumi Pertiwi" class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
            </div>

            <!-- Qty & Jumlah Nominal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">QTY (Unit)</label>
                    <input type="number" name="qty" min="1" value="1" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-bold focus:outline-none focus:border-tealBrand">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Jumlah Nominal (Rp)</label>
                    <input type="number" name="amount" step="1000" placeholder="16700000" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-bold focus:outline-none focus:border-tealBrand">
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Deskripsi / Keterangan</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Penjualan produk kategori A..." class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-lg transition-colors">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Transaksi -->
<div id="editModal" class="fixed inset-0 z-50 bg-navy-950/70 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-navy-900 text-white flex items-center justify-between">
            <h3 class="font-extrabold text-sm tracking-tight">Edit Transaksi <span id="editCodeDisplay" class="font-mono text-teal-300"></span></h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
        </div>

        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <!-- Cabang & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Cabang</label>
                    @if(auth()->user()->canAccessAllBranches())
                        <select name="branch_id" id="editBranchId" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" id="editBranchName" disabled class="w-full px-3.5 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-lg font-bold text-slate-600">
                    @endif
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" id="editDate" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                </div>
            </div>

            <!-- Jenis & Customer -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Jenis Transaksi</label>
                    <select name="type" id="editType" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                        <option value="Penjualan Tunai">Penjualan Tunai</option>
                        <option value="Penjualan Kredit">Penjualan Kredit</option>
                        <option value="Retur Penjualan">Retur Penjualan</option>
                        <option value="Transfer Cabang">Transfer Cabang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Customer / Tujuan</label>
                    <input type="text" name="customer_name" id="editCustomer" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand">
                </div>
            </div>

            <!-- Qty & Jumlah Nominal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">QTY (Unit)</label>
                    <input type="number" name="qty" id="editQty" min="1" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-bold focus:outline-none focus:border-tealBrand">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Jumlah Nominal (Rp)</label>
                    <input type="number" name="amount" id="editAmount" step="1000" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-bold focus:outline-none focus:border-tealBrand">
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Deskripsi / Keterangan</label>
                <textarea name="notes" id="editNotes" rows="2" class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium focus:outline-none focus:border-tealBrand"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-lg transition-colors">
                    Perbarui Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div id="importModal" class="fixed inset-0 z-50 bg-navy-950/70 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 w-full max-w-lg shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-slate-800 tracking-tight">Import dari Excel</h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('transactions.import-excel') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            <!-- Amber Warning Notice -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start space-x-3 text-amber-900">
                <div class="shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="text-xs leading-relaxed">
                    <strong class="font-bold">Pastikan format Excel sesuai template</strong> yang disediakan kepala cabang. Kolom: <span class="font-medium text-amber-950">Tanggal, Cabang, Jenis, Deskripsi, Customer, Jumlah, Qty</span>
                </div>
            </div>

            <!-- Drag and Drop Dropzone -->
            <div id="dropzoneContainer" onclick="document.getElementById('importFileInput').click()" class="border-2 border-dashed border-slate-200 hover:border-tealBrand rounded-2xl p-7 text-center transition-all bg-slate-50/60 hover:bg-teal-50/20 cursor-pointer group">
                <input type="file" id="importFileInput" name="file" required accept=".xlsx,.xls,.csv" class="hidden" onchange="handleFileSelect(this)">
                
                <div class="w-12 h-12 mx-auto mb-3 bg-emerald-100 text-emerald-600 group-hover:scale-105 rounded-full flex items-center justify-center transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div class="text-xs font-bold text-slate-800 mb-1">
                    Pilih File Excel / CSV
                </div>
                <div class="text-[11px] text-slate-400 font-medium mb-1">
                    Drag and drop berkas ke sini, atau klik untuk memilih (.xlsx, .xls, .csv)
                </div>
                <div id="selectedFileName" class="hidden mt-2.5 inline-flex items-center px-3 py-1 rounded-md bg-emerald-100 text-emerald-800 text-xs font-bold">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="fileNameText">file.xlsx</span>
                </div>
            </div>

            <!-- Download Template Link -->
            <div class="text-center pt-1">
                <a href="{{ route('transactions.download-template') }}" class="inline-flex items-center text-xs font-bold text-tealBrand hover:text-tealBrand-hover transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Template Excel
                </a>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2.5">
                <button type="button" onclick="closeImportModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import Data
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal(trx) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        form.action = `/transactions/${trx.id}`;
        document.getElementById('editCodeDisplay').textContent = `(${trx.code})`;
        document.getElementById('editDate').value = trx.transaction_date ? trx.transaction_date.substring(0, 10) : '';
        document.getElementById('editType').value = trx.type;
        document.getElementById('editCustomer').value = trx.customer_name;
        document.getElementById('editQty').value = trx.qty;
        document.getElementById('editAmount').value = trx.amount;
        document.getElementById('editNotes').value = trx.notes || '';

        const branchSelect = document.getElementById('editBranchId');
        if (branchSelect) {
            branchSelect.value = trx.branch_id;
        }
        const branchNameInput = document.getElementById('editBranchName');
        if (branchNameInput && trx.branch) {
            branchNameInput.value = trx.branch.name;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            document.getElementById('fileNameText').textContent = fileName;
            document.getElementById('selectedFileName').classList.remove('hidden');
        }
    }

    // Bulk Action Checkboxes
    function toggleSelectAllTrx(master) {
        const checkboxes = document.querySelectorAll('.trx-item-checkbox:not(:disabled)');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateTrxSelection();
    }

    function updateTrxSelection() {
        const checkboxes = document.querySelectorAll('.trx-item-checkbox:checked');
        const count = checkboxes.length;
        const toolbar = document.getElementById('bulkTrxToolbar');
        const countText = document.getElementById('selectedTrxCount');
        const deleteContainer = document.getElementById('bulkDeleteInputs');

        if (count > 0) {
            countText.textContent = count;
            toolbar.classList.remove('hidden');

            deleteContainer.innerHTML = '';
            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                deleteContainer.appendChild(input);
            });
        } else {
            toolbar.classList.add('hidden');
            deleteContainer.innerHTML = '';
            const master = document.getElementById('selectAllTrx');
            if (master) master.checked = false;
        }
    }

    // Drag & Drop event listeners
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('dropzoneContainer');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-tealBrand', 'bg-teal-50/40');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-tealBrand', 'bg-teal-50/40');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    const input = document.getElementById('importFileInput');
                    input.files = files;
                    handleFileSelect(input);
                }
            });
        }
    });
</script>
@endpush
@endsection
