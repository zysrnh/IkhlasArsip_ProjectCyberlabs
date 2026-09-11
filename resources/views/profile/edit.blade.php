@extends('layouts.app')

@section('title', 'Kelola Profil Akun')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fadeIn">

    <!-- Page Header -->
    <div class="pb-2 border-b border-slate-200/80">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Profil Akun</h1>
        <p class="text-xs text-slate-500 mt-1 font-medium">Perbarui informasi identitas akun, foto profil pengguna, dan kata sandi keamanan.</p>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Foto Profil (Avatar / PP) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight mb-1">Foto Profil Pengguna</h3>
            <p class="text-xs text-slate-500 mb-5">Foto ini akan ditampilkan pada header, sidebar, dan aktivitas akun di seluruh sistem.</p>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <!-- Avatar Preview -->
                <div class="relative group shrink-0">
                    @if($user->avatar_url)
                        <img 
                            id="avatarPreviewImg"
                            src="{{ $user->avatar_url }}" 
                            alt="{{ $user->name }}" 
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 border-slate-200 shadow-md transition-transform"
                        >
                    @else
                        <div 
                            id="avatarFallbackDiv"
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl {{ $user->role === 'kepala_cabang' ? 'bg-tealBrand text-white' : ($user->role === 'superadmin' ? 'bg-purple-700 text-white' : 'bg-navy-900 text-white') }} font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-md"
                        >
                            {{ $user->initials }}
                        </div>
                        <img 
                            id="avatarPreviewImg"
                            src="" 
                            alt="{{ $user->name }}" 
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 border-slate-200 shadow-md hidden"
                        >
                    @endif
                </div>

                <!-- Action & Specs -->
                <div class="space-y-3 flex-1 text-center sm:text-left">
                    <div>
                        <input 
                            type="file" 
                            name="avatar" 
                            id="avatarInput" 
                            accept="image/png, image/jpeg, image/jpg, image/webp" 
                            class="hidden"
                            onchange="handleAvatarPreview(this)"
                        >
                        <button 
                            type="button" 
                            onclick="document.getElementById('avatarInput').click()" 
                            class="px-4 py-2 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-colors cursor-pointer inline-flex items-center space-x-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Pilih Foto Baru</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                        Format file yang didukung: <strong class="text-slate-600">JPG, PNG, WEBP</strong>. Ukuran file maksimal <strong class="text-slate-600">2 MB</strong>.
                    </p>

                    @if($user->avatar)
                        <div class="pt-1">
                            <button 
                                type="button" 
                                onclick="document.getElementById('deleteAvatarForm').submit()"
                                class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline inline-flex items-center space-x-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Hapus Foto Profil</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2: Informasi Dasar Akun -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight pb-2 border-b border-slate-100">Informasi Identitas</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">NAMA LENGKAP</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $user->name) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>

                <!-- Alamat Email -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">ALAMAT EMAIL</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>
            </div>

            <!-- Role & Cabang (Read-Only Info) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100/80">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">HAK AKSES SISTEM</label>
                    <div class="px-3.5 py-2.5 text-xs bg-slate-100/70 border border-slate-200 rounded-xl text-slate-700 font-bold flex items-center justify-between">
                        <span>
                            @if($user->role === 'superadmin')
                                Super Administrator
                            @elseif($user->role === 'kepala_cabang')
                                Kepala Cabang
                            @elseif($user->role === 'admin_cabang')
                                Admin Cabang
                            @else
                                Viewer (Read-Only)
                            @endif
                        </span>
                        <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-400">DITETAPKAN</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PENEMPATAN CABANG</label>
                    <div class="px-3.5 py-2.5 text-xs bg-slate-100/70 border border-slate-200 rounded-xl text-slate-700 font-bold flex items-center justify-between">
                        <span>{{ $user->branch->name ?? 'Semua Cabang (Global)' }}</span>
                        <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-400">DITETAPKAN</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Keamanan & Ganti Kata Sandi -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Ganti Password Akun</h3>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Kosongkan kolom di bawah jika Anda tidak ingin mengubah password akun Anda saat ini.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                <!-- Password Saat Ini -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PASSWORD SAAT INI</label>
                    <input 
                        type="password" 
                        name="current_password" 
                        placeholder="Ketik password saat ini..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>

                <!-- Password Baru -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">PASSWORD BARU</label>
                    <input 
                        type="password" 
                        name="new_password" 
                        placeholder="Minimal 6 karakter..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 tracking-wider mb-1">KONFIRMASI PASSWORD</label>
                    <input 
                        type="password" 
                        name="new_password_confirmation" 
                        placeholder="Ulangi password baru..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-tealBrand transition"
                    >
                </div>
            </div>
        </div>

        <!-- Bottom Action Button -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a 
                href="{{ route('dashboard') }}" 
                class="px-5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer"
            >
                Kembali
            </a>
            <button 
                type="submit" 
                class="px-6 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm transition-colors cursor-pointer"
            >
                Simpan Perubahan
            </button>
        </div>

    </form>

    <!-- Hidden Form for Deleting Avatar -->
    <form id="deleteAvatarForm" action="{{ route('profile.avatar.destroy') }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>

@push('scripts')
<script>
    function handleAvatarPreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreviewImg');
                const fallback = document.getElementById('avatarFallbackDiv');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                if (fallback) {
                    fallback.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
