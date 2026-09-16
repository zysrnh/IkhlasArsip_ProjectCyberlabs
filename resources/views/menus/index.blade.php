@extends('layouts.app')

@section('title', 'Master Menu Masakan & Harga Cabang')

@section('content')
<div class="space-y-6 animate-fadeIn pb-6">
    <!-- Top Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Master Menu Masakan</h1>
                @if(auth()->user()->isSuperAdmin())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200 uppercase tracking-wider">
                        Master Data
                    </span>
                @elseif(auth()->user()->isAdminDapur())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">
                        Admin Dapur
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Daftar menu masakan dapur, pengaturan sifat lauk (cepat basi / tahan lama), dan penetapan harga per cabang.
            </p>
        </div>

        @if(!auth()->user()->isViewer())
        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="openCreateModal()" 
                class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm inline-flex items-center justify-center space-x-1.5 transition-all duration-150 cursor-pointer"
            >
                <span class="text-sm leading-none">+</span>
                <span>Tambah Menu Baru</span>
            </button>
        </div>
        @endif
    </div>

    <!-- 4 Primary Stat Cards (Matching Dashboard & Transactions Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Menu -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider group-hover:text-slate-700 transition-colors">
                    Total Menu
                </span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-sans">
                    {{ number_format($stats['total_menus']) }} <span class="text-xs font-normal text-slate-400">item</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                <span>Total Item Masakan</span>
                <span class="font-bold text-slate-700">{{ $stats['total_menus'] }} menu</span>
            </div>
        </div>

        <!-- Cepat Basi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-rose-500 uppercase tracking-wider group-hover:text-rose-600 transition-colors">
                    Cepat Basi
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-rose-600 tracking-tight font-sans">
                    {{ number_format($stats['total_sayur']) }} <span class="text-xs font-normal text-slate-400">item</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                <span>Sayur & Makanan Basah</span>
                <span class="font-bold text-rose-600">{{ $stats['total_sayur'] }} menu</span>
            </div>
        </div>

        <!-- Lauk Biasa -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-tealBrand uppercase tracking-wider group-hover:text-tealBrand-hover transition-colors">
                    Lauk Biasa
                </span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-tealBrand flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-tealBrand tracking-tight font-sans">
                    {{ number_format($stats['total_lauk']) }} <span class="text-xs font-normal text-slate-400">item</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                <span>Lauk Olahan / Tahan Lama</span>
                <span class="font-bold text-tealBrand">{{ $stats['total_lauk'] }} menu</span>
            </div>
        </div>

        <!-- Menu Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider group-hover:text-emerald-700 transition-colors">
                    Menu Aktif
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
            <div class="my-3">
                <div class="text-2xl font-black text-emerald-600 tracking-tight font-sans">
                    {{ number_format($stats['total_active']) }} <span class="text-xs font-normal text-slate-400">item</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-2.5 border-t border-slate-100">
                <span>Tersedia di Form Dapur</span>
                <span class="font-bold text-emerald-600">{{ $stats['total_active'] }} aktif</span>
            </div>
        </div>
    </div>

    <!-- Filter & Sorting Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>FILTER & PENCARIAN MENU</span>
                @if(request()->hasAny(['search', 'category', 'status']))
                    <span class="w-2 h-2 rounded-full bg-tealBrand inline-block"></span>
                @endif
            </div>
        </div>

        <form id="menuFilterForm" method="GET" action="{{ route('menus.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="category" id="filterCategoryInput" value="{{ request('category') }}">
            <input type="hidden" name="status" id="filterStatusInput" value="{{ request('status') }}">

            <!-- 1. Search Input -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama menu..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 hover:border-tealBrand focus:border-tealBrand rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 shadow-sm outline-none transition-all duration-150"
                >
            </div>

            <!-- 2. Custom Popover: Sifat Lauk -->
            <div class="relative" id="filterCategoryDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleCustomPopover('filterCategoryMenu', 'filterCategoryChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors shadow-sm cursor-pointer"
                >
                    <span class="truncate" id="filterCategoryLabel">
                        @if(request('category') === 'perishable')
                            Cepat Basi
                        @elseif(request('category') === 'non_perishable')
                            Lauk Biasa (Tahan Lama)
                        @else
                            Semua Sifat Lauk
                        @endif
                    </span>
                    <svg id="filterCategoryChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="filterCategoryMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectFilterCategory('', 'Semua Sifat Lauk')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty(request('category')) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Sifat Lauk</span>
                        @if(empty(request('category')))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    <button 
                        type="button" 
                        onclick="selectFilterCategory('perishable', 'Cepat Basi')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('category') === 'perishable' ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Cepat Basi</span>
                        @if(request('category') === 'perishable')
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    <button 
                        type="button" 
                        onclick="selectFilterCategory('non_perishable', 'Lauk Biasa (Tahan Lama)')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('category') === 'non_perishable' ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Lauk Biasa (Tahan Lama)</span>
                        @if(request('category') === 'non_perishable')
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                </div>
            </div>

            <!-- 3. Custom Popover: Status Menu -->
            <div class="relative" id="filterStatusDropdownContainer">
                <button 
                    type="button"
                    onclick="toggleCustomPopover('filterStatusMenu', 'filterStatusChevron')"
                    class="w-full px-3.5 py-2.5 text-xs bg-white hover:bg-slate-50 border border-slate-200 hover:border-tealBrand rounded-xl text-slate-700 font-medium flex items-center justify-between transition-colors shadow-sm cursor-pointer"
                >
                    <span class="truncate" id="filterStatusLabel">
                        @if(request('status') === 'active')
                            Aktif Saja
                        @elseif(request('status') === 'inactive')
                            Nonaktif Saja
                        @else
                            Semua Status
                        @endif
                    </span>
                    <svg id="filterStatusChevron" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="filterStatusMenu" class="hidden absolute left-0 top-full mt-1.5 w-full min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-40 animate-fadeIn">
                    <button 
                        type="button" 
                        onclick="selectFilterStatus('', 'Semua Status')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ empty(request('status')) ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Semua Status</span>
                        @if(empty(request('status')))
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    <button 
                        type="button" 
                        onclick="selectFilterStatus('active', 'Aktif Saja')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('status') === 'active' ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Aktif Saja</span>
                        @if(request('status') === 'active')
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                    <button 
                        type="button" 
                        onclick="selectFilterStatus('inactive', 'Nonaktif Saja')"
                        class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors {{ request('status') === 'inactive' ? 'text-tealBrand font-bold bg-teal-50/60' : 'text-slate-700 hover:bg-slate-50 font-medium' }}"
                    >
                        <span>Nonaktif Saja</span>
                        @if(request('status') === 'inactive')
                            <svg class="w-3.5 h-3.5 text-tealBrand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @endif
                    </button>
                </div>
            </div>

            <!-- 4. Submit & Reset Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-navy-800 hover:bg-navy-900 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('menus.index') }}" class="py-2.5 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl text-center transition-all duration-150">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Menu Masakan</th>
                        <th class="py-3.5 px-4">Sifat Lauk</th>
                        <th class="py-3.5 px-4 text-right">Harga Default</th>
                        @foreach($branches as $b)
                        <th class="py-3.5 px-4 text-right text-slate-700">{{ $b->name }}</th>
                        @endforeach
                        <th class="py-3.5 px-4 text-center">Status</th>
                        @if(!auth()->user()->isViewer())
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($menus as $menu)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $menu->order_number ?: '-' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $menu->name }}
                        </td>
                        <td class="py-3 px-4">
                            @if($menu->is_perishable)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200 uppercase tracking-wider">
                                Cepat Basi
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-tealBrand border border-teal-200 uppercase tracking-wider">
                                Lauk Biasa
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-800">
                            Rp {{ number_format($menu->default_price, 0, ',', '.') }}
                        </td>
                        @foreach($branches as $b)
                        @php
                            $branchPrice = $menu->branchPrices->firstWhere('branch_id', $b->id);
                            $priceVal = $branchPrice && $branchPrice->price > 0 ? $branchPrice->price : $menu->default_price;
                            $isCustom = $branchPrice && $branchPrice->price != $menu->default_price;
                        @endphp
                        <td class="py-3 px-4 text-right">
                            <span class="font-medium {{ $isCustom ? 'text-tealBrand font-bold' : 'text-slate-600' }}">
                                Rp {{ number_format($priceVal, 0, ',', '.') }}
                            </span>
                        </td>
                        @endforeach
                        <td class="py-3 px-4 text-center">
                            @if($menu->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase tracking-wider">
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wider">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        @if(!auth()->user()->isViewer())
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <!-- Tombol Setting Harga Cabang -->
                                <button 
                                    type="button" 
                                    onclick="openPriceModal({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ json_encode($menu->branchPrices->pluck('price', 'branch_id')) }})" 
                                    title="Atur Harga Tiap Cabang"
                                    class="p-1.5 text-slate-400 hover:text-tealBrand hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <!-- Tombol Edit Menu -->
                                <button 
                                    type="button" 
                                    onclick="openEditModal({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ $menu->order_number ?: 0 }}, {{ $menu->is_perishable ? 1 : 0 }}, {{ $menu->default_price }}, {{ $menu->is_active ? 1 : 0 }})" 
                                    title="Edit Menu"
                                    class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <!-- Tombol Hapus -->
                                <button 
                                    type="button" 
                                    onclick="confirmDelete({{ $menu->id }}, '{{ addslashes($menu->name) }}')" 
                                    title="Hapus Menu"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + count($branches) }}" class="py-12 text-center text-slate-400 font-medium">
                            Tidak ada data menu masakan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($menus->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $menus->links() }}
        </div>
        @endif
    </div>
