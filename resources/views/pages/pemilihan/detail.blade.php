@extends('layouts.app')

@section('title', $pemilihan->nama)
@section('page', $pemilihan->lingkup === 'jemaat' ? 'jemaat_pemilihan' : 'pemilihan')
@section('page_title', 'Pemilihan')

@section('content')
    @php
        $klasis = $pemilihan->lingkup === 'klasis';
        $tombolTab = fn (bool $aktif) => 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition ' . ($aktif ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800');
        $warnaStatus = [
            'Selesai' => 'bg-slate-800 text-white',
            'Pemungutan dibuka' => 'bg-primary text-white',
            'Berlangsung' => 'bg-blue-50 text-primary',
            'Belum dimulai' => 'bg-slate-100 text-slate-600',
        ];
        $selesai = $daftarJabatan->whereNotNull('terpilih_id');
        $angka = [
            ['fa-check-to-slot', 'Hak suara', $jumlahHakSuara],
            ['fa-user-check', 'Bisa dicalonkan', $jumlahCalon],
            ['fa-user-tie', 'Jabatan', $daftarJabatan->count()],
            ['fa-award', 'Sudah ditetapkan', $selesai->count()],
        ];
        $maksPenuh = \App\Models\PemilihanPeserta::MAKS_UTUSAN_PENUH;
        $inisial = fn (string $nama) => collect(explode(' ', $nama))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->join('');
        $tombolKecil = 'inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-primary hover:text-primary';
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-primary text-xl text-white shadow-lg shadow-primary/20" aria-hidden="true"><i class="fas fa-check-to-slot"></i></span>
                    <div class="min-w-0">
                        <a href="{{ route($rute . '.index') }}" class="text-xs font-semibold text-slate-500 hover:text-primary"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Semua kegiatan</a>
                        <h2 class="mt-1 text-2xl font-bold text-slate-800">{{ $pemilihan->nama }}</h2>
                        <p class="text-sm text-slate-500">{{ $klasis ? 'Pemilihan Konferensi Klasis' : 'Pemilihan Jemaat' }} · dibuat {{ $pemilihan->created_at->timezone('Asia/Makassar')->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-start gap-2">
                    <a href="{{ route($rute . '.kartu', $pemilihan->id) }}" class="inline-flex items-center gap-2 rounded-xl border border-primary px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white"><i class="fas fa-file-pdf" aria-hidden="true"></i> Unduh Kartu Kode (PDF)</a>
                    <form method="POST" action="{{ route($rute . '.destroy', $pemilihan->id) }}"
                        data-konfirmasi="Hapus kegiatan ini?" data-konfirmasi-teks="Semua peserta, jabatan, dan hasil suara {{ $pemilihan->nama }} ikut terhapus dan tidak bisa dikembalikan." data-konfirmasi-ok="Ya, hapus" data-bahaya>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-red-300 hover:text-red-600">Hapus</button>
                    </form>
                </div>
            </div>
            <dl class="grid grid-cols-2 border-t border-slate-100 bg-slate-50/60 lg:grid-cols-4">
                @foreach ($angka as [$ikon, $label, $nilai])
                    <div class="flex items-center gap-3 px-6 py-4 {{ $loop->odd ? '' : 'border-l border-slate-100' }} {{ $loop->index === 2 ? 'lg:border-l' : '' }} {{ $loop->index >= 2 ? 'border-t border-slate-100 lg:border-t-0' : '' }}">
                        <i class="fas {{ $ikon }} text-primary/70" aria-hidden="true"></i>
                        <div>
                            <dt class="text-xs text-slate-500">{{ $label }}</dt>
                            <dd class="text-xl font-bold text-slate-800">{{ $nilai }}</dd>
                        </div>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-1 gap-4">
                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true"><i class="fas fa-person-booth"></i></span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Layar bilik suara</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Buka di satu laptop atau HP di depan ruangan, lalu pemilih maju satu per satu. Jangan bagikan link ini ke grup.</p>
                    <input type="text" readonly value="{{ route('bilik.index', $pemilihan->token) }}" aria-label="Link bilik suara" onclick="this.select()" class="mt-2 w-full max-w-xl rounded-lg border-slate-200 bg-slate-50 px-3 py-1.5 font-mono text-xs text-slate-600">
                </div>
            </div>
            <a href="{{ route('bilik.index', $pemilihan->token) }}" target="_blank" rel="noopener" class="inline-flex flex-none items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"><i class="fas fa-up-right-from-square text-xs" aria-hidden="true"></i> Buka Bilik Suara</a>
        </section>

        <nav class="inline-flex gap-1 rounded-2xl bg-slate-200/70 p-1" aria-label="Bagian pemilihan">
            <a href="{{ route($rute . '.show', $pemilihan->id) }}" class="{{ $tombolTab($tab === 'jabatan') }}"><i class="fas fa-user-tie text-xs" aria-hidden="true"></i> Jabatan <span class="rounded-md bg-slate-100 px-1.5 text-xs text-slate-600">{{ $daftarJabatan->count() }}</span></a>
            <a href="{{ route($rute . '.show', [$pemilihan->id, 'tab' => 'peserta']) }}" class="{{ $tombolTab($tab === 'peserta') }}"><i class="fas fa-users text-xs" aria-hidden="true"></i> Peserta <span class="rounded-md bg-slate-100 px-1.5 text-xs text-slate-600">{{ $peserta->count() }}</span></a>
        </nav>

        @if ($tab === 'jabatan')
            @if ($selesai->isNotEmpty())
                <section class="rounded-2xl bg-primary p-5 text-white shadow-sm">
                    <h3 class="text-xs font-semibold text-blue-100">Hasil yang sudah ditetapkan</h3>
                    <dl class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($selesai as $item)
                            <div>
                                <dt class="text-xs text-blue-100">{{ $item->nama }}</dt>
                                <dd class="font-bold">{{ $item->terpilih?->nama }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            @if ($jumlahCalon < 2)
                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true"><i class="fas fa-circle-info"></i></span>
                        Isi peserta dulu sebelum memulai pemilihan jabatan.
                    </p>
                    <a href="{{ route($rute . '.show', [$pemilihan->id, 'tab' => 'peserta']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white">Isi peserta <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($daftarJabatan as $item)
                    @php
                        $status = $item->status();
                        $terakhir = $item->tahapTerakhir();
                    @endphp
                    <a href="{{ route($rute . '.jabatan.show', [$pemilihan->id, $item->id]) }}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true"><i class="fas {{ $item->terpilih_id ? 'fa-award' : 'fa-user-tie' }}"></i></span>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $warnaStatus[$status] }}">{{ $status }}</span>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-800 group-hover:text-primary">{{ $item->nama }}</h3>
                        <p class="mt-1 flex-1 text-sm text-slate-500">
                            @if ($item->terpilih)
                                Terpilih: <strong class="text-slate-800">{{ $item->terpilih->nama }}</strong>
                            @elseif ($terakhir)
                                Tahap {{ $item->tahap->count() }}: {{ $terakhir->tahap }}
                            @else
                                Belum ada tahap
                            @endif
                        </p>
                        <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">Kelola <i class="fas fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                    </a>
                @endforeach

                <form method="POST" action="{{ route($rute . '.jabatan.store', $pemilihan->id) }}" class="flex flex-col justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white/60 p-5">
                    @csrf
                    <label for="namaJabatan" class="text-sm font-bold text-slate-800"><i class="fas fa-plus mr-1.5 text-primary" aria-hidden="true"></i>{{ $daftarJabatan->isEmpty() ? 'Mulai dari jabatan pertama' : 'Pemilihan jabatan berikutnya' }}</label>
                    <p class="mt-1 text-xs text-slate-500">Contoh: Ketua, lalu Sekretaris, lalu Bendahara.</p>
                    <div class="mt-3 flex gap-2">
                        <input type="text" id="namaJabatan" name="nama" required maxlength="100" list="daftarJabatan" placeholder="Nama jabatan" class="min-w-0 flex-1 rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary">
                        <button type="submit" class="flex-none rounded-xl bg-primary px-4 text-sm font-semibold text-white transition hover:opacity-90">Buat</button>
                    </div>
                    <datalist id="daftarJabatan">
                        @foreach (['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'] as $saran)
                            <option value="{{ $saran }}"></option>
                        @endforeach
                    </datalist>
                </form>
            </div>
        @elseif ($klasis)
            @php
                $demisioner = $peserta->where('peran', 'demisioner');
                $jemaatLengkap = $jemaats->filter(fn ($j) => $peserta->where('jemaat_id', $j->id)->where('peran', 'utusan_penuh')->count() >= 3)->count();
            @endphp

            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-600">
                    <strong class="text-slate-800">{{ $jemaatLengkap }}/{{ $jemaats->count() }}</strong> jemaat sudah punya 3 utusan penuh.
                    Pilih utusan dari data anggota tiap jemaat.
                </p>
                <p class="flex flex-wrap gap-2 text-xs">
                    <span class="rounded-lg bg-primary px-2.5 py-1 font-semibold text-white">{{ $peserta->where('peran', 'utusan_penuh')->count() }} utusan penuh</span>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-semibold text-slate-600">{{ $peserta->where('peran', 'utusan_cadangan')->count() }} cadangan</span>
                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-semibold text-primary">{{ $demisioner->count() }} demisioner</span>
                </p>
            </div>

            <div class="grid items-start gap-4 lg:grid-cols-2">
                @foreach ($jemaats as $jemaat)
                    @php
                        $utusan = $peserta->where('jemaat_id', $jemaat->id)->whereIn('peran', ['utusan_penuh', 'utusan_cadangan'])->sortByDesc('peran');
                        $penuh = $utusan->where('peran', 'utusan_penuh')->count();
                        $cadangan = $utusan->where('peran', 'utusan_cadangan')->count();
                    @endphp
                    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <header class="flex items-center gap-3 border-b border-slate-100 p-4">
                            <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-slate-800 text-xs font-bold uppercase text-white" aria-hidden="true">{{ $inisial($jemaat->nama) }}</span>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-bold text-slate-800">{{ $jemaat->nama }}</h3>
                                <div class="mt-1.5 flex gap-4 text-[11px] text-slate-500">
                                    @foreach ([['Penuh', $penuh, 'bg-primary'], ['Cadangan', $cadangan, 'bg-slate-400']] as [$label, $isi, $warna])
                                        <span class="flex items-center gap-1.5 whitespace-nowrap">
                                            <span class="flex gap-0.5" aria-hidden="true">
                                                @for ($i = 0; $i < 3; $i++)
                                                    <span class="h-1.5 w-3 rounded-full {{ $i < $isi ? $warna : 'bg-slate-200' }}"></span>
                                                @endfor
                                            </span>
                                            {{ $label }} {{ $isi }}/3
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex flex-none gap-1.5">
                                @if ($penuh)
                                    <a href="{{ route($rute . '.kartu', [$pemilihan->id, 'jemaat' => $jemaat->id]) }}" class="{{ $tombolKecil }}" title="Unduh kartu kode jemaat ini"><i class="fas fa-file-pdf" aria-hidden="true"></i><span class="sr-only">Kartu PDF {{ $jemaat->nama }}</span></a>
                                @endif
                                <button type="button" class="{{ $tombolKecil }}" data-tambah-peserta="{{ json_encode(['jemaat_id' => $jemaat->id, 'jemaat' => $jemaat->nama, 'sisa' => max(0, $maksPenuh - $penuh)]) }}">
                                    <i class="fas fa-plus" aria-hidden="true"></i> Utusan
                                </button>
                            </div>
                        </header>
                        @if ($utusan->isEmpty())
                            <div class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                                <p class="text-sm text-slate-400">Belum ada utusan.</p>
                                <button type="button" class="text-sm font-semibold text-primary hover:underline" data-tambah-peserta="{{ json_encode(['jemaat_id' => $jemaat->id, 'jemaat' => $jemaat->nama, 'sisa' => $maksPenuh]) }}">Cari dari data anggota</button>
                            </div>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach ($utusan as $orang)
                                    @include('pages.pemilihan._peserta-baris')
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-blue-50 text-primary" aria-hidden="true"><i class="fas fa-user-clock"></i></span>
                        <div>
                            <h3 class="font-bold text-slate-800">Pengurus Demisioner <span class="font-normal text-slate-400">({{ $demisioner->count() }})</span></h3>
                            <p class="text-xs text-slate-500">Bisa dicalonkan, tetapi tidak punya hak suara.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route($rute . '.peserta.demisioner', $pemilihan->id) }}" class="flex gap-2">
                            @csrf
                            <select name="periode_id" aria-label="Periode pengurus" class="rounded-xl border-slate-200 py-2 text-xs focus:border-primary focus:ring-primary">
                                @foreach ($periodes as $periodeItem)
                                    <option value="{{ $periodeItem->id }}">Periode {{ $periodeItem->nama }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="{{ $tombolKecil }}"><i class="fas fa-download" aria-hidden="true"></i> Ambil pengurus</button>
                        </form>
                    </div>
                </header>
                <ul class="grid divide-y divide-slate-100 md:grid-cols-2 md:divide-y-0">
                    @forelse ($demisioner as $orang)
                        @include('pages.pemilihan._peserta-baris')
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-slate-400 md:col-span-2">Belum ada pengurus demisioner. Ambil dari data pengurus periode di atas.</li>
                    @endforelse
                </ul>
            </section>
        @else
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-3 border-b border-slate-100 p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">Pemilih <span class="font-normal text-slate-400">({{ $peserta->count() }})</span></h3>
                        <p class="text-xs text-slate-500">Semua pemilih juga bisa dicalonkan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route($rute . '.peserta.anggota', $pemilihan->id) }}"
                            data-konfirmasi="Ambil semua anggota aktif?" data-konfirmasi-teks="Semua anggota aktif jemaat ini ditambahkan sebagai pemilih. Nama yang sudah ada dilewati." data-konfirmasi-ok="Ya, tambahkan" data-konfirmasi-ikon="fa-users">
                            @csrf
                            <button type="submit" class="{{ $tombolKecil }}"><i class="fas fa-users" aria-hidden="true"></i> Ambil semua anggota aktif</button>
                        </form>
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white" data-tambah-peserta="{{ json_encode(['peran' => 'pemilih']) }}"><i class="fas fa-plus" aria-hidden="true"></i> Pilih anggota</button>
                    </div>
                </header>
                <ul class="grid divide-y divide-slate-100 md:grid-cols-2 md:divide-y-0">
                    @forelse ($peserta as $orang)
                        @include('pages.pemilihan._peserta-baris')
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-slate-400 md:col-span-2">Belum ada pemilih.</li>
                    @endforelse
                </ul>
            </section>
        @endif
    </div>

    {{-- Modal memilih peserta dari data anggota --}}
    <div id="modalPeserta" class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-xl -translate-y-6 scale-95 rounded-3xl bg-white shadow-2xl transition-all duration-300">
            <form id="formPeserta" method="POST" action="{{ route($rute . '.peserta.store', $pemilihan->id) }}">
                @csrf
                <input type="hidden" name="jemaat_id" id="pesertaJemaatId">
                <input type="hidden" name="peran" id="pesertaPeranTetap">

                <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div>
                        <h3 id="pesertaJudul" class="text-lg font-bold text-slate-800">Tambah peserta</h3>
                        <p id="pesertaSub" class="text-sm text-slate-500"></p>
                    </div>
                    <button type="button" onclick="closeModal('modalPeserta')" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100" aria-label="Tutup"><i class="fa-solid fa-xmark text-lg"></i></button>
                </header>

                <div class="space-y-4 px-6 py-5">
                    @if ($klasis)
                        <div>
                            <div class="grid grid-cols-2 gap-1 rounded-2xl bg-slate-100 p-1">
                                @foreach (['utusan_penuh' => ['Utusan Penuh', 'Memilih dan bisa dicalonkan'], 'utusan_cadangan' => ['Utusan Cadangan', 'Pengganti utusan penuh']] as $nilai => [$label, $ket])
                                    <label class="cursor-pointer has-[:disabled]:cursor-not-allowed">
                                        <input type="radio" name="peran" value="{{ $nilai }}" class="peer sr-only" data-peran-utusan>
                                        <span class="block rounded-xl px-3 py-2 text-center transition peer-checked:bg-white peer-checked:shadow-sm peer-disabled:opacity-50">
                                            <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                                            <span class="block text-[11px] text-slate-500" @if ($nilai === 'utusan_penuh') data-ket-penuh @endif>{{ $ket }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p id="pesertaInfoSlot" class="mt-2 text-xs text-slate-500"></p>
                        </div>
                    @endif

                    <div>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                            <input type="search" id="pesertaCari" placeholder="Ketik nama atau nomor anggota" autocomplete="off" aria-label="Cari anggota" aria-controls="pesertaHasil"
                                class="w-full rounded-xl border-slate-200 py-3 pl-10 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        {{-- Hasil hanya muncul saat mengetik, jadi tidak ada daftar panjang. --}}
                        <ul id="pesertaHasil" class="mt-2 hidden divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 shadow-sm" aria-live="polite"></ul>
                    </div>

                    <div id="pesertaTerpilihBungkus" class="hidden">
                        <p class="mb-2 text-xs font-semibold text-slate-500">Dipilih</p>
                        <ul id="pesertaTerpilih" class="space-y-2"></ul>
                    </div>

                    <details class="rounded-2xl border border-dashed border-slate-300 p-3 text-sm">
                        <summary class="cursor-pointer font-semibold text-slate-600">Nama tidak ada di data anggota?</summary>
                        <label for="pesertaManual" class="mt-3 block text-xs text-slate-500">Ketik manual, satu nama per baris.</label>
                        <textarea id="pesertaManual" name="nama" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-primary focus:ring-primary"></textarea>
                    </details>
                </div>

                <footer class="flex flex-col-reverse gap-2 border-t border-slate-100 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalPeserta')" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" id="pesertaKirim" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">Tambahkan <span id="pesertaJumlah"></span></button>
                </footer>
            </form>
        </div>
    </div>

    @include('pages.pemilihan._konfirmasi')
@endsection

@push('addon-script')
    <script>
        (function() {
            const urlAnggota = @json(route($rute . '.anggota', $pemilihan->id));
            const klasis = @json($klasis);
            const maksPenuh = @json($maksPenuh);
            const form = document.getElementById('formPeserta');
            const cari = document.getElementById('pesertaCari');
            const hasil = document.getElementById('pesertaHasil');
            const terpilihEl = document.getElementById('pesertaTerpilih');
            const kirim = document.getElementById('pesertaKirim');
            let terpilih = new Map();
            let jemaatId = null;
            let sisaPenuh = Infinity;
            let tunda = null;
            let permintaan = 0;

            const peranDipilih = () => form.querySelector('input[name=peran]:checked:not(:disabled)')?.value;

            // Batas hanya berlaku untuk utusan penuh. Server juga memeriksa batas yang sama.
            function batas() {
                return klasis && peranDipilih() === 'utusan_penuh' ? sisaPenuh : Infinity;
            }

            function perbaruiSlot() {
                const info = document.getElementById('pesertaInfoSlot');
                if (!info) return;
                const lebih = terpilih.size > batas();
                info.className = 'mt-2 text-xs ' + (lebih ? 'font-semibold text-red-600' : 'text-slate-500');
                info.textContent = peranDipilih() === 'utusan_penuh'
                    ? (lebih ? `Utusan penuh maksimal ${maksPenuh}. Sisa slot jemaat ini ${sisaPenuh}, hapus ${terpilih.size - sisaPenuh} nama.` : `Sisa slot utusan penuh: ${sisaPenuh - terpilih.size} dari ${maksPenuh}.`)
                    : 'Utusan cadangan tidak dibatasi.';
                kirim.disabled = lebih;
            }

            function elemen(tag, kelas, teks) {
                const el = document.createElement(tag);
                if (kelas) el.className = kelas;
                if (teks !== undefined) el.textContent = teks;
                return el;
            }

            function gambarTerpilih() {
                terpilihEl.replaceChildren();
                form.querySelectorAll('input[name="anggota[]"]').forEach(el => el.remove());
                terpilih.forEach(function(a, id) {
                    const li = elemen('li', 'flex items-center gap-3 rounded-xl bg-blue-50/70 px-3 py-2');
                    const teks = elemen('span', 'min-w-0 flex-1');
                    teks.append(elemen('span', 'block truncate text-sm font-semibold text-slate-800', a.nama), elemen('span', 'block text-xs text-slate-500', a.keterangan || ''));
                    const hapus = elemen('button', 'flex h-8 w-8 flex-none items-center justify-center rounded-lg text-slate-400 transition hover:bg-white hover:text-red-600');
                    hapus.type = 'button';
                    hapus.setAttribute('aria-label', 'Batalkan ' + a.nama);
                    hapus.append(elemen('i', 'fas fa-xmark'));
                    hapus.addEventListener('click', () => { terpilih.delete(id); gambarTerpilih(); });
                    li.append(elemen('i', 'fas fa-circle-check text-primary'), teks, hapus);
                    terpilihEl.append(li);

                    const input = elemen('input');
                    input.type = 'hidden';
                    input.name = 'anggota[]';
                    input.value = id;
                    form.append(input);
                });
                document.getElementById('pesertaTerpilihBungkus').classList.toggle('hidden', terpilih.size === 0);
                document.getElementById('pesertaJumlah').textContent = terpilih.size ? '(' + terpilih.size + ')' : '';
                perbaruiSlot();
            }

            function tampilHasil(isi) {
                hasil.replaceChildren(...isi);
                hasil.classList.toggle('hidden', isi.length === 0);
            }

            function pesan(teks, kelas = 'text-slate-400') {
                return elemen('li', 'px-4 py-4 text-center text-sm ' + kelas, teks);
            }

            function muat() {
                const kata = cari.value.trim();
                if (kata === '') return tampilHasil([]);

                const nomor = ++permintaan;
                const params = new URLSearchParams({ cari: kata });
                if (jemaatId) params.set('jemaat_id', jemaatId);

                fetch(urlAnggota + '?' + params, { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(function(data) {
                        // Abaikan jawaban lama kalau pengguna sudah mengetik lagi.
                        if (nomor !== permintaan) return;
                        const baru = data.filter(a => !terpilih.has(a.id)).slice(0, 6);
                        if (!baru.length) return tampilHasil([pesan('Nama tidak ditemukan di data anggota.')]);

                        const penuhSlot = terpilih.size >= batas();
                        tampilHasil(baru.map(function(a) {
                            const tidakBisa = !!a.sudah || penuhSlot;
                            const tombol = elemen('button', 'flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition ' + (tidakBisa ? 'cursor-not-allowed bg-slate-50 opacity-60' : 'hover:bg-blue-50/60'));
                            tombol.type = 'button';
                            tombol.disabled = tidakBisa;
                            const teks = elemen('span', 'min-w-0 flex-1');
                            teks.append(elemen('span', 'block font-semibold text-slate-800', a.nama), elemen('span', 'block text-xs text-slate-500', a.keterangan || ''));
                            tombol.append(teks);
                            tombol.append(a.sudah
                                ? elemen('span', 'flex-none rounded-md bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-600', 'Sudah ' + a.sudah)
                                : elemen('span', 'flex-none text-xs font-semibold text-primary', penuhSlot ? 'Slot penuh' : '+ Pilih'));
                            tombol.addEventListener('click', function() {
                                terpilih.set(a.id, a);
                                cari.value = '';
                                tampilHasil([]);
                                gambarTerpilih();
                                cari.focus();
                            });
                            const li = elemen('li');
                            li.append(tombol);
                            return li;
                        }));
                    })
                    .catch(() => tampilHasil([pesan('Gagal memuat data anggota.', 'text-red-600')]));
            }

            cari.addEventListener('input', function() {
                clearTimeout(tunda);
                tunda = setTimeout(muat, 250);
            });
            cari.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());
            form.querySelectorAll('[data-peran-utusan]').forEach(r => r.addEventListener('change', () => { perbaruiSlot(); muat(); }));

            // Data tombol dibaca dari atribut yang sudah di-escape. Nama seperti Baji' Ma'rumpa memuat tanda petik.
            document.addEventListener('click', function(event) {
                const tombol = event.target.closest('[data-tambah-peserta]');
                if (tombol) bukaTambahPeserta(JSON.parse(tombol.dataset.tambahPeserta));
            });

            window.bukaTambahPeserta = function(opsi) {
                terpilih = new Map();
                cari.value = '';
                tampilHasil([]);
                form.querySelector('textarea[name=nama]').value = '';

                // Pemilihan jemaat memakai peran tetap. Pemilihan klasis memilih peran utusan dari tombol.
                document.getElementById('pesertaPeranTetap').disabled = klasis;
                document.getElementById('pesertaPeranTetap').value = 'pemilih';

                if (klasis) {
                    sisaPenuh = opsi.sisa;
                    jemaatId = opsi.jemaat_id;
                    form.querySelectorAll('[data-peran-utusan]').forEach(function(r) {
                        r.disabled = r.value === 'utusan_penuh' && sisaPenuh <= 0;
                        r.checked = r.value === (sisaPenuh > 0 ? 'utusan_penuh' : 'utusan_cadangan');
                    });
                    form.querySelector('[data-ket-penuh]').textContent = sisaPenuh > 0 ? 'Memilih dan bisa dicalonkan' : `Sudah ${maksPenuh}/${maksPenuh}`;
                } else {
                    jemaatId = null;
                }

                document.getElementById('pesertaJemaatId').value = jemaatId || '';
                document.getElementById('pesertaJudul').textContent = klasis ? 'Tambah utusan' : 'Pilih pemilih';
                document.getElementById('pesertaSub').textContent = opsi.jemaat ? 'Jemaat ' + opsi.jemaat : 'Cari dari data anggota jemaat.';
                gambarTerpilih();

                showModal('modalPeserta');
                setTimeout(() => cari.focus(), 150);
            };
        })();
    </script>
@endpush
