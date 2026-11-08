{{-- Satu baris peserta. Butuh: $orang, $pemilihan, $rute. --}}
@php
    $warnaPeran = [
        'utusan_penuh' => 'bg-primary text-white',
        'utusan_cadangan' => 'bg-slate-100 text-slate-600',
        'demisioner' => 'bg-blue-50 text-primary',
        'pemilih' => 'bg-blue-50 text-primary',
    ];
    $inisial = collect(explode(' ', $orang->nama))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->join('');
@endphp
<li class="group/baris flex items-center gap-3 px-4 py-3 transition hover:bg-slate-50/70">
    <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full {{ $orang->bisaMemilih() ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-500' }} text-xs font-bold uppercase" aria-hidden="true">{{ $inisial }}</span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-slate-800">{{ $orang->nama }}</p>
        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
            <span class="rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $warnaPeran[$orang->peran] }}">{{ \App\Models\PemilihanPeserta::PERAN[$orang->peran] }}</span>
            @if ($orang->anggota?->no_anggota)
                <span>{{ $orang->anggota->no_anggota }}</span>
            @elseif (! $orang->data_anggota_id)
                <span title="Diketik manual, tidak terhubung ke data anggota">Manual</span>
            @endif
        </p>
    </div>
    @if ($orang->bisaMemilih())
        <span class="hidden flex-none rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-bold tracking-widest text-slate-700 sm:inline" title="Kode kartu">{{ $orang->kode }}</span>
    @endif

    <details class="relative flex-none">
        <summary class="flex h-8 w-8 cursor-pointer list-none items-center justify-center rounded-lg text-slate-400 transition hover:bg-white hover:text-primary hover:shadow-sm" aria-label="Ubah {{ $orang->nama }}">
            <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
        </summary>
        <div class="absolute right-0 z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
            <form method="POST" action="{{ route($rute . '.peserta.update', [$pemilihan->id, $orang->id]) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <label class="block text-xs font-semibold text-slate-500">Nama
                    <input type="text" name="nama" value="{{ $orang->nama }}" required maxlength="255" class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                </label>
                <label class="block text-xs font-semibold text-slate-500">Peran
                    <select name="peran" class="mt-1 w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                        @foreach ($pemilihan->pilihanPeran() as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($orang->peran === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">Simpan</button>
            </form>
            <div class="mt-3 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                @if ($orang->bisaMemilih())
                    <a href="{{ route($rute . '.kartu', [$pemilihan->id, 'peserta' => $orang->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:border-primary hover:text-primary"><i class="fas fa-file-pdf" aria-hidden="true"></i> Kartu</a>
                    <form method="POST" action="{{ route($rute . '.peserta.kode', [$pemilihan->id, $orang->id]) }}"
                        data-konfirmasi="Ganti kode {{ $orang->nama }}?" data-konfirmasi-teks="Kartu lama langsung tidak berlaku. Cetak ulang kartunya setelah ini." data-konfirmasi-ok="Ya, ganti" data-konfirmasi-ikon="fa-key">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:border-primary hover:text-primary"><i class="fas fa-key" aria-hidden="true"></i> Ganti kode</button>
                    </form>
                @endif
                <form method="POST" action="{{ route($rute . '.peserta.destroy', [$pemilihan->id, $orang->id]) }}" class="ml-auto"
                    data-konfirmasi="Hapus {{ $orang->nama }}?" data-konfirmasi-teks="Peserta ini dihapus dari kegiatan." data-konfirmasi-ok="Ya, hapus" data-bahaya>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50"><i class="fas fa-trash" aria-hidden="true"></i> Hapus</button>
                </form>
            </div>
        </div>
    </details>
</li>
