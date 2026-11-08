@extends('layouts.app')

@section('title', 'Pemilihan ' . $jabatan->nama)
@section('page', $pemilihan->lingkup === 'jemaat' ? 'jemaat_pemilihan' : 'pemilihan')
@section('page_title', 'Pemilihan')

@section('content')
    @php
        $jumlahTahap = $jabatan->tahap->count();
        $tahapBerikut = $jumlahTahap === 0 ? 'Penjaringan Bakal Calon' : ($jumlahTahap === 1 ? 'Pemilihan Calon' : 'Pemilihan Akhir');
        $bolehHapus = $jabatan->tahap->every(fn ($t) => $t->status === 'draft');
        $statusLingkaran = [
            'draft' => 'border-2 border-dashed border-slate-300 bg-white text-slate-400',
            'dibuka' => 'bg-primary text-white ring-4 ring-primary/20',
            'ditutup' => 'bg-slate-800 text-white',
        ];
    @endphp

    <datalist id="daftarTahap">
        <option value="Penjaringan Bakal Calon"></option>
        <option value="Pemilihan Calon"></option>
        <option value="Pemilihan Akhir"></option>
        <option value="Pemungutan Ulang"></option>
    </datalist>

    <div class="mx-auto max-w-5xl space-y-6">
        <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500" aria-label="Lokasi halaman">
            <a href="{{ route($rute . '.index') }}" class="hover:text-primary">Pemilihan</a>
            <i class="fas fa-chevron-right text-[9px]" aria-hidden="true"></i>
            <a href="{{ route($rute . '.show', $pemilihan->id) }}" class="hover:text-primary">{{ $pemilihan->nama }}</a>
            <i class="fas fa-chevron-right text-[9px]" aria-hidden="true"></i>
            <span class="text-slate-800">{{ $jabatan->nama }}</span>
        </nav>

        <header class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-primary/10 text-xl text-primary" aria-hidden="true"><i class="fas fa-user-tie"></i></span>
                <div>
                    <p class="text-xs font-semibold text-slate-500">Pemilihan jabatan</p>
                    <h2 class="text-2xl font-bold text-slate-800">{{ $jabatan->nama }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $jabatan->status() }} · {{ $jumlahTahap }} tahap</p>
                </div>
            </div>
            @if ($bolehHapus)
                <form method="POST" action="{{ route($rute . '.jabatan.destroy', [$pemilihan->id, $jabatan->id]) }}"
                    data-konfirmasi="Hapus jabatan {{ $jabatan->nama }}?" data-konfirmasi-teks="Jabatan dan tahap yang belum dibuka akan dihapus." data-konfirmasi-ok="Ya, hapus" data-bahaya>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-red-300 hover:text-red-600">Hapus jabatan</button>
                </form>
            @endif
        </header>

        @if ($jabatan->terpilih)
            <section class="relative overflow-hidden rounded-3xl bg-primary p-6 text-white shadow-lg sm:p-8">
                <i class="fas fa-award absolute -right-4 -top-4 text-[9rem] text-white/10" aria-hidden="true"></i>
                <p class="relative text-sm font-semibold text-blue-100">{{ $jabatan->nama }} terpilih</p>
                <p class="relative mt-1 text-3xl font-bold">{{ $jabatan->terpilih->nama }}</p>
                <p class="relative text-sm text-blue-100">{{ $jabatan->terpilih->jemaat?->nama ?? 'Klasis' }}</p>
                <div class="relative mt-6 flex flex-wrap gap-3">
                    <a href="{{ route($rute . '.show', $pemilihan->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-blue-50">
                        <i class="fas fa-plus text-xs" aria-hidden="true"></i> Lanjut ke jabatan berikutnya
                    </a>
                    <form method="POST" action="{{ route($rute . '.jabatan.batal_tetapkan', [$pemilihan->id, $jabatan->id]) }}"
                        data-konfirmasi="Batalkan penetapan?" data-konfirmasi-teks="{{ $jabatan->terpilih->nama }} tidak lagi tercatat sebagai {{ $jabatan->nama }}. Anda bisa menetapkan ulang dari tahap terakhir." data-konfirmasi-ok="Ya, batalkan" data-bahaya>
                        @csrf
                        <button type="submit" class="rounded-xl border border-white/30 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Batalkan penetapan</button>
                    </form>
                </div>
            </section>
        @endif

        @if ($jumlahTahap)
            <ol class="space-y-5">
                @foreach ($jabatan->tahap as $putaran)
                    <li class="relative grid grid-cols-[2.5rem_1fr] gap-4">
                        @unless ($loop->last && ! $jabatan->bisaTambahTahap())
                            <span class="absolute bottom-[-1.25rem] left-5 top-11 w-px -translate-x-1/2 bg-slate-200" aria-hidden="true"></span>
                        @endunless
                        <span class="relative mt-3 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold {{ $statusLingkaran[$putaran->status] }}">{{ $loop->iteration }}</span>
                        <div class="min-w-0">
                            @include('pages.pemilihan._putaran')
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if (! $jabatan->terpilih_id && $jabatan->bisaTambahTahap())
            @if (! $terakhir)
                @if ($opsiCalon->count() < 2)
                    <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                        <i class="fas fa-users text-3xl text-slate-300" aria-hidden="true"></i>
                        <p class="mt-3 font-semibold text-slate-800">Belum cukup peserta yang bisa dicalonkan</p>
                        <p class="mt-1 text-sm text-slate-500">Isi peserta dulu, minimal 2 orang.</p>
                        <a href="{{ route($rute . '.show', [$pemilihan->id, 'tab' => 'peserta']) }}" class="mt-4 inline-flex rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white">Isi peserta</a>
                    </section>
                @else
                    <form method="POST" action="{{ route($rute . '.jabatan.tahap', [$pemilihan->id, $jabatan->id]) }}" class="space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        @csrf
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-primary text-sm font-bold text-white">1</span>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Mulai tahap pertama</h3>
                                <p class="text-sm text-slate-500">Biasanya penjaringan bakal calon. Semua yang bisa dicalonkan sudah tercentang, hapus yang tidak sesuai.</p>
                            </div>
                        </div>
                        <div class="sm:max-w-md">
                            <label for="tahap" class="mb-2 block text-sm font-semibold text-slate-700">Nama tahap</label>
                            <input type="text" id="tahap" name="tahap" required maxlength="100" list="daftarTahap" value="{{ $tahapBerikut }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        @include('pages.pemilihan._calon', ['opsi' => $opsiCalon, 'suara' => []])
                        <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90">Buat Tahap</button>
                    </form>
                @endif
            @else
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full border-2 border-dashed border-primary text-sm font-bold text-primary">{{ $jumlahTahap + 1 }}</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Langkah berikutnya</h3>
                            <p class="text-sm text-slate-500">Tahap {{ $terakhir->tahap }} sudah selesai. Tetapkan yang terpilih, atau lanjut ke tahap berikutnya.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route($rute . '.jabatan.tetapkan', [$pemilihan->id, $jabatan->id]) }}"
                        data-konfirmasi="Tetapkan {{ $jabatan->nama }}?" data-konfirmasi-teks="Pemilihan jabatan ini akan selesai." data-konfirmasi-ok="Ya, tetapkan" data-konfirmasi-ikon="fa-award"
                        class="mt-6 flex flex-col gap-4 rounded-2xl border border-slate-200 p-5 md:flex-row md:items-center">
                        @csrf
                        <span class="hidden h-11 w-11 flex-none items-center justify-center rounded-xl bg-primary/10 text-primary md:flex" aria-hidden="true"><i class="fas fa-award"></i></span>
                        <div class="md:w-56 md:flex-none">
                            <h4 class="font-bold text-slate-800">Tetapkan {{ $jabatan->nama }}</h4>
                            <p class="text-sm text-slate-500">Kalau sudah ada satu nama yang naik.</p>
                        </div>
                        <select name="peserta_id" required aria-label="Nama yang ditetapkan" class="min-w-0 flex-1 rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                            @foreach ($terakhir->hasil() as $calon)
                                <option value="{{ $calon->peserta_id }}">{{ $calon->peserta->nama }} · {{ $calon->suara }} suara</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex flex-none items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"><i class="fas fa-award text-xs" aria-hidden="true"></i> Tetapkan</button>
                    </form>

                    <div class="my-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-hidden="true">
                        <span class="h-px flex-1 bg-slate-200"></span> atau <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <form method="POST" action="{{ route($rute . '.jabatan.tahap', [$pemilihan->id, $jabatan->id]) }}" class="space-y-4 rounded-2xl border border-slate-200 p-5">
                        @csrf
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div class="flex items-start gap-4">
                                <span class="hidden h-11 w-11 flex-none items-center justify-center rounded-xl bg-slate-100 text-slate-600 md:flex" aria-hidden="true"><i class="fas fa-forward"></i></span>
                                <div>
                                    <h4 class="font-bold text-slate-800">Lanjut ke tahap berikutnya</h4>
                                    <p class="text-sm text-slate-500">Calon tahap {{ $terakhir->tahap }} sudah tercentang. Hapus centang yang tidak lanjut.</p>
                                </div>
                            </div>
                            <div class="md:w-72">
                                <label for="tahapLanjut" class="mb-2 block text-sm font-semibold text-slate-700">Nama tahap</label>
                                <input type="text" id="tahapLanjut" name="tahap" required maxlength="100" list="daftarTahap" value="{{ $tahapBerikut }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        @include('pages.pemilihan._calon', ['opsi' => $opsiCalon, 'suara' => $suaraSebelumnya])
                        <button type="submit" class="rounded-xl border border-primary px-5 py-3 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">Buat Tahap Berikutnya</button>
                    </form>
                </section>
            @endif
        @endif
    </div>

    @include('pages.pemilihan._konfirmasi')
@endsection
