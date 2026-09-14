@extends('layouts.app')

@section('title', 'Backup Database — Ikhlas Solusi')

@section('content')
<div class="space-y-6">

    <!-- Top Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Backup Database</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola pencadangan data, riwayat berkas SQL, dan pengiriman cadangan ke email resmi.
            </p>
        </div>

        <!-- Primary Action Buttons -->
        <div class="flex items-center gap-2">
            <!-- Modal Trigger: Buat & Kirim Email -->
            <button 
                type="button" 
                onclick="openCreateAndSendModal()"
                class="px-3.5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors cursor-pointer"
                title="Buat backup database baru dan langsung kirim ke email"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Backup & Kirim Email</span>
            </button>

            <!-- Direct Button: Buat Backup Sekarang -->
            <form action="{{ route('backups.create') }}" method="POST" id="directBackupForm" class="inline">
                @csrf
                <button 
                    type="submit" 
                    onclick="handleDirectBackupSubmit(event, this.form)"
                    class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>+ Buat Backup Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- 1. Total Berkas Backup -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-tealBrand/10 text-tealBrand flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Total Berkas</div>
                <div class="text-base font-extrabold text-slate-900 truncate">{{ $totalCount }} Berkas SQL</div>
            </div>
        </div>

        <!-- 2. Ruang Terpakai -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Penyimpanan Terpakai</div>
                <div class="text-base font-extrabold text-slate-900 truncate">{{ $totalStorageFormatted }}</div>
            </div>
        </div>

        <!-- 3. Basis Data Aktif -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Database Aktif</div>
                <div class="text-sm font-extrabold text-slate-900 truncate font-mono">{{ $databaseName }} ({{ strtoupper($driver) }})</div>
            </div>
        </div>

        <!-- 4. Jadwal Otomatis -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Jadwal Otomatis</div>
                <div class="text-sm font-extrabold text-slate-900 truncate">Harian (00:00 WIB)</div>
            </div>
        </div>
    </div>

    <!-- Info Banner Panduan Cron Server -->
    <div class="bg-slate-900 text-white rounded-xl p-4 sm:p-5 shadow-sm space-y-2 border border-slate-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs font-bold text-teal-400 uppercase tracking-wider">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Otomatisasi Jadwal Backup 24 Jam (Cron Job)</span>
            </div>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed">
            Sistem telah dilengkapi task scheduler bawaan yang otomatis mem-backup seluruh database setiap hari pukul <strong>00:00 WIB</strong>. Pastikan cron job server (DirectAdmin / cPanel) telah mengarah ke perintah scheduler Laravel:
        </p>
        <div class="bg-slate-950/80 rounded-lg p-2.5 font-mono text-[11px] text-teal-300 border border-slate-800 overflow-x-auto select-all">
            * * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1
        </div>
    </div>

    <!-- Data List: Riwayat Berkas Backup -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm space-y-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Daftar Berkas Cadangan Database</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                    {{ count($backups) }} Berkas
                </span>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3 w-10 text-center">NO</th>
                        <th class="py-3 px-3">NAMA BERKAS (.SQL)</th>
                        <th class="py-3 px-3">WAKTU PEMBUATAN</th>
                        <th class="py-3 px-3">UKURAN BERKAS</th>
                        <th class="py-3 px-3">STATUS</th>
                        <th class="py-3 px-3 text-center w-36">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($backups as $idx => $b)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            
                            <!-- Nama Berkas -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center space-x-2 font-mono font-bold text-slate-900">
                                    <svg class="w-4 h-4 text-tealBrand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="hover:text-tealBrand transition-colors select-all">{{ $b['file_name'] }}</span>
                                </div>
                            </td>

                            <!-- Waktu Pembuatan -->
                            <td class="py-3.5 px-3 text-slate-600 whitespace-nowrap">
                                {{ $b['created_at_formatted'] }}
                            </td>

                            <!-- Ukuran Berkas -->
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $b['size_formatted'] }}
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if($b['is_recent'])
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tersimpan (Baru)
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Arsip Tersimpan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <!-- Unduh File SQL -->
                                    <a 
                                        href="{{ route('backups.download', $b['file_name']) }}" 
                                        class="p-1.5 text-slate-500 hover:text-tealBrand hover:bg-teal-50 rounded-lg transition-colors cursor-pointer"
                                        title="Unduh Berkas SQL"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>

                                    <!-- Kirim ke Email -->
                                    <button 
                                        type="button" 
                                        onclick="openSendEmailModal('{{ $b['file_name'] }}', '{{ $b['size_formatted'] }}')"
                                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Kirim Berkas ini ke Email"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>

                                    <!-- Hapus Berkas Backup -->
                                    <form action="{{ route('backups.destroy', $b['file_name']) }}" method="POST" onsubmit="event.preventDefault(); confirmCustomAction({ title: 'Hapus Berkas Backup?', text: 'Berkas {{ $b['file_name'] }} akan dihapus permanen dari server penyimpanan.', icon: 'warning', danger: true, confirmButtonText: 'Ya, Hapus Permanen', form: this });" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Berkas Backup"
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
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                                Belum ada berkas backup database yang dibuat. Silakan klik tombol <strong>+ Buat Backup Sekarang</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Modal 1: Kirim Berkas Tertentu ke Email -->
<div id="sendEmailModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-[460px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
        
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Kirim Berkas Backup ke Email</h3>
            <button type="button" onclick="closeSendEmailModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="sendEmailForm" method="POST" action="" class="p-5 space-y-4 overflow-y-auto">
            @csrf
            
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs space-y-1">
                <div class="text-[10px] font-bold uppercase text-slate-400">Berkas yang Akan Dikirim:</div>
                <div id="sendModalFileName" class="font-mono font-bold text-slate-900 break-all"></div>
                <div id="sendModalFileSize" class="text-slate-500 font-medium text-[11px]"></div>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">
                    ALAMAT EMAIL TUJUAN <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                        </svg>
                    </div>
                    <input 
                        type="email" 
                        name="email" 
                        id="sendModalEmailInput" 
                        value="{{ auth()->user()->email }}" 
                        required 
                        placeholder="contoh: admin@perusahaan.com"
                        class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Anda dapat mengetik alamat email lain sesuai kebutuhan.</p>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">CATATAN TAMBAHAN (OPSIONAL)</label>
                <textarea 
                    name="notes" 
                    rows="2" 
                    placeholder="Tulis pesan atau catatan untuk penerima..."
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition resize-none"
                ></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end space-x-2">
                <button 
                    type="button" 
                    onclick="closeSendEmailModal()" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Kirim Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Buat Backup Baru Sekaligus Kirim ke Email -->
