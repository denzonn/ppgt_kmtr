@extends('layouts.publik')

@php
    $ringkasan = \App\Support\KontenArtikel::ringkasan($artikel->isi, 160);
    $namaJemaat = $artikel->jemaat?->nama ? 'Jemaat '.$artikel->jemaat->nama : 'PPGT KMTr';
    $fotoSampul = $artikel->foto ?: asset('images/no-image.svg');
@endphp

@section('title', $artikel->judul.' · PPGT KMTr')
@section('description', $ringkasan)

{{-- Supaya judul dan foto muncul saat link dibagikan ke WhatsApp. --}}
@push('prepend-style')
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="PPGT Klasis Makassar Timur">
    <meta property="og:title" content="{{ $artikel->judul }}">
    <meta property="og:description" content="{{ $ringkasan }}">
    <meta property="og:url" content="{{ $artikel->urlBaca() }}">
    <meta property="og:image" content="{{ $fotoSampul }}">
@endpush

@push('addon-style')
    @vite(['resources/css/konten-artikel.css'])
    <style>
        .artikel-isi.ck-content {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-size: 1.0625rem;
            line-height: 1.9;
        }

        .artikel-isi.ck-content p {
            margin: 1.1rem 0;
        }
    </style>
@endpush

@section('isi')
    <section class="beranda-gelap text-white">
        <div class="wadah-publik pb-40 pt-10 lg:pb-52 lg:pt-14">
            <div class="mx-auto max-w-4xl">
                <a href="{{ route('artikel.daftar') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-100 transition hover:text-white">
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Semua artikel
                </a>
                <p class="mt-6">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-300">
                        <i class="fa-solid fa-church" aria-hidden="true"></i> {{ $namaJemaat }}
                    </span>
                </p>
                <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-5xl lg:leading-[1.15]">{{ $artikel->judul }}</h1>
                <p class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-blue-100">
                    <span><i class="fa-regular fa-user mr-1.5" aria-hidden="true"></i>{{ $artikel->penulis }}</span>
                    <span><i class="fa-regular fa-calendar mr-1.5" aria-hidden="true"></i>{{ $artikel->tanggalTayang() }}</span>
                </p>
            </div>
        </div>
    </section>

    <section class="pb-20 lg:pb-24">
        <div class="wadah-publik">
            <div class="mx-auto max-w-4xl">
                <img src="{{ $fotoSampul }}" alt="{{ $artikel->judul }}" class="relative -mt-28 aspect-video w-full rounded-3xl bg-slate-100 object-cover shadow-2xl shadow-blue-950/25 lg:-mt-36">

                <div class="artikel-isi ck-content mt-10">{!! $artikel->isi !!}</div>

                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Bagikan artikel ini</p>
                    <a href="https://wa.me/?text={{ rawurlencode($artikel->judul.' '.$artikel->urlBaca()) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Bagikan ke WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if ($lainnya->isNotEmpty())
        <section class="bg-[#f5f7fd] py-20 lg:py-24">
            <div class="wadah-publik">
                <p class="pil-publik"><i class="fa-regular fa-newspaper" aria-hidden="true"></i> Artikel</p>
                <h2 class="judul-publik mt-4">Artikel lainnya</h2>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($lainnya as $artikel)
                        @include('includes.publik.artikel-kartu')
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
