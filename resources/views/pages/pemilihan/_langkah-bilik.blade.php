{{-- Penanda langkah di layar bilik suara. Butuh: $langkah (1 kode, 2 pilih, 3 selesai). --}}
<ol class="flex items-center justify-center gap-2 text-[11px] font-semibold" aria-label="Langkah memilih">
    @foreach ([1 => 'Kode', 2 => 'Pilih', 3 => 'Selesai'] as $nomor => $label)
        <li class="flex items-center gap-2">
            <span @class([
                'flex items-center gap-1.5 rounded-full px-2.5 py-1',
                'bg-primary text-white' => $nomor === $langkah,
                'bg-blue-50 text-primary' => $nomor < $langkah,
                'bg-slate-100 text-slate-400' => $nomor > $langkah,
            ]) @if ($nomor === $langkah) aria-current="step" @endif>
                @if ($nomor < $langkah)
                    <i class="fas fa-check text-[9px]" aria-hidden="true"></i>
                @else
                    <span>{{ $nomor }}</span>
                @endif
                {{ $label }}
            </span>
            @unless ($loop->last)
                <span class="h-px w-4 bg-slate-200" aria-hidden="true"></span>
            @endunless
        </li>
    @endforeach
</ol>
