{{-- Satu tahap pemungutan suara. Butuh: $putaran, $pemilihan, $rute, $jumlahHakSuara, $opsiDraft. --}}
@php
    $alamat = fn (string $aksi) => route($rute . '.putaran.' . $aksi, [$pemilihan->id, $putaran->id]);
    $sudah = $putaran->jumlahMemilih();
    $hasil = $putaran->hasil();
    $totalSuara = $hasil->sum('suara');
    $batas = \App\Models\PemilihanPutaran::batasMutlak($totalSuara);
    $tertinggi = $hasil->first()?->suara ?? 0;
    $seri = $tertinggi > 0 && $hasil->where('suara', $tertinggi)->count() > 1;
@endphp

<article class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $putaran->status === 'dibuka' ? 'border-primary ring-4 ring-primary/10' : 'border-slate-200' }}">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
        <div>
            <h3 class="font-bold text-slate-800">{{ $putaran->tahap }}</h3>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $putaran->calon->count() }} calon
                @if ($putaran->ditutup_pada)
                    · ditutup {{ $putaran->ditutup_pada->timezone('Asia/Makassar')->translatedFormat('d M, H:i') }}
                @endif
            </p>
        </div>
        @if ($putaran->status === 'draft')
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Belum dibuka</span>
        @elseif ($putaran->status === 'dibuka')
            <span class="inline-flex items-center gap-2 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white"><span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>Sedang dibuka</span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-white"><i class="fas fa-check text-[10px]"></i>Selesai</span>
        @endif
    </header>

    <div class="p-5">
        @if ($putaran->status === 'draft')
            <form method="POST" action="{{ $alamat('calon') }}" class="space-y-3">
                @csrf
                @method('PUT')
                @include('pages.pemilihan._calon', ['opsi' => $opsiDraft, 'tercentang' => $putaran->calon->pluck('peserta_id')->all(), 'suara' => []])
                <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-primary hover:text-primary">Simpan perubahan calon</button>
            </form>

            <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">Sudah benar calonnya? Buka saat pemilih siap maju ke bilik suara.</p>
                <div class="flex flex-none gap-2">
                    <form method="POST" action="{{ route($rute . '.putaran.destroy', [$pemilihan->id, $putaran->id]) }}"
                        data-konfirmasi="Hapus tahap ini?" data-konfirmasi-teks="Tahap {{ $putaran->tahap }} dan daftar calonnya akan dihapus." data-konfirmasi-ok="Ya, hapus" data-bahaya>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-red-300 hover:text-red-600">Hapus</button>
                    </form>
                    <form method="POST" action="{{ $alamat('buka') }}"
                        data-konfirmasi="Buka pemungutan suara?" data-konfirmasi-teks="Calon tidak bisa diubah lagi setelah dibuka. Pemilih bisa langsung memilih di bilik suara." data-konfirmasi-ok="Ya, buka" data-konfirmasi-ikon="fa-play">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"><i class="fas fa-play text-xs" aria-hidden="true"></i> Buka Pemungutan Suara</button>
                    </form>
                </div>
            </div>
        @elseif ($putaran->status === 'dibuka')
            <div data-putaran-aktif data-url="{{ $alamat('status') }}" class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-sm text-slate-500">Sudah memilih</p>
                    <p class="mt-1 text-4xl font-bold text-primary"><span data-sudah>{{ $sudah }}</span> <span class="text-base font-semibold text-slate-400">dari {{ $jumlahHakSuara }} pemilih</span></p>
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div data-bar data-total="{{ $jumlahHakSuara }}" class="h-full rounded-full bg-primary transition-all" style="width: {{ $jumlahHakSuara ? round($sudah / $jumlahHakSuara * 100) : 0 }}%"></div>
                    </div>
                    <details class="mt-3 text-sm">
                        <summary class="cursor-pointer font-semibold text-primary">Lihat yang belum memilih</summary>
                        <p data-belum class="mt-2 leading-6 text-slate-600">{{ $putaran->belumMemilih()->join(', ') ?: '-' }}</p>
                    </details>
                    <p class="mt-3 text-xs text-slate-500"><i class="fas fa-lock mr-1" aria-hidden="true"></i>Hasil dirahasiakan sampai pemungutan suara ditutup.</p>
                </div>
                <form method="POST" action="{{ $alamat('tutup') }}"
                    data-konfirmasi="Tutup pemungutan suara?" data-konfirmasi-teks="Pemungutan suara yang sudah ditutup tidak bisa dibuka lagi. Pastikan semua pemilih sudah memilih." data-konfirmasi-ok="Ya, tutup" data-konfirmasi-ikon="fa-flag-checkered">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"><i class="fas fa-stop text-xs" aria-hidden="true"></i> Tutup dan Lihat Hasil</button>
                </form>
            </div>
        @else
            <div class="mb-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-500">
                <span><strong class="text-slate-800">{{ $totalSuara }}</strong> suara masuk dari {{ $jumlahHakSuara }} pemilih</span>
                <span>Suara terbanyak mutlak: <strong class="text-slate-800">{{ $batas }}</strong></span>
            </div>

            {{-- Daftar panjang dipotong supaya tahap penjaringan tidak memenuhi layar. --}}
            <ol class="space-y-3">
                @foreach ($hasil as $calon)
                    @if ($loop->index === 8 && $hasil->count() > 10)
                        </ol>
                        <details class="group mt-3">
                            <summary class="cursor-pointer list-none text-sm font-semibold text-primary">
                                <span class="group-open:hidden">Tampilkan {{ $hasil->count() - 8 }} calon lainnya</span>
                                <span class="hidden group-open:inline">Sembunyikan</span>
                            </summary>
                            <ol class="mt-3 space-y-3">
                    @endif
                    <li>
                        <div class="flex items-baseline justify-between gap-3 text-sm">
                            <span class="min-w-0">
                                <span class="font-semibold text-slate-800">{{ $calon->peserta->nama }}</span>
                                <span class="text-xs text-slate-500">{{ $calon->peserta->jemaat?->nama ?? 'Klasis' }}</span>
                                @if ($calon->suara >= $batas)
                                    <span class="ml-1 rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold text-white">Mutlak</span>
                                @endif
                            </span>
                            <span class="flex-none font-bold text-slate-800">{{ $calon->suara }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $calon->suara === $tertinggi && $tertinggi > 0 ? 'bg-primary' : 'bg-slate-300' }}" style="width: {{ $totalSuara ? round($calon->suara / $totalSuara * 100) : 0 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ol>
            @if ($hasil->count() > 10)
                </details>
            @endif

            @if ($seri)
                <p class="mt-4 flex gap-3 rounded-xl bg-slate-100 p-3 text-sm text-slate-700">
                    <i class="fas fa-scale-balanced mt-0.5 text-primary" aria-hidden="true"></i>
                    <span>Suara tertinggi sama. Lakukan pemungutan ulang. Kalau tetap sama setelah dua kali, pimpinan sidang yang memutuskan (AD Pasal 12).</span>
                </p>
            @endif
        @endif
    </div>
</article>