<div id="createAndSendModal" class="fixed inset-0 z-50 bg-slate-950/70 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-[460px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fadeIn">
        
        <div class="px-5 py-4 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="font-extrabold text-sm text-slate-900 tracking-tight">Buat Backup & Kirim Email</h3>
            <button type="button" onclick="closeCreateAndSendModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('backups.create') }}" method="POST" class="p-5 space-y-4 overflow-y-auto">
            @csrf
            
            <p class="text-xs text-slate-600 leading-relaxed">
                Sistem akan membuat berkas backup database terbaru saat ini dan otomatis melampirkannya ke alamat email tujuan.
            </p>

            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">
                    ALAMAT EMAIL PENERIMA <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                        </svg>
                    </div>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ auth()->user()->email }}" 
                        required 
                        placeholder="contoh: admin@perusahaan.com"
                        class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">CATATAN (OPSIONAL)</label>
                <textarea 
                    name="notes" 
                    rows="2" 
                    placeholder="Catatan backup..."
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition resize-none"
                ></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end space-x-2">
                <button 
                    type="button" 
                    onclick="closeCreateAndSendModal()" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center space-x-1.5 transition-colors cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Proses & Kirim</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // 1. Direct Backup Submit Handling
    function handleDirectBackupSubmit(e, form) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Memproses Backup Database...',
                text: 'Mohon tunggu beberapa detik saat sistem membuat cadangan SQL.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }

    // 2. Modal Kirim Email Terpilih
    function openSendEmailModal(fileName, fileSizeFormatted) {
        document.getElementById('sendModalFileName').textContent = fileName;
        document.getElementById('sendModalFileSize').textContent = 'Ukuran: ' + fileSizeFormatted;
        
        const form = document.getElementById('sendEmailForm');
        form.action = "{{ url('/backups') }}/" + encodeURIComponent(fileName) + "/send-email";

        const modal = document.getElementById('sendEmailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeSendEmailModal() {
        const modal = document.getElementById('sendEmailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 3. Modal Buat & Kirim Email
    function openCreateAndSendModal() {
        const modal = document.getElementById('createAndSendModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateAndSendModal() {
        const modal = document.getElementById('createAndSendModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSendEmailModal();
            closeCreateAndSendModal();
        }
    });
</script>
@endpush
@endsection
