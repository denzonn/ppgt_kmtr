@extends('layouts.app')

@section('title', 'Rekap Pendampingan')
@section('page', 'pendampingan')
@section('page_title', 'Rekap Pendampingan')

@section('content')
    @php
        $alamat = fn (array $tambahan = []) => route('kepengurusan.pendampingan.index', [$periode, ...$tambahan]);
        $chip = fn (bool $aktif) => 'whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition ' . ($aktif ? 'bg-primary text-white' : 'text-slate-500 hover:bg-white hover:text-primary');
        $namaBulan = fn (string $bulan) => \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Rekap Pendampingan</h2>
                <p class="mt-1 text-sm text-slate-500">Kegiatan yang didampingi pengurus klasis {{ $bulan ? $namaBulan($bulan) : 'periode ' . $periode->nama }}, dari rekap yang dikirim lewat Telegram.</p>
            </div>
            <span class="inline-flex flex-none items-center gap-1.5 self-start whitespace-nowrap text-xs font-medium {{ $botAktif ? 'text-primary' : 'text-slate-400' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $botAktif ? 'bg-emerald-500' : 'bg-slate-300' }}" aria-hidden="true"></span>
                {{ $botAktif ? 'Bot Telegram aktif' : 'Bot Telegram belum diatur' }}
            </span>
        </div>

        <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['fa-calendar-check', 'Kegiatan', $jumlahKegiatan], ['fa-calendar-day', 'Hari', $perTanggal->count()], ['fa-user-check', 'Total kehadiran', $jumlahKehadiran], ['fa-users', 'Orang berbeda', $peringkat->count()]] as [$ikon, $label, $nilai])
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true"><i class="fas {{ $ikon }}"></i></span>
                    <div>
                        <dt class="text-xs text-slate-500">{{ $label }}</dt>
                        <dd class="text-xl font-bold text-slate-800">{{ $nilai }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>

        @if ($daftarBulan->count() > 1)
            <nav class="-mx-1 flex gap-1 overflow-x-auto px-1" aria-label="Saring bulan">
                <a href="{{ $alamat() }}" class="{{ $chip(! $bulan) }}">Semua</a>
                @foreach ($daftarBulan as $item)
                    <a href="{{ $alamat(['bulan' => $item]) }}" class="{{ $chip($bulan === $item) }}">{{ $namaBulan($item) }}</a>
                @endforeach
            </nav>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-12">
            <section class="lg:col-span-8" aria-label="Daftar kegiatan">
                @if ($perTanggal->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-10 text-center">
                        <p class="font-semibold text-slate-700">Belum ada rekap pendampingan</p>
                        <p class="mt-1 text-sm text-slate-500">Kirim rekap ke bot Telegram, atau tempel teksnya lewat <strong>Tempel rekap manual</strong>.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($perTanggal as $tanggal => $daftar)
                            @php $hari = \Illuminate\Support\Carbon::parse($tanggal); @endphp
                            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <header class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
                                    <span class="flex h-11 w-11 flex-none flex-col items-center justify-center rounded-xl bg-primary text-white">
                                        <span class="text-base font-bold leading-none">{{ $hari->format('d') }}</span>
                                        <span class="text-[10px] uppercase">{{ $hari->translatedFormat('M') }}</span>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-slate-800">{{ $hari->translatedFormat('l, d F Y') }}</h3>
                                        <p class="text-xs text-slate-500">{{ $daftar->count() }} kegiatan</p>
                                    </div>
                                </header>

                                <ul class="divide-y divide-slate-100">
                                    @foreach ($daftar as $kegiatan)
                                        <li class="flex items-start gap-3 px-4 py-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-slate-800">{{ $kegiatan->nama }}</p>
                                                @if ($kegiatan->hadir)
                                                    <p class="mt-2 flex flex-wrap gap-1.5">
                                                        @foreach ($kegiatan->hadir as $nama)
                                                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $nama }}</span>
                                                        @endforeach
                                                    </p>
                                                @else
                                                    <p class="mt-1 text-xs text-slate-400">Belum ada yang tercatat hadir.</p>
                                                @endif
                                                @if ($kegiatan->sambutan)
                                                    <p class="mt-2 text-xs text-slate-500"><i class="fas fa-microphone mr-1 text-primary" aria-hidden="true"></i>Sambutan: <span class="font-semibold text-slate-700">{{ implode(', ', $kegiatan->sambutan) }}</span></p>
                                                @endif
                                            </div>
                                            <span class="flex-none rounded-lg bg-primary/10 px-2 py-1 text-xs font-bold text-primary" title="Jumlah yang hadir">{{ count($kegiatan->hadir) }}</span>

                                            <details class="relative flex-none">
                                                <summary class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary" aria-label="Ubah {{ $kegiatan->nama }}">
                                                    <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                                                </summary>
                                                <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                                                    <form method="POST" action="{{ route('kepengurusan.pendampingan.update', [$periode, $kegiatan->id]) }}" class="space-y-3">
                                                        @csrf
                                                        @method('PUT')
                                                        <label class="block text-xs font-semibold text-slate-500">Tanggal
                                                            <input type="date" name="tanggal" value="{{ $kegiatan->tanggal->toDateString() }}" required class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                                                        </label>
                                                        <label class="block text-xs font-semibold text-slate-500">Kegiatan
                                                            <input type="text" name="nama" value="{{ $kegiatan->nama }}" required maxlength="255" class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                                                        </label>
                                                        <label class="block text-xs font-semibold text-slate-500">Hadir (pisahkan dengan koma)
                                                            <textarea name="hadir" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">{{ implode(', ', $kegiatan->hadir) }}</textarea>
                                                        </label>
                                                        <label class="block text-xs font-semibold text-slate-500">Sambutan (boleh kosong)
                                                            <input type="text" name="sambutan" value="{{ implode(', ', $kegiatan->sambutan ?? []) }}" maxlength="1000" class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                                                        </label>
                                                        <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">Simpan</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('kepengurusan.pendampingan.destroy', [$periode, $kegiatan->id]) }}" class="mt-2"
                                                        data-konfirmasi="Hapus kegiatan ini?" data-konfirmasi-teks="{{ $kegiatan->nama }} pada {{ $hari->translatedFormat('d F Y') }} dihapus dari rekap." data-konfirmasi-ok="Ya, hapus" data-bahaya>
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full rounded-xl px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">Hapus kegiatan</button>
                                                    </form>
                                                </div>
                                            </details>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside class="space-y-4 lg:col-span-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800">Paling sering hadir</h3>
                    @if ($peringkat->isEmpty())
                        <p class="mt-2 text-sm text-slate-400">Belum ada data.</p>
                    @else
                        <ol class="mt-3 space-y-2 text-sm">
                            @foreach ($peringkat->take(5) as $orang)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="truncate text-slate-700"><span class="mr-2 inline-block w-4 text-xs text-slate-400">{{ $loop->iteration }}</span>{{ $orang['nama'] }}</span>
                                    <span class="flex-none text-xs font-semibold text-slate-500">{{ $orang['jumlah'] }}×</span>
                                </li>
                            @endforeach
                        </ol>
                        @if ($peringkat->count() > 5)
                            <details class="mt-2 text-sm">
                                <summary class="cursor-pointer text-xs font-semibold text-primary">Lihat {{ $peringkat->count() - 5 }} lainnya</summary>
                                <ol start="6" class="mt-2 max-h-64 space-y-2 overflow-y-auto">
                                    @foreach ($peringkat->slice(5) as $orang)
                                        <li class="flex items-center justify-between gap-3">
                                            <span class="truncate text-slate-600"><span class="mr-2 inline-block w-4 text-xs text-slate-400">{{ $loop->iteration + 5 }}</span>{{ $orang['nama'] }}</span>
                                            <span class="flex-none text-xs text-slate-500">{{ $orang['jumlah'] }}×</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif
                    @endif
                </section>

                <details class="group rounded-2xl border border-slate-200 bg-white shadow-sm" @if (session('abaikan') || $errors->has('teks') || old('teks')) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 text-sm font-bold text-slate-800">
                        Tempel rekap manual
                        <i class="fas fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180" aria-hidden="true"></i>
                    </summary>
                    <div class="border-t border-slate-100 px-5 pb-5 pt-4">
                        @if (session('abaikan'))
                            <div class="mb-3 rounded-xl bg-slate-100 p-3 text-xs text-slate-600">
                                <p class="font-semibold text-slate-700">Baris yang tidak terbaca:</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach (session('abaikan') as $baris)
                                        <li class="truncate">{{ $baris }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('kepengurusan.pendampingan.tempel', $periode) }}" class="space-y-3">
                            @csrf
                            <label for="teksRekap" class="sr-only">Teks rekap</label>
                            <textarea id="teksRekap" name="teks" rows="7" required class="w-full rounded-xl border-slate-200 font-mono text-xs focus:border-primary focus:ring-primary"
                                placeholder="TANGGAL 02 NOVEMBER (SENIN)&#10;* Rapat SC RA Tamalanrea&#10;Hadir : Rocky, Cici&#10;Sambutan : Rocky">{{ old('teks') }}</textarea>
                            <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">Simpan Rekap</button>
                        </form>

                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            Tulis <strong>TANGGAL 02 NOVEMBER</strong>, lalu kegiatan diawali <strong>*</strong>, lalu <strong>Hadir :</strong> dengan nama dipisah koma. Tambahkan <strong>Sambutan :</strong> bila ada yang membawakan sambutan.
                            Mengirim ulang kegiatan yang sama akan memperbarui daftar hadirnya.
                        </p>
                    </div>
                </details>
            </aside>
        </div>
    </div>

    @include('pages.pemilihan._konfirmasi')
@endsection
