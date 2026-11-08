@extends('layouts.app')

@section('title', 'Pemilihan')
@section('page', $lingkup === 'jemaat' ? 'jemaat_pemilihan' : 'pemilihan')
@section('page_title', 'Pemilihan')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl bg-primary p-6 text-white shadow-lg shadow-primary/20 sm:p-8">
            <i class="fas fa-check-to-slot absolute -bottom-6 -right-4 text-[9rem] text-white/10" aria-hidden="true"></i>
            <div class="relative max-w-2xl">
                <h2 class="text-2xl font-bold">{{ $lingkup === 'jemaat' ? 'Pemilihan Jemaat' : 'Pemilihan Konferensi' }}</h2>
                <p class="mt-2 text-sm leading-6 text-blue-100">
                    {{ $lingkup === 'jemaat'
                        ? 'Buat kegiatan, pilih pemilih dari data anggota, lalu pilih per jabatan sampai ada yang ditetapkan.'
                        : 'Buat kegiatan Konferensi, pilih utusan dari data anggota tiap jemaat, lalu pilih per jabatan sampai ada yang ditetapkan.' }}
                </p>
            </div>
            <form method="POST" action="{{ route($rute . '.store') }}" class="relative mt-6 flex max-w-2xl flex-col gap-2 rounded-2xl bg-white/10 p-2 backdrop-blur sm:flex-row">
                @csrf
                <label for="nama" class="sr-only">Nama kegiatan</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required maxlength="255"
                    placeholder="{{ $lingkup === 'jemaat' ? 'Nama kegiatan, misalnya Rapat Anggota ' . date('Y') : 'Nama kegiatan, misalnya Konferensi VI' }}"
                    class="min-w-0 flex-1 rounded-xl border-0 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-white">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-blue-50">
                    <i class="fas fa-plus" aria-hidden="true"></i> Buat Kegiatan
                </button>
            </form>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($daftar as $item)
                <a href="{{ route($rute . '.show', $item->id) }}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true"><i class="fas fa-check-to-slot"></i></span>
                        <span class="text-xs text-slate-400">{{ $item->created_at->timezone('Asia/Makassar')->translatedFormat('d M Y') }}</span>
                    </div>
                    <h3 class="mt-4 text-lg font-bold leading-snug text-slate-800 group-hover:text-primary">{{ $item->nama }}</h3>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <p class="rounded-xl bg-slate-50 px-3 py-2"><span class="block text-lg font-bold text-slate-800">{{ $item->hak_suara_count }}</span><span class="text-xs text-slate-500">hak suara</span></p>
                        <p class="rounded-xl bg-slate-50 px-3 py-2"><span class="block text-lg font-bold text-slate-800">{{ $item->jabatan_count }}</span><span class="text-xs text-slate-500">jabatan</span></p>
                    </div>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">Buka <i class="fas fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                </a>
            @empty
                <div class="flex flex-col items-center rounded-3xl border-2 border-dashed border-slate-300 bg-white/60 p-12 text-center md:col-span-2 xl:col-span-3">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-xl text-primary" aria-hidden="true"><i class="fas fa-check-to-slot"></i></span>
                    <p class="mt-4 font-semibold text-slate-800">Belum ada kegiatan pemilihan</p>
                    <p class="mt-1 text-sm text-slate-500">Buat kegiatan pertama dari kolom di atas.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