</div>

<!-- MODAL TAMBAH MENU -->
<div id="createModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <h3 class="text-sm font-extrabold text-slate-900">Tambah Menu Masakan Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('menus.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Menu</label>
                <input type="number" name="order_number" min="1" placeholder="Auto / Contoh: 1" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Masakan <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Rendang Daging Sapi" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sifat Lauk <span class="text-rose-500">*</span></label>
                <select name="is_perishable" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none cursor-pointer">
                    <option value="0">Lauk Biasa (Tahan Lama / Bisa Diolah Kembali)</option>
                    <option value="1">Cepat Basi (Sayuran / Makanan Basah)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="default_price" required min="0" step="500" placeholder="Contoh: 9000" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                <label for="create_is_active" class="text-xs font-medium text-slate-700">Menu Aktif (Tersedia di form dapur)</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MENU -->
<div id="editModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <h3 class="text-sm font-extrabold text-slate-900">Edit Data Menu Masakan</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Menu</label>
                <input type="number" id="edit_order_number" name="order_number" min="1" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Masakan <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sifat Lauk <span class="text-rose-500">*</span></label>
                <select id="edit_is_perishable" name="is_perishable" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none cursor-pointer">
                    <option value="0">Lauk Biasa (Tahan Lama / Bisa Diolah Kembali)</option>
                    <option value="1">Cepat Basi (Sayuran / Makanan Basah)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="edit_default_price" name="default_price" required min="0" step="500" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:border-tealBrand outline-none">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-slate-300 text-tealBrand focus:ring-tealBrand">
                <label for="edit_is_active" class="text-xs font-medium text-slate-700">Menu Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-navy-800 hover:bg-navy-900 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Perbarui Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SETTING HARGA CABANG -->
