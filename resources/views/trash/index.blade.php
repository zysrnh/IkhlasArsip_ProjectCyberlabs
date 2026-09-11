@extends('layouts.app')

@section('title', 'Sampah Transaksi')

@section('content')
<div class="space-y-5 animate-fadeIn">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 pb-2">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                    Recycle Bin
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Sampah Transaksi</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Daftar transaksi yang dihapus sementara (Soft Delete). Khusus Super Admin untuk memulihkan atau menghapus permanen.
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a 
                href="{{ route('transactions.index') }}" 
                class="px-3.5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Data Transaksi</span>
            </a>

            @if($transactions->total() > 0)
                <form action="{{ route('trash.empty') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan tempat sampah? Semua data transaksi yang terhapus akan DIMUSNAHKAN PERMANEN dan tidak dapat dikembalikan lagi!');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Kosongkan Sampah</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        <form id="trashFilterForm" method="GET" action="{{ route('trash.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="hidden" name="branch_id" id="trashBranchFilterInput" value="{{ $selectedBranchId }}">
            
            <!-- Live Search -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg id="trashSearchStaticIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg id="trashSearchLoadingIcon" class="w-4 h-4 text-tealBrand animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                <input 
                    type="text" 
                    id="trashSearchInput"
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari ID, customer, atau catatan di tempat sampah... (otomatis)" 
                    autocomplete="off"
                    oninput="handleTrashSearchDebounce(this)"
                    class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-tealBrand focus:ring-1 focus:ring-tealBrand shadow-sm transition"
                >

                <button 
                    type="button" 
                    id="trashSearchClearBtn"
                    onclick="clearTrashSearchInput()"
                    class="{{ request('search') ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"
                    title="Hapus pencarian"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Branch Custom Popover Dropdown -->
            <div class="relative" id="trashBranchDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleTrashPopover('trashBranchMenu', 'trashBranchChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors cursor-pointer"
                >
                    <span class="truncate">
                        @if($selectedBranchId)
                            {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                        @else
                            Semua Cabang
                        @endif
                    </span>
                    <svg id="trashBranchChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="trashBranchMenu" class="hidden absolute right-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectTrashBranch('')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty($selectedBranchId) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Cabang</span>
                        @if(empty($selectedBranchId))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    @foreach($branches as $branch)
                        <button 
                            type="button" 
                            onclick="selectTrashBranch('{{ $branch->id }}')"
                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ $selectedBranchId == $branch->id ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                        >
                            <span>{{ $branch->name }}</span>
                            @if($selectedBranchId == $branch->id)
                                <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

        </form>
    </div>

    <!-- Bulk Action Toolbar (Muncul saat dicentang) -->
    <div id="bulkTrashToolbar" class="hidden bg-slate-900 text-white p-3.5 rounded-xl shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fadeIn">
        <div class="flex items-center space-x-2 text-xs font-bold px-2">
            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
            <span><span id="selectedCountText">0</span> transaksi dipilih</span>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Form Bulk Restore -->
            <form id="bulkRestoreForm" action="{{ route('trash.bulk-restore') }}" method="POST" class="inline">
                @csrf
                <div id="bulkRestoreInputs"></div>
                <button 
                    type="submit" 
                    onclick="return confirm('Apakah Anda yakin ingin memulihkan transaksi yang dipilih?');" 
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center space-x-1.5 shadow-sm cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Pulihkan Terpilih</span>
                </button>
            </form>

            <!-- Form Bulk Force Delete -->
            <form id="bulkForceForm" action="{{ route('trash.bulk-force-delete') }}" method="POST" class="inline">
                @csrf
                <div id="bulkForceInputs"></div>
                <button 
                    type="submit" 
                    onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN transaksi yang dipilih? Data ini TIDAK BISA dikembalikan lagi.');" 
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center space-x-1.5 shadow-sm cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Permanen Terpilih</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Info Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs px-1">
        <div class="text-slate-500 font-medium">
            Terdapat <strong class="text-slate-800 font-bold">{{ $transactions->total() }}</strong> transaksi di tempat sampah
            @if(request()->hasAny(['search', 'branch_id']))
                &bull; <a href="{{ route('trash.index') }}" class="text-tealBrand hover:underline font-bold">Reset Filter</a>
            @endif
        </div>
        <div class="text-xs sm:text-sm font-bold text-slate-700">
            Total Nominal Sampah : <span class="text-rose-600 font-extrabold font-sans">Rp {{ number_format($totalTrashedAmount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Data Container: Mobile Card List + Desktop Table -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm overflow-hidden">
        
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Sampah</h3>
                <label class="md:hidden flex items-center space-x-1.5 text-[11px] font-bold text-slate-500 cursor-pointer">
                    <input type="checkbox" id="selectAllTrashMobile" onchange="toggleSelectAllTrash(this)" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                    <span>Pilih Semua</span>
                </label>
            </div>
            <span class="text-xs text-slate-400 font-medium font-mono">Page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</span>
        </div>

        <!-- 1. Mobile Cards View (< md) -->
        <div class="block md:hidden space-y-3">
            @forelse($transactions as $trx)
                <div class="bg-rose-50/20 hover:bg-rose-50/40 border border-rose-200/80 rounded-xl p-3.5 space-y-2.5 transition-all">
                    
                    <!-- Top Row: Checkbox, Code, Type & Deleted Date -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="trash_ids[]" value="{{ $trx->id }}" onchange="updateTrashSelection()" class="trash-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                            <span class="font-mono text-[11px] font-bold text-slate-700 bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                                {{ $trx->code }}
                            </span>
                        </div>
                        
                        <span class="text-[10px] text-rose-600 font-bold font-mono">
                            Dihapus: {{ $trx->deleted_at ? $trx->deleted_at->format('d M, H:i') : '-' }}
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-bold text-xs text-slate-900">
                                {{ $trx->customer_name }}
                            </div>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $trx->type }}
                            </span>
                        </div>

                        <div class="text-[10px] text-slate-500 flex items-center space-x-1">
                            <span>Cabang:</span>
                            <span class="text-slate-700 font-bold">{{ $trx->branch->name ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Bottom: Nominal & Action Buttons -->
                    <div class="pt-2 border-t border-rose-200/60 flex items-center justify-between">
                        <div class="text-xs">
                            <span class="text-[10px] text-slate-400 font-bold mr-1">QTY: {{ $trx->qty }}</span>
                            <span class="font-extrabold {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <!-- Restore Button -->
                            <form action="{{ route('trash.restore', $trx->id) }}" method="POST" onsubmit="return confirm('Pulihkan transaksi {{ $trx->code }} kembali ke daftar aktif?');" class="inline">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="px-2.5 py-1 bg-white hover:bg-emerald-50 border border-emerald-300 text-emerald-700 text-[11px] font-bold rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span>Pulihkan</span>
                                </button>
                            </form>

                            <!-- Force Delete Button -->
                            <form action="{{ route('trash.force-delete', $trx->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Hapus permanen transaksi {{ $trx->code }}? Data tidak dapat dikembalikan lagi!');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="p-1 text-rose-500 hover:text-rose-700 transition-colors cursor-pointer"
                                    title="Hapus Permanen"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Tempat sampah kosong. Tidak ada transaksi yang dihapus.
                </div>
            @endforelse
        </div>

        <!-- 2. Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3 w-8 text-center">
                            <input type="checkbox" id="selectAllTrash" onchange="toggleSelectAllTrash(this)" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                        </th>
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Tanggal Transaksi</th>
                        <th class="py-3 px-3">Cabang</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3 text-center">QTY</th>
                        <th class="py-3 px-3 text-right">Jumlah</th>
                        <th class="py-3 px-3">Waktu Dihapus</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-rose-50/30 transition-colors">
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" name="trash_ids[]" value="{{ $trx->id }}" onchange="updateTrashSelection()" class="trash-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-500 font-bold">{{ $trx->code }}</td>
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">
                                {{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">
                                {{ $trx->branch->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $trx->type }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap font-medium">
                                {{ $trx->customer_name }}
                            </td>
                            <td class="py-3 px-3 text-center text-slate-700 font-bold font-mono">
                                {{ $trx->qty }}
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold whitespace-nowrap {{ $trx->amount < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-rose-700 whitespace-nowrap text-[11px] font-mono">
                                {{ $trx->deleted_at ? $trx->deleted_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Restore Button -->
                                    <form action="{{ route('trash.restore', $trx->id) }}" method="POST" onsubmit="return confirm('Pulihkan transaksi {{ $trx->code }} kembali ke daftar aktif?');" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="p-1 text-emerald-600 hover:text-emerald-800 transition-colors cursor-pointer"
                                            title="Pulihkan Transaksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Force Delete Button -->
                                    <form action="{{ route('trash.force-delete', $trx->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Hapus permanen transaksi {{ $trx->code }}? Data tidak dapat dikembalikan lagi!');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus Permanen"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 font-medium">
                                Tempat sampah kosong. Tidak ada data transaksi yang dihapus.
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

@push('scripts')
<script>
    // Live Search Debounce
    let trashSearchDebounceTimer = null;
    function handleTrashSearchDebounce(input) {
        const clearBtn = document.getElementById('trashSearchClearBtn');
        const staticIcon = document.getElementById('trashSearchStaticIcon');
        const loadingIcon = document.getElementById('trashSearchLoadingIcon');

        if (input.value.trim().length > 0) {
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else {
            if (clearBtn) clearBtn.classList.add('hidden');
        }

        clearTimeout(trashSearchDebounceTimer);
        
        if (staticIcon && loadingIcon) {
            staticIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
        }

        trashSearchDebounceTimer = setTimeout(() => {
            document.getElementById('trashFilterForm').submit();
        }, 450);
    }

    function clearTrashSearchInput() {
        const input = document.getElementById('trashSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('trashSearchClearBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        document.getElementById('trashFilterForm').submit();
    }

    // Branch Popover
    function toggleTrashPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
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

    function selectTrashBranch(branchId) {
        document.getElementById('trashBranchFilterInput').value = branchId;
        document.getElementById('trashFilterForm').submit();
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('trashBranchDropdownContainer');
        const menu = document.getElementById('trashBranchMenu');
        const chevron = document.getElementById('trashBranchChevron');
        if (container && menu && !container.contains(e.target)) {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });

    // Checkboxes
    function toggleSelectAllTrash(master) {
        const checkboxes = document.querySelectorAll('.trash-item-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        
        const masterDesktop = document.getElementById('selectAllTrash');
        const masterMobile = document.getElementById('selectAllTrashMobile');
        if (masterDesktop) masterDesktop.checked = master.checked;
        if (masterMobile) masterMobile.checked = master.checked;

        updateTrashSelection();
    }

    function updateTrashSelection() {
        const checkboxes = document.querySelectorAll('.trash-item-checkbox:checked');
        const count = checkboxes.length;
        const toolbar = document.getElementById('bulkTrashToolbar');
        const countText = document.getElementById('selectedCountText');
        const restoreContainer = document.getElementById('bulkRestoreInputs');
        const forceContainer = document.getElementById('bulkForceInputs');

        if (count > 0) {
            toolbar.classList.remove('hidden');

            restoreContainer.innerHTML = '';
            forceContainer.innerHTML = '';
            const selectedIds = new Set();

            checkboxes.forEach(cb => {
                if (!selectedIds.has(cb.value)) {
                    selectedIds.add(cb.value);

                    const inputR = document.createElement('input');
                    inputR.type = 'hidden';
                    inputR.name = 'ids[]';
                    inputR.value = cb.value;
                    restoreContainer.appendChild(inputR);

                    const inputF = document.createElement('input');
                    inputF.type = 'hidden';
                    inputF.name = 'ids[]';
                    inputF.value = cb.value;
                    forceContainer.appendChild(inputF);
                }
            });
            countText.textContent = selectedIds.size;
        } else {
            toolbar.classList.add('hidden');
            restoreContainer.innerHTML = '';
            forceContainer.innerHTML = '';
            const masterDesktop = document.getElementById('selectAllTrash');
            const masterMobile = document.getElementById('selectAllTrashMobile');
            if (masterDesktop) masterDesktop.checked = false;
            if (masterMobile) masterMobile.checked = false;
        }
    }

    // Keyboard Shortcut '/'
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const searchInput = document.getElementById('trashSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });
</script>
@endpush
@endsection
