@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner (Flat Solid) -->
    <div class="bg-white border border-slate-300 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Anda masuk sebagai <strong class="text-slate-800 uppercase">{{ auth()->user()->role }}</strong>
                    @if(auth()->user()->branch)
                        di outlet <strong class="text-slate-800">{{ auth()->user()->branch->name }}</strong>
                    @else
                        (Akses Global Seluruh Outlet)
                    @endif
                </p>
            </div>
            
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold tracking-wide transition-colors">
                    Kelola Pengguna Sistem &rarr;
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards (Placeholder) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-300 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Cabang / Outlet</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\Branch::count() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Outlet terdaftar aktif</div>
        </div>

        <div class="bg-white border border-slate-300 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Pengguna</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\User::count() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Akun Superadmin & Cabang</div>
        </div>

        <div class="bg-white border border-slate-300 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Arsip Berkas</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">0</div>
            <div class="text-[11px] text-slate-500 mt-1">Dokumen tersimpan</div>
        </div>

        <div class="bg-white border border-slate-300 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Transaksi Masuk</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">0</div>
            <div class="text-[11px] text-slate-500 mt-1">Siap rekap & filter</div>
        </div>
    </div>

</div>
@endsection