<div id="priceModal" class="fixed inset-0 z-50 hidden bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Setting Harga Cabang</h3>
                <p id="priceModalSubtitle" class="text-[11px] text-slate-500 mt-0.5 font-medium">Atur harga khusus untuk masing-masing cabang</p>
            </div>
            <button type="button" onclick="closePriceModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="priceForm" method="POST" class="p-5 space-y-4">
            @csrf
            <div class="space-y-3">
                @foreach($branches as $b)
                <div class="flex items-center justify-between gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <div class="text-xs font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-[10px] text-slate-400 font-medium">{{ $b->code }} - {{ $b->address }}</div>
                    </div>
                    <div class="w-36">
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-[11px] text-slate-400 font-bold">Rp</span>
                            <input 
                                type="number" 
                                name="prices[{{ $b->id }}]" 
                                id="price_branch_{{ $b->id }}" 
                                min="0" 
                                step="500" 
                                placeholder="0" 
                                class="w-full pl-9 pr-3 py-1.5 text-xs text-right font-bold bg-white border border-slate-200 rounded-lg focus:border-tealBrand outline-none"
                            >
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePriceModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-tealBrand hover:bg-tealBrand-hover text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Simpan Harga Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete Hidden -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Custom Popover Dropdown Handlers (Matching transactions/index.blade.php)
    function toggleCustomPopover(menuId, chevronId) {
        const menu = document.getElementById(menuId);
        const chevron = document.getElementById(chevronId);
        if (!menu) return;

        const allPopovers = ['filterCategoryMenu', 'filterStatusMenu'];
        const allChevrons = ['filterCategoryChevron', 'filterStatusChevron'];

        allPopovers.forEach((id, idx) => {
            if (id !== menuId) {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
                const ch = document.getElementById(allChevrons[idx]);
                if (ch) ch.classList.remove('rotate-180');
            }
        });

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    // Close popovers on click outside
    document.addEventListener('click', function(e) {
        const categoryDropdown = document.getElementById('filterCategoryDropdownContainer');
        const statusDropdown = document.getElementById('filterStatusDropdownContainer');

        if (categoryDropdown && !categoryDropdown.contains(e.target)) {
            const menu = document.getElementById('filterCategoryMenu');
            const ch = document.getElementById('filterCategoryChevron');
            if (menu) menu.classList.add('hidden');
            if (ch) ch.classList.remove('rotate-180');
        }

        if (statusDropdown && !statusDropdown.contains(e.target)) {
            const menu = document.getElementById('filterStatusMenu');
            const ch = document.getElementById('filterStatusChevron');
            if (menu) menu.classList.add('hidden');
            if (ch) ch.classList.remove('rotate-180');
        }
    });

    function selectFilterCategory(val, label) {
        document.getElementById('filterCategoryInput').value = val;
        document.getElementById('filterCategoryLabel').innerText = label;
        const menu = document.getElementById('filterCategoryMenu');
        const ch = document.getElementById('filterCategoryChevron');
        if (menu) menu.classList.add('hidden');
        if (ch) ch.classList.remove('rotate-180');
        document.getElementById('menuFilterForm').submit();
    }

    function selectFilterStatus(val, label) {
        document.getElementById('filterStatusInput').value = val;
        document.getElementById('filterStatusLabel').innerText = label;
        const menu = document.getElementById('filterStatusMenu');
        const ch = document.getElementById('filterStatusChevron');
        if (menu) menu.classList.add('hidden');
        if (ch) ch.classList.remove('rotate-180');
        document.getElementById('menuFilterForm').submit();
    }

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(id, name, order, isPerishable, defaultPrice, isActive) {
        const form = document.getElementById('editForm');
        form.action = `/menus/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_order_number').value = order;
        document.getElementById('edit_is_perishable').value = isPerishable ? "1" : "0";
        document.getElementById('edit_default_price').value = defaultPrice;
        document.getElementById('edit_is_active').checked = isActive === 1;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openPriceModal(id, name, prices) {
        const form = document.getElementById('priceForm');
        form.action = `/menus/${id}/prices`;
        document.getElementById('priceModalSubtitle').innerText = `Menu: ${name}`;
        
        @foreach($branches as $b)
        document.getElementById('price_branch_{{ $b->id }}').value = '';
        @endforeach

        if (prices) {
            for (const [branchId, price] of Object.entries(prices)) {
                const input = document.getElementById(`price_branch_${branchId}`);
                if (input) input.value = price;
            }
        }

        document.getElementById('priceModal').classList.remove('hidden');
    }
    function closePriceModal() {
        document.getElementById('priceModal').classList.add('hidden');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Menu Masakan?',
            text: `Apakah Anda yakin ingin menghapus menu "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Menu',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = `/menus/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection
