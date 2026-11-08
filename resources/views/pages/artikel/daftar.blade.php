@extends('layouts.publik')

@section('title', 'Artikel · PPGT Klasis Makassar Timur')
@section('description', 'Kumpulan artikel dari jemaat-jemaat PPGT Klasis Makassar Timur.')

@section('isi')
    <section class="beranda-gelap text-white">
        <div class="wadah-publik pb-24 pt-14 lg:pb-28 lg:pt-20">
            <p class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-300">
                <i class="fa-regular fa-newspaper" aria-hidden="true"></i> Artikel
            </p>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">Tulisan dari jemaat</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-blue-100">Kumpulan artikel dari jemaat-jemaat di Klasis Makassar Timur.</p>
        </div>
    </section>

    <section class="flow-root bg-[#f5f7fd] pb-20 lg:pb-28">
        <div class="wadah-publik">
            <form method="GET" action="{{ route('artikel.daftar') }}" class="relative -mt-12 grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-xl shadow-blue-950/10 sm:p-5 lg:grid-cols-[1fr_18rem_auto]">
                <label class="relative block">
                    <span class="sr-only">Cari judul atau penulis</span>
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                    <input type="search" name="cari" value="{{ $cari }}" maxlength="100" placeholder="Cari judul atau penulis" class="w-full rounded-xl border-slate-200 py-3 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary">
                </label>

                <label class="block">
                    <span class="sr-only">Pilih jemaat</span>
                    <select name="jemaat" class="w-full rounded-xl border-slate-200 py-3 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Semua jemaat</option>
                        @foreach ($jemaats as $jemaat)
                            <option value="{{ $jemaat->id }}" @selected($jemaatId === $jemaat->id)>{{ $jemaat->nama }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="rounded-xl bg-blue-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-800">Cari</button>
            </form>

            @if ($cari !== '' || $jemaatId)
                <p class="mt-6 text-sm text-slate-500">
                    {{ $artikels->total() }} artikel ditemukan.
                    <a href="{{ route('artikel.daftar') }}" class="font-semibold text-primary hover:text-blue-600">Hapus pencarian</a>
                </p>
            @endif

            @if ($artikels->isEmpty())
                <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-12 text-center">
                    <i class="fa-regular fa-newspaper text-3xl text-slate-300" aria-hidden="true"></i>
                    <p class="mt-4 font-semibold text-slate-900">{{ $cari !== '' || $jemaatId ? 'Artikel tidak ditemukan' : 'Belum ada artikel' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $cari !== '' || $jemaatId ? 'Coba kata kunci atau jemaat lain.' : 'Artikel akan tampil di sini setelah disetujui.' }}</p>
                </div>
            @else
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($artikels as $artikel)
                        @include('includes.publik.artikel-kartu')
                    @endforeach
                </div>
            @endif

            @if ($artikels->hasPages())
                <nav aria-label="Halaman artikel" class="mt-10 flex flex-wrap items-center justify-between gap-3 text-sm">
                    @if ($artikels->onFirstPage())
                        <span class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 font-semibold text-slate-400">Sebelumnya</span>
                    @else
                        <a href="{{ $artikels->previousPageUrl() }}" rel="prev" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-semibold text-primary transition hover:border-primary">Sebelumnya</a>
                    @endif

                    <span class="text-slate-500">Halaman {{ $artikels->currentPage() }} dari {{ $artikels->lastPage() }}</span>

                    @if ($artikels->hasMorePages())
                        <a href="{{ $artikels->nextPageUrl() }}" rel="next" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-semibold text-primary transition hover:border-primary">Berikutnya</a>
                    @else
                        <span class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 font-semibold text-slate-400">Berikutnya</span>
                    @endif
                </nav>
            @endif
        </div>
    </section>
@endsection
