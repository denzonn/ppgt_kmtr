@extends('layouts.publik')

@section('title', 'PPGT Klasis Makassar Timur')

@section('isi')
    @php
        $angka = [
            ['fa-users', 'bg-blue-500/20 text-blue-300', 'Anggota aktif', number_format($anggotaAktif, 0, ',', '.'), ''],
            ['fa-church', 'bg-amber-400/20 text-amber-300', 'Jemaat', $jemaats->count(), 'jemaat'],
            ['fa-layer-group', 'bg-indigo-400/20 text-indigo-200', 'Bidang pelayanan', $bidangs->count(), ''],
            ['fa-seedling', 'bg-emerald-400/20 text-emerald-300', 'Berdiri sejak', 1962, ''],
        ];
        $misi = [
            ['fa-people-group', 'bg-blue-50 text-blue-600', 'text-blue-600', 'Bersekutu', 'Koinonia', 'Menjalin kebersamaan dalam persekutuan sebagai satu tubuh Kristus.'],
            ['fa-bullhorn', 'bg-orange-50 text-orange-500', 'text-orange-500', 'Bersaksi', 'Marturia', 'Memberitakan kabar baik dan kebaikan Tuhan bagi sesama.'],
            ['fa-hand-holding-heart', 'bg-emerald-50 text-emerald-600', 'text-emerald-600', 'Melayani', 'Diakonia', 'Mewujudkan kasih dan pelayanan nyata kepada sesama.'],
        ];
        $maknaLogo = [
            ['bg-blue-600', 'Lingkaran', 'Lingkaran luar melambangkan persekutuan manusia dengan sesamanya. Lingkaran dalam melambangkan persekutuan manusia dengan Allah.'],
            ['bg-primary', 'Salib', 'Pelayanan PPGT selalu berpusat pada salib Kristus, dengan komitmen pelayan yang siap berkorban, siap menderita, dan siap menjadi hamba yang melayani.'],
            ['bg-red-600', 'Rumah Toraja (Tongkonan)', 'Menunjukkan kelahiran PPGT dari tengah orang Toraja. Bersama salib, rumah ini menembus lingkaran dalam sebagai tanda PPGT yang inklusif dan siap bersesama tanpa memandang latar belakang.'],
            ['bg-emerald-500', 'Daun kelapa muda', 'Kuncup yang siap mekar menandakan pemuda dengan jiwa idealisme yang tinggi. Jumlahnya 12, sebagai simbol 12 murid Tuhan Yesus dan 12 suku Israel.'],
            ['bg-slate-950', 'Alkitab', 'Dasar pelayanan PPGT adalah Alkitab, firman Allah yang hidup. Tulisan 1 Kor. 3:11 di atasnya adalah dasar berdirinya Gereja Toraja.'],
            ['bg-cyan-500', 'Tiga garis', 'Garis bergelombang di kiri dan kanan salib melambangkan Tri Panggilan Gereja: Marturia, Koinonia, dan Diakonia.'],
        ];
        $warnaLogo = [
            ['bg-blue-600', 'Biru', 'Semangat militansi dan ketenangan'],
            ['bg-slate-950', 'Hitam', 'Keagungan dan penaklukan kegelapan'],
            ['bg-emerald-500', 'Hijau muda', 'Pengharapan dan hidup baru'],
            ['bg-amber-400', 'Kuning perak', 'Kematangan, kebijaksanaan, keabadian, dan sukacita'],
            ['border-2 border-blue-300 bg-white', 'Putih', 'Kemurnian pelayanan dan kesucian persekutuan'],
        ];
        $namaBulan = fn ($bulan, $format = 'F') => now()->startOfYear()->month($bulan)->translatedFormat($format);
        $bidangUtama = $bidangs->sortByDesc(fn ($b) => $b->programKerjas->count())->first();
        $bidangUtama = $bidangUtama?->programKerjas->isNotEmpty() ? $bidangUtama : null;
    @endphp


        <section id="beranda" class="beranda-gelap text-white">
            <div class="wadah-publik">
                <div class="grid items-center gap-12 py-16 lg:grid-cols-12 lg:py-24">
                    <div class="lg:col-span-8">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-blue-50">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                            Pengurus Klasis Makassar Timur{{ $periode ? ' Periode '.$periode->nama : '' }}
                        </p>
                        <h1 class="mt-6 text-4xl font-extrabold leading-[1.12] tracking-tight sm:text-5xl lg:text-[4rem] 2xl:text-[4.75rem]">
                            Bersekutu, bersaksi,<br>
                            <span class="beranda-gradasi">dan melayani.</span>
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-blue-100">
                            Pemuda Gereja Toraja yang dipanggil untuk menjadi terang ke mana pun pergi dan di mana pun berada.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="#kegiatan" class="inline-flex items-center gap-3 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-primary shadow-lg shadow-black/20 transition hover:bg-blue-50">
                                <i class="fa-regular fa-calendar" aria-hidden="true"></i> Lihat kegiatan <i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i>
                            </a>
                            <a href="#tentang" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/20">
                                <i class="fa-solid fa-church text-blue-300" aria-hidden="true"></i> Tentang kami
                            </a>
                            <a href="#logo" class="inline-flex items-center gap-2 rounded-xl border border-amber-400/40 bg-amber-400/10 px-5 py-3 text-sm font-semibold text-amber-200 transition hover:bg-amber-400/20">
                                <i class="fa-solid fa-compass text-amber-300" aria-hidden="true"></i> Arti Lambang
                            </a>
                        </div>
                    </div>

                    <div class="flex flex-col items-center lg:col-span-4 lg:items-end">
                        <div class="flex flex-col items-center">
                            <div class="rounded-full bg-white p-4 shadow-[0_0_70px_rgba(147,197,253,.5)]">
                                <div class="rounded-full border-[3px] border-amber-400 p-1.5">
                                    <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" class="h-52 w-52 rounded-full sm:h-60 sm:w-60 2xl:h-80 2xl:w-80">
                                </div>
                            </div>
                            <p class="-mt-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-[#0d1f4f] px-4 py-1.5 text-xs font-semibold text-blue-50">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> 1 Kor. 3:11 · 1962
                            </p>
                        </div>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-4 pb-14 lg:grid-cols-4 lg:gap-6 lg:pb-20">
                    @foreach ($angka as [$ikon, $warna, $label, $nilai, $satuan])
                        <div class="flex items-center gap-4 rounded-2xl border border-blue-300/20 bg-[#10245a]/60 p-4 lg:p-5">
                            <span class="{{ $warna }} flex h-12 w-12 flex-none items-center justify-center rounded-xl text-lg" aria-hidden="true">
                                <i class="fa-solid {{ $ikon }}"></i>
                            </span>
                            <div class="min-w-0">
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-blue-200">{{ $label }}</dt>
                                <dd class="whitespace-nowrap text-2xl font-extrabold lg:text-3xl">
                                    {{ $nilai }}
                                    @if ($satuan)
                                        <span class="text-xs font-medium text-blue-200">{{ $satuan }}</span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        <section id="tentang" class="scroll-mt-20 py-20 lg:py-28">
            <div class="wadah-publik grid gap-12 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-5">
                    <p class="pil-publik"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Tentang kami</p>
                    <h2 class="judul-publik mt-4">Wadah pelayanan dan kaderisasi pemuda Gereja Toraja</h2>
                    <p class="mt-6 leading-8 text-slate-500">
                        PPGT dibentuk pada <strong class="font-semibold text-slate-800">11 Desember 1962</strong> sebagai Organisasi Intra Gerejawi pertama dalam Gereja Toraja. Di Klasis Makassar Timur, PPGT hadir bersama jemaat-jemaat untuk melaksanakan panggilan ini.
                    </p>

                    <figure class="relative mt-8 overflow-hidden rounded-2xl bg-gradient-to-br from-[#0b1f52] to-[#163a8f] p-6 text-white shadow-xl shadow-blue-950/25 sm:p-7">
                        <i class="fa-solid fa-book-open absolute -bottom-4 right-3 text-8xl text-white/10" aria-hidden="true"></i>
                        <p class="relative text-xs font-bold uppercase tracking-wider text-amber-300"><i class="fa-solid fa-bookmark mr-1" aria-hidden="true"></i> Dasar iman pelayanan</p>
                        <blockquote class="beranda-ayat relative mt-4 text-xl italic leading-9">
                            "Karena tidak ada seorang pun yang dapat meletakkan dasar lain dari pada dasar yang telah diletakkan, yaitu Yesus Kristus."
                        </blockquote>
                        <figcaption class="relative mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-white/15 pt-4 text-xs">
                            <span class="font-bold uppercase tracking-wider text-amber-300">1 Korintus 3:11</span>
                            <span class="text-blue-100">Alkitab Terjemahan Baru</span>
                        </figcaption>
                    </figure>
                </div>

                <div class="lg:col-span-7">
                    <div class="rounded-3xl border border-blue-100/60 bg-gradient-to-br from-blue-50 to-white p-6 sm:p-10">
                        <p class="pil-publik border-transparent bg-white"><i class="fa-solid fa-eye" aria-hidden="true"></i> Visi</p>
                        <p class="mt-5 text-2xl font-semibold leading-snug text-slate-900 sm:text-[1.9rem] sm:leading-[1.35]">
                            Terwujudnya anggota PPGT yang memuliakan Tuhan, memberitakan kebaikan-Nya, menjadi berkat bagi manusia dan dunia.
                        </p>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Misi PPGT: panggilan gereja</h3>
                        <span class="text-xs font-semibold text-blue-600">Tiga Pilar Pelayanan</span>
                    </div>
                    <ul class="mt-4 grid gap-4 sm:grid-cols-3">
                        @foreach ($misi as [$ikon, $tile, $warnaIstilah, $judul, $istilah, $isi])
                            <li class="rounded-2xl border border-slate-200 bg-white p-5">
                                <span class="{{ $tile }} flex h-12 w-12 items-center justify-center rounded-xl" aria-hidden="true"><i class="fa-solid {{ $ikon }}"></i></span>
                                <p class="mt-4 font-semibold text-slate-900">{{ $judul }}</p>
                                <p class="{{ $warnaIstilah }} text-[11px] font-bold uppercase tracking-wider">{{ $istilah }}</p>
                                <p class="mt-3 text-sm leading-6 text-slate-500">{{ $isi }}</p>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-blue-100 text-blue-600" aria-hidden="true"><i class="fa-solid fa-flag"></i></span>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-primary">Tujuan</h3>
                            <p class="mt-1 text-sm leading-7 text-slate-600">Mewujudkan warga gereja yang sadar dan bertanggung jawab terhadap tugas dan panggilannya di tengah-tengah gereja, masyarakat, dan alam semesta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="logo" class="scroll-mt-20 bg-[#f5f7fd] py-20 lg:py-28">
            <div class="wadah-publik">
                <p class="pil-publik"><i class="fa-solid fa-shapes" aria-hidden="true"></i> Makna logo &amp; simbol</p>
                <h2 class="judul-publik mt-4">Identitas yang menuntun pelayanan</h2>
                <p class="mt-3 text-slate-500">Setiap garis, simbol, dan warna dalam lambang PPGT memuat arti panggilan pemuda Gereja Toraja.</p>

                <div class="mt-10 grid items-start gap-8 lg:grid-cols-12">
                    <div class="flex flex-col items-center rounded-3xl border border-slate-200 bg-white p-8 text-center lg:sticky lg:top-28 lg:col-span-4">
                        <div class="rounded-full bg-blue-50 p-6">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" loading="lazy" class="h-48 w-48 rounded-full bg-white shadow-lg 2xl:h-64 2xl:w-64">
                        </div>
                        <p class="pil-publik mt-6 normal-case tracking-normal">Lambang Resmi PPGT</p>
                        <p class="mt-2 text-xs text-slate-500">Persekutuan Pemuda Gereja Toraja</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:col-span-8">
                        @foreach ($maknaLogo as [$titik, $bagian, $arti])
                            <article class="rounded-2xl border border-slate-200 bg-white p-6">
                                <h3 class="flex items-center gap-3 font-semibold text-slate-900">
                                    <span class="{{ $titik }} h-2.5 w-2.5 flex-none rounded-full" aria-hidden="true"></span>{{ $bagian }}
                                </h3>
                                <p class="mt-3 text-sm leading-7 text-slate-500">{{ $arti }}</p>
                            </article>
                        @endforeach
                        <article class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50/60 to-white p-6 sm:col-span-2">
                            <h3 class="flex items-center gap-3 font-semibold text-slate-900">
                                <span class="h-2.5 w-2.5 flex-none rounded-full bg-amber-400" aria-hidden="true"></span>Bintang delapan
                            </h3>
                            <p class="mt-3 text-sm leading-7 text-slate-500">PPGT harus menjadi terang ke mana pun pergi dan di mana pun berada.</p>
                        </article>
                    </div>
                </div>

                <div class="mt-10 border-t border-slate-200 pt-8">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Makna warna</h3>
                        <span class="text-xs text-slate-400">5 elemen warna lambang</span>
                    </div>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ($warnaLogo as [$kelas, $warna, $arti])
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <dt class="flex items-center gap-2.5 text-sm font-semibold text-slate-900">
                                    <span class="{{ $kelas }} h-3 w-3 flex-none rounded-full" aria-hidden="true"></span>{{ $warna }}
                                </dt>
                                <dd class="mt-2 text-xs leading-5 text-slate-500">{{ $arti }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </section>

        <section id="bidang" class="scroll-mt-20 py-20 lg:py-28">
            <div class="wadah-publik">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="pil-publik"><i class="fa-solid fa-sitemap" aria-hidden="true"></i> Bidang pelayanan{{ $periode ? ' periode '.$periode->nama : '' }}</p>
                        <h2 class="judul-publik mt-4">Program kerja {{ $tahun }}</h2>
                    </div>
                    <p class="max-w-md text-sm leading-6 text-slate-500">Struktur kerja {{ $bidangs->count() }} bidang pelayanan kepengurusan PPGT Klasis Makassar Timur untuk menjawab kebutuhan jemaat dan kader.</p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($bidangs as $bidang)
                        @php
                            $nomor = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT);
                            $keterangan = config('beranda.bidang.'.str_replace('.', '', $bidang->nama));
                        @endphp

                        @if ($bidangUtama?->is($bidang))
                            <article class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0d2259] to-[#0b1738] p-6 text-white shadow-xl shadow-blue-950/25 sm:p-8 md:col-span-2">
                                <i class="fa-solid fa-book-open absolute -bottom-3 right-4 text-8xl text-white/10" aria-hidden="true"></i>
                                <div class="relative flex flex-wrap items-center justify-between gap-2">
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-300">Bidang {{ $nomor }} · Utama</span>
                                    <span class="rounded-full border border-amber-400/50 bg-amber-400/10 px-3 py-1 text-[11px] font-semibold text-amber-300">Program Prioritas</span>
                                </div>
                                <h3 class="relative mt-5 text-2xl font-bold">{{ $bidang->nama }}</h3>
                                @if ($keterangan)
                                    <p class="relative mt-2 text-sm leading-6 text-blue-100">{{ $keterangan }}</p>
                                @endif
                                <p class="relative mt-6 text-[11px] font-bold uppercase tracking-wider text-blue-200">Program terdaftar:</p>
                                <ul class="relative mt-3 flex flex-wrap gap-3">
                                    @foreach ($bidang->programKerjas as $program)
                                        <li class="{{ $loop->last ? 'bg-amber-400 text-slate-900' : 'border border-white/15 bg-white/10 text-white' }} inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold">
                                            <i class="fa-solid fa-book-bible text-xs" aria-hidden="true"></i> {{ $program->nama }}
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @else
                            <article class="flex flex-col rounded-3xl border border-slate-200 bg-slate-50/70 p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="pil-publik py-0.5">Bidang {{ $nomor }}</span>
                                    @if ($bidang->programKerjas->isNotEmpty())
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-slate-200/70 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">Tahap Penyusunan</span>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-xl font-semibold text-slate-900">{{ $bidang->nama }}</h3>
                                @if ($keterangan)
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $keterangan }}</p>
                                @endif
                                <div class="mt-auto">
                                    <p class="mt-5 border-t border-slate-200 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Program terdaftar</p>
                                    @forelse ($bidang->programKerjas as $program)
                                        <p class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                                            <i class="fa-regular fa-circle-check text-blue-600" aria-hidden="true"></i> {{ $program->nama }}
                                        </p>
                                    @empty
                                        <p class="mt-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-400">Program belum dicantumkan</p>
                                    @endforelse
                                </div>
                            </article>
                        @endif
                    @empty
                        <p class="text-slate-500">Belum ada bidang pelayanan.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="kegiatan" class="scroll-mt-20 bg-[#f5f7fd] py-20 lg:py-28">
            <div class="wadah-publik grid items-start gap-8 lg:grid-cols-12">
                <div class="lg:sticky lg:top-28 lg:col-span-4">
                    <p class="pil-publik"><i class="fa-regular fa-calendar" aria-hidden="true"></i> Kegiatan</p>
                    <h2 class="judul-publik mt-4">Yang kami kerjakan tahun {{ $tahun }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-500">Dokumentasi program dan kegiatan pemuda di lingkup Klasis Makassar Timur untuk memperlengkapi jemaat dan kader.</p>

                    @if ($kegiatans->contains('nama', 'Penyusunan & Pendistribusian Bahan Khotbah PPGT KMTr'))
                        <aside class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500"><i class="fa-regular fa-lightbulb mr-1.5 text-amber-500" aria-hidden="true"></i> Publikasi khusus</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Buku bahan khotbah <strong class="font-semibold text-slate-800">"Suluh"</strong> yang artinya Obor, sebagai penerang perjalanan kehidupan pemuda dalam masa mudanya.</p>
                        </aside>
                    @endif
                </div>

                <div class="space-y-6 lg:col-span-8">
                    @forelse ($kegiatans as $kegiatan)
                        @php $rincian = config('beranda.kegiatan')[$kegiatan->nama] ?? null; @endphp

                        <article class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
                            <div class="flex gap-4">
                                <div class="flex h-16 w-16 flex-none flex-col items-center justify-center rounded-2xl bg-[#0b1738] text-white">
                                    <span class="text-[11px] font-bold uppercase text-amber-300">{{ $namaBulan($kegiatan->bulan[0], 'M') }}</span>
                                    <span class="text-base font-extrabold leading-tight">{{ $kegiatan->programKerja->tahun }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="pil-publik py-0.5 normal-case tracking-normal"><span class="h-1.5 w-1.5 rounded-full bg-blue-600" aria-hidden="true"></span>{{ $kegiatan->programKerja->bidang->nama }}</p>
                                    <h3 class="mt-2 text-xl font-bold leading-snug text-slate-900 sm:text-2xl">{{ $kegiatan->nama }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>
                                        {{ $rincian['waktu'] ?? collect($kegiatan->bulan)->map(fn ($bulan) => $namaBulan($bulan))->join(', ', ' & ') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 space-y-4 border-t border-slate-100 pt-6 text-sm leading-7 text-slate-600">
                                @if ($rincian)
                                    @foreach (array_filter($rincian, 'is_int', ARRAY_FILTER_USE_KEY) as $blok)
                                        @switch($blok['jenis'])
                                            @case('kotak')
                                                <div class="{{ $blok['latar'] ?? 'bg-slate-50' }} rounded-2xl p-4">
                                                    <p class="font-semibold text-slate-800">{{ $blok['judul'] }}</p>
                                                    <p class="mt-1">{{ $blok['isi'] }}</p>
                                                </div>
                                                @break
                                            @case('catatan')
                                                <div class="flex gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-orange-950">
                                                    <i class="fa-solid fa-book-open mt-1.5 text-orange-500" aria-hidden="true"></i>
                                                    <p>{{ $blok['isi'] }}</p>
                                                </div>
                                                @break
                                            @case('pemateri')
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    @foreach ($blok['orang'] as [$nama, $materi])
                                                        <div class="rounded-2xl bg-slate-50 p-4">
                                                            <p class="font-semibold text-slate-800">{{ $nama }}</p>
                                                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $materi }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @break
                                            @case('kecil')
                                                <p class="text-xs text-slate-500">{{ $blok['isi'] }}</p>
                                                @break
                                            @default
                                                <p>{{ $blok['isi'] }}</p>
                                        @endswitch
                                    @endforeach
                                @else
                                    <p class="whitespace-pre-line">{{ $kegiatan->deskripsi }}</p>
                                @endif

                                @if ($kegiatan->foto)
                                    <img src="{{ $kegiatan->foto }}" alt="Dokumentasi {{ $kegiatan->nama }}" loading="lazy" class="aspect-video w-full rounded-2xl object-cover">
                                @endif

                                @if ($kegiatan->link)
                                    <a href="{{ $kegiatan->link }}" target="_blank" rel="noopener" class="inline-block font-semibold text-primary hover:text-blue-600">
                                        Lihat dokumentasi <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-xs" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-3xl border border-slate-200 bg-white p-8 text-center text-slate-500">Belum ada kegiatan untuk tahun ini.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="keanggotaan" class="scroll-mt-20 py-20 lg:py-24">
            <div class="wadah-publik grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <p class="pil-publik"><i class="fa-solid fa-user-group" aria-hidden="true"></i> Keanggotaan</p>
                    <h2 class="judul-publik mt-4">Siapa anggota PPGT?</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">Keanggotaan PPGT terbuka bagi segenap pemuda Gereja Toraja yang ingin bertumbuh dan berkarya dalam persekutuan Kristus.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:col-span-8">
                    <article class="rounded-3xl border border-blue-200 bg-gradient-to-br from-white to-blue-50 p-6 sm:p-8">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 text-white" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
                        <h3 class="mt-5 text-[11px] font-bold uppercase tracking-wider text-blue-700">Anggota Biasa</h3>
                        <p class="mt-1 text-3xl font-bold text-slate-900">15–35 tahun</p>
                        <p class="mt-3 text-sm leading-7 text-slate-500">Semua anggota Gereja Toraja dalam rentang umur ini otomatis menjadi Anggota Biasa PPGT.</p>
                    </article>

                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 sm:p-8">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
                        <h3 class="mt-5 text-[11px] font-bold uppercase tracking-wider text-orange-600">Anggota Luar Biasa</h3>
                        <p class="mt-1 text-3xl font-bold text-slate-900">Setia dan loyal</p>
                        <p class="mt-3 text-sm leading-7 text-slate-500">Anggota Gereja Toraja di luar rentang umur tersebut yang setia dan loyal kepada PPGT, serta pemuda lainnya yang bersedia menerima AD/ART PPGT.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="jemaat" class="beranda-gelap scroll-mt-20 py-16 text-white lg:py-20">
            <div class="wadah-publik">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="max-w-xl">
                        <p class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-300"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Struktur wilayah</p>
                        <h2 class="mt-4 text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{{ $jemaats->count() }} jemaat, satu persekutuan</h2>
                        <p class="mt-3 text-sm leading-7 text-blue-100">Klasis Makassar Timur menaungi {{ $jemaats->count() }} persekutuan jemaat yang bergandengan tangan dalam satu komitmen iman.</p>
                    </div>
                    <p class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium text-blue-50">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> Satu koordinasi Klasis Makassar Timur
                    </p>
                </div>

                <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($jemaats as $nama)
                        <li class="flex items-center gap-4 rounded-2xl border border-blue-300/20 bg-[#10245a]/60 p-5">
                            <span class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-amber-400/15 text-amber-300" aria-hidden="true"><i class="fa-solid fa-church"></i></span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Jemaat {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <p class="font-bold">{{ $nama }}</p>
                                <p class="mt-0.5 text-xs text-blue-200"><i class="fa-solid fa-location-dot mr-1" aria-hidden="true"></i>Klasis Makassar Timur</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-blue-100">Belum ada data jemaat.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        @if ($artikels->isNotEmpty())
            <section id="artikel" class="scroll-mt-20 bg-[#f5f7fd] py-20 lg:py-24">
                <div class="wadah-publik">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="pil-publik"><i class="fa-regular fa-newspaper" aria-hidden="true"></i> Artikel</p>
                            <h2 class="judul-publik mt-4">Artikel terbaru</h2>
                            <p class="mt-3 text-sm text-slate-500">Tulisan dari jemaat-jemaat di Klasis Makassar Timur.</p>
                        </div>
                        <a href="{{ route('artikel.daftar') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-primary transition hover:border-primary">
                            Lihat semua artikel <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($artikels as $artikel)
                            @include('includes.publik.artikel-kartu')
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section id="layanan" class="scroll-mt-20 py-20 lg:py-24">
            <div class="wadah-publik">
                <p class="pil-publik"><i class="fa-solid fa-grip" aria-hidden="true"></i> Layanan</p>
                <h2 class="judul-publik mt-4">Yang bisa Anda akses</h2>
                <p class="mt-3 text-sm text-slate-500">Pusat fasilitas dan berkas administrasi organisasi PPGT Klasis Makassar Timur untuk jemaat dan pengurus.</p>

                <div class="mt-10 grid gap-6 md:grid-cols-2">
                    @foreach ([
                        [route('peminjaman'), 'fa-box-open', 'bg-blue-700', 'Peminjaman inventaris', 'Online', 'bg-emerald-50 text-emerald-700', 'Ajukan peminjaman barang inventaris PPGT KMTr.'],
                        [route('linktree'), 'fa-folder-open', 'bg-amber-500', 'Tautan & dokumen', 'Arsip', 'bg-blue-50 text-primary', 'Buka juknis, peraturan, logo, dan tautan penting lainnya.'],
                    ] as [$tujuan, $ikon, $tile, $judul, $label, $warnaLabel, $isi])
                        <a href="{{ $tujuan }}" class="group flex items-center gap-5 rounded-3xl border border-slate-200 bg-slate-50 p-6 transition hover:border-primary sm:p-8">
                            <span class="{{ $tile }} flex h-14 w-14 flex-none items-center justify-center rounded-2xl text-xl text-white" aria-hidden="true">
                                <i class="fa-solid {{ $ikon }}"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2 text-lg font-semibold text-slate-900">
                                    {{ $judul }}
                                    <span class="{{ $warnaLabel }} rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase">{{ $label }}</span>
                                </span>
                                <span class="mt-1 block text-sm leading-6 text-slate-500">{{ $isi }}</span>
                            </span>
                            <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full border border-slate-200 bg-white text-xs text-slate-400 transition group-hover:bg-primary group-hover:text-white" aria-hidden="true">
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

@endsection
