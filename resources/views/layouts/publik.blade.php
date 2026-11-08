@extends('layouts.auth')

@push('prepend-style')
    @include('includes.publik.gaya')
@endpush

@section('content')
    @php
        $menu = [
            url('/#tentang') => 'Tentang',
            url('/#logo') => 'Makna Logo',
            url('/#bidang') => 'Program Kerja',
            url('/#kegiatan') => 'Kegiatan '.($tahun ?? now()->year),
            url('/#keanggotaan') => 'Keanggotaan',
            url('/#jemaat') => 'Jemaat',
            route('artikel.daftar') => 'Artikel',
            url('/#layanan') => 'Layanan',
        ];
    @endphp

    <div class="beranda bg-white text-slate-800">
        <header class="z-30 border-b border-slate-200 bg-white/95 backdrop-blur lg:sticky lg:top-0">
            <div class="wadah-publik flex flex-col gap-2 py-3 xl:flex-row xl:items-center xl:justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" class="h-12 w-12 rounded-full">
                    <span class="leading-tight">
                        <span class="flex items-center gap-2 font-bold text-slate-900">
                            PPGT KMTr
                            <span class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-primary">Klasis</span>
                        </span>
                        <span class="block text-xs text-slate-500">Klasis Makassar Timur</span>
                    </span>
                </a>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    <nav aria-label="Menu utama" class="-mx-2 flex flex-wrap text-sm font-medium text-slate-600">
                        @foreach ($menu as $tujuan => $label)
                            <a href="{{ $tujuan }}" class="whitespace-nowrap rounded-lg px-2 py-2 transition hover:bg-blue-50 hover:text-primary">{{ $label }}</a>
                        @endforeach
                    </nav>
                    <a href="{{ url('/#layanan') }}" class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800">
                        <i class="fa-solid fa-grip text-xs" aria-hidden="true"></i> Pusat Layanan
                    </a>
                </div>
            </div>
        </header>

        @yield('isi')

        <footer class="beranda-gelap text-blue-100">
            <div class="wadah-publik py-10 lg:py-12">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-14 w-14 rounded-full border-2 border-amber-400 bg-white">
                        <div class="leading-tight">
                            <p class="font-bold text-white">PPGT Klasis Makassar Timur</p>
                            <p class="mt-1 text-xs">&copy; {{ date('Y') }} PPGT KMTr. All rights reserved.</p>
                        </div>
                    </div>

                    <nav aria-label="Menu bawah" class="flex flex-wrap gap-x-6 gap-y-2 text-xs font-medium text-white">
                        @foreach ($menu as $tujuan => $label)
                            <a href="{{ $tujuan }}" class="transition hover:text-amber-300">{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs">
                    <p>Wadah pelayanan dan kaderisasi pemuda Gereja Toraja.</p>
                    <p class="inline-flex items-center gap-2 font-medium text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> Klasis Makassar Timur
                    </p>
                </div>
            </div>
        </footer>
    </div>
@endsection
