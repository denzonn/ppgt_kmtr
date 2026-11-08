{{-- Daftar centang calon. Butuh: $opsi (peserta), $tercentang (id peserta). Opsional: $idSudahTerpilih, $suara (id peserta => jumlah suara). --}}
@php
    $idSudahTerpilih ??= [];
    $suara ??= [];
@endphp
<div data-daftar-calon>
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <span class="text-sm font-semibold text-slate-700">Calon <span class="font-normal text-slate-500">(<span data-jumlah-calon>{{ count($tercentang) }}</span> dipilih)</span></span>
        <span class="flex gap-3 text-xs font-semibold text-primary">
            <button type="button" data-centang="1" class="hover:underline">Pilih semua</button>
            <button type="button" data-centang="0" class="hover:underline">Kosongkan</button>
        </span>
    </div>
    <div class="grid max-h-80 gap-2 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50/60 p-2 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($opsi as $orang)
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-transparent bg-white px-3 py-2.5 text-sm transition hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-blue-50/60">
                <input type="checkbox" name="calon[]" value="{{ $orang->id }}" @checked(in_array($orang->id, $tercentang)) class="rounded border-slate-300 text-primary focus:ring-primary">
                <span class="min-w-0 flex-1">
                    <span class="block font-semibold text-slate-800">{{ $orang->nama }}</span>
                    <span class="block text-xs text-slate-500">
                        {{ $orang->jemaat?->nama ?? 'Klasis' }}{{ $orang->peran === 'demisioner' ? ' · Demisioner' : '' }}{{ in_array($orang->id, $idSudahTerpilih) ? ' · Sudah terpilih di jabatan lain' : '' }}
                    </span>
                </span>
                @isset($suara[$orang->id])
                    <span class="flex-none rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">{{ $suara[$orang->id] }} suara</span>
                @endisset
            </label>
        @endforeach
    </div>
</div>
