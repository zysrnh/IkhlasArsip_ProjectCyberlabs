@extends('layouts.app')

@section('title', 'Sampah Transaksi')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                    Recycle Bin
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sampah Transaksi</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Daftar transaksi yang dihapus sementara (Soft Delete). Khusus Super Admin untuk memulihkan atau menghapus permanen.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a 
                href="{{ route('transactions.index') }}" 
                class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-2 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Transaksi</span>
            </a>

            @if($transactions->total() > 0)
                <form action="{{ route('trash.empty') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan tempat sampah? Semua data transaksi yang terhapus akan DIMUSNAHKAN PERMANEN dan tidak dapat dikembalikan lagi!');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors"
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
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('trash.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            
            <!-- Search -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari ID, customer, atau catatan di tempat sampah..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-tealBrand"
                >
            </div>

            <!-- Filter Cabang -->
            <div>
                <select 
                    name="branch_id" 
                    onchange="this.form.submit()" 
                    class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium focus:bg-white focus:outline-none focus:border-tealBrand"
                >
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </form>
    </div>

    <!-- Bulk Action Toolbar (Muncul saat dicentang) -->
    <div id="bulkTrashToolbar" class="hidden bg-navy-900 text-white p-3.5 rounded-xl shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fadeIn">
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
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center space-x-1.5"
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
                    class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center space-x-1.5"
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
    <div class="flex items-center justify-between text-xs px-1">
        <div class="text-slate-500 font-medium">
            Terdapat <strong class="text-slate-800 font-bold">{{ $transactions->total() }}</strong> transaksi di tempat sampah
            @if(request()->hasAny(['search', 'branch_id']))
                &bull; <a href="{{ route('trash.index') }}" class="text-tealBrand hover:underline font-semibold">Reset Filter</a>
            @endif
        </div>
        <div class="text-sm font-bold text-slate-700">
            Total Nominal Sampah : <span class="text-rose-600 font-extrabold font-sans">Rp {{ number_format($totalTrashedAmount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm overflow-hidden">
        
        <div class="overflow-x-auto">
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
                            <!-- Checkbox -->
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" name="trash_ids[]" value="{{ $trx->id }}" onchange="updateTrashSelection()" class="trash-item-checkbox rounded border-slate-300 text-tealBrand focus:ring-tealBrand cursor-pointer">
                            </td>

                            <!-- ID -->
                            <td class="py-3 px-3 font-mono text-slate-500 font-bold">{{ $trx->code }}</td>
                            
                            <!-- Tanggal Transaksi -->
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">
                                {{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}
                            </td>
                            
                            <!-- Cabang -->
                            <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">
                                {{ $trx->branch->name ?? '-' }}
                            </td>
                            
                            <!-- Jenis -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $trx->type }}
                                </span>
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

                            <!-- Waktu Dihapus -->
                            <td class="py-3 px-3 text-rose-700 whitespace-nowrap text-[11px] font-mono">
                                {{ $trx->deleted_at ? $trx->deleted_at->format('d M Y, H:i') : '-' }}
                            </td>

                            <!-- Aksi Restore & Force Delete -->
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Restore Button -->
                                    <form action="{{ route('trash.restore', $trx->id) }}" method="POST" onsubmit="return confirm('Pulihkan transaksi {{ $trx->code }} kembali ke daftar aktif?');" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
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
                                            class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
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
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Tempat sampah kosong. Tidak ada data transaksi yang dihapus.</span>
                                </div>
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
    function toggleSelectAllTrash(master) {
        const checkboxes = document.querySelectorAll('.trash-item-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
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
            countText.textContent = count;
            toolbar.classList.remove('hidden');

            // Inject hidden inputs for forms
            restoreContainer.innerHTML = '';
            forceContainer.innerHTML = '';
            checkboxes.forEach(cb => {
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
            });
        } else {
            toolbar.classList.add('hidden');
            restoreContainer.innerHTML = '';
            forceContainer.innerHTML = '';
            const master = document.getElementById('selectAllTrash');
            if (master) master.checked = false;
        }
    }
</script>
@endpush
@endsection
