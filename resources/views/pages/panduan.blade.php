@extends('layouts.app')

@section('title', 'Panduan')
@section('page', 'panduan')
@section('page_title', 'Panduan')

@section('content')

    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-slate-800">{{ $isi['judul'] }}</h2>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            {{-- Di ponsel daftar topik diringkas menjadi pilihan supaya isi tidak terdorong jauh ke bawah. --}}
            <div class="lg:hidden">
                <label for="pilihTopik" class="mb-2 block text-sm font-semibold text-slate-700">Pilih topik</label>
                <select id="pilihTopik" onchange="location.href = this.value"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10">
                    @foreach ($daftar->groupBy('grup') as $grup => $_)
                        <optgroup label="{{ $grup }}">
                            @foreach ($daftar->where('grup', $grup) as $kunci => $item)
                                <option value="{{ route('panduan', $kunci) }}" @selected($kunci === $topik)>{{ $item['judul'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <nav aria-label="Topik panduan" class="hidden w-60 shrink-0 space-y-5 lg:sticky lg:top-24 lg:block">
                @foreach ($daftar->groupBy('grup') as $grup => $_)
                    <div>
                        <p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $grup }}</p>
                        <div class="space-y-0.5">
                            @foreach ($daftar->where('grup', $grup) as $kunci => $item)
                                <a href="{{ route('panduan', $kunci) }}" @if ($kunci === $topik) aria-current="page" @endif
                                    class="block rounded-lg px-3 py-2 text-sm transition {{ $kunci === $topik ? 'bg-primary/10 font-semibold text-primary' : 'text-slate-600 hover:bg-white' }}">
                                    {{ $item['judul'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <article class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                <p class="leading-relaxed text-slate-600">{{ $isi['pengantar'] }}</p>

                @foreach ($isi['bagian'] as $bagian)
                    <section class="mt-8">
                        <h3 class="text-lg font-bold text-slate-800">{{ $bagian['judul'] }}</h3>

                        @isset($bagian['gambar'])
                            <a href="{{ asset('images/panduan/' . $bagian['gambar'] . '.webp') }}" target="_blank" rel="noopener"
                                aria-label="Buka gambar ukuran penuh: {{ $bagian['alt'] }}"
                                class="mt-3 block overflow-hidden rounded-xl border border-slate-200 transition hover:border-primary/40">
                                <img src="{{ asset('images/panduan/' . $bagian['gambar'] . '.webp') }}" alt="{{ $bagian['alt'] }}"
                                    width="1440" height="900" loading="lazy" class="h-auto w-full">
                            </a>
                        @endisset

                        @isset($bagian['langkah'])
                            <ol class="mt-4 list-decimal space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
                                @foreach ($bagian['langkah'] as $langkah)
                                    <li>{!! Str::inlineMarkdown($langkah) !!}</li>
                                @endforeach
                            </ol>
                        @endisset

                        @isset($bagian['teks'])
                            <p class="mt-3 text-sm leading-relaxed text-slate-700">{!! Str::inlineMarkdown($bagian['teks']) !!}</p>
                        @endisset

                        @isset($bagian['catatan'])
                            <p class="mt-3 rounded-xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-900">{!! Str::inlineMarkdown($bagian['catatan']) !!}</p>
                        @endisset
                    </section>
                @endforeach

                <div class="mt-10 flex flex-wrap justify-between gap-3 border-t border-slate-100 pt-5 text-sm font-semibold">
                    @if ($sebelumnya)
                        <a href="{{ route('panduan', $sebelumnya) }}" class="text-primary hover:underline"><i class="fa-solid fa-arrow-left mr-1.5 text-xs"></i>{{ $daftar[$sebelumnya]['judul'] }}</a>
                    @else
                        <span></span>
                    @endif
                    @if ($berikutnya)
                        <a href="{{ route('panduan', $berikutnya) }}" class="text-primary hover:underline">{{ $daftar[$berikutnya]['judul'] }}<i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i></a>
                    @endif
                </div>
            </article>
        </div>
    </div>

@endsection
