@extends('layouts.auth')

@section('title', 'Official Website PPGT KMTr')

@section('content')
    <div class="bg-slate-50">

        {{-- HERO --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-blue-700 to-cyan-600"></div>

            <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute left-0 bottom-0 h-72 w-72 rounded-full bg-cyan-300/10 blur-3xl"></div>

            <div class="relative max-w-7xl mx-auto px-6 py-24">

                <div class="grid lg:grid-cols-2 gap-12 items-center">

                    <div class="text-white">

                        <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-2 text-sm backdrop-blur">
                            Official Website
                        </span>

                        <h1 class="mt-6 text-5xl font-extrabold leading-tight">
                            Persekutuan Pemuda
                            <span class="text-yellow-300">
                                Gereja Toraja
                            </span>
                        </h1>

                        <p class="mt-6 text-lg text-blue-100 leading-8">
                            Bertumbuh dalam iman, melayani dengan kasih,
                            dan menjadi terang bagi generasi muda Gereja Toraja.
                        </p>

                        <div class="mt-10 flex flex-wrap gap-4">

                            <a href="#tentang"
                                class="rounded-xl bg-white text-blue-700 px-6 py-3 font-semibold hover:bg-blue-50 transition">
                                Tentang Kami
                            </a>

                            <a href="#kegiatan"
                                class="rounded-xl border border-white text-white px-6 py-3 hover:bg-white hover:text-blue-700 transition">
                                Lihat Kegiatan
                            </a>

                        </div>

                    </div>

                    <div class="hidden lg:flex justify-center">

                        <div
                            class="w-[420px] h-[420px] rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-48 h-48 text-white opacity-80" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 2l3 6 7 1-5 5 1 8-6-3-6 3 1-8-5-5 7-1z" />

                            </svg>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        {{-- STATS --}}
        <section class="-mt-12 relative z-20">

            <div class="max-w-6xl mx-auto px-6">

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">

                    @foreach ([['120+', 'Anggota'], ['15', 'Wilayah'], ['35+', 'Program'], ['10', 'Komisi']] as $item)
                        <div class="bg-white rounded-2xl shadow-lg p-6 text-center hover:-translate-y-2 transition">

                            <h2 class="text-3xl font-bold text-blue-700">
                                {{ $item[0] }}
                            </h2>

                            <p class="text-slate-500 mt-2">
                                {{ $item[1] }}
                            </p>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

        {{-- TENTANG --}}
        <section id="tentang" class="py-24">

            <div class="max-w-6xl mx-auto px-6">

                <div class="text-center">

                    <h2 class="text-4xl font-bold text-slate-800">
                        Tentang PPGT
                    </h2>

                    <p class="mt-5 text-slate-600 max-w-3xl mx-auto leading-8">

                        Persekutuan Pemuda Gereja Toraja merupakan wadah pembinaan,
                        pelayanan, dan pengembangan karakter pemuda agar bertumbuh
                        dalam iman, kasih, dan pengabdian kepada Tuhan serta sesama.

                    </p>

                </div>

            </div>

        </section>

        {{-- KEGIATAN --}}
        <section id="kegiatan" class="bg-white py-20">

            <div class="max-w-6xl mx-auto px-6">

                <div class="flex justify-between items-center mb-10">

                    <h2 class="text-3xl font-bold">
                        Program Pelayanan
                    </h2>

                    <a href="#" class="text-blue-600 font-semibold">
                        Lihat Semua →
                    </a>

                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                    @foreach (['Ibadah Pemuda', 'Retreat', 'Bakti Sosial', 'Pelatihan Kepemimpinan', 'Olahraga', 'Seminar'] as $item)
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-7 hover:shadow-xl hover:-translate-y-2 transition">

                            <div
                                class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 mb-5">

                                ★

                            </div>

                            <h3 class="text-xl font-bold">

                                {{ $item }}

                            </h3>

                            <p class="mt-3 text-slate-500 leading-7">

                                Kegiatan yang dirancang untuk membangun iman,
                                karakter, dan kebersamaan pemuda.

                            </p>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

        {{-- BERITA --}}
        <section class="py-24">

            <div class="max-w-6xl mx-auto px-6">

                <div class="text-center mb-12">

                    <h2 class="text-4xl font-bold">
                        Berita Terbaru
                    </h2>

                </div>

                <div class="grid lg:grid-cols-3 gap-8">

                    @for ($i = 1; $i <= 3; $i++)
                        <div class="overflow-hidden rounded-2xl bg-white shadow hover:shadow-xl transition">

                            <div class="h-52 bg-slate-300"></div>

                            <div class="p-6">

                                <span class="text-sm text-blue-600">
                                    20 Juli 2026
                                </span>

                                <h3 class="mt-3 text-xl font-bold">

                                    Judul Berita PPGT

                                </h3>

                                <p class="mt-3 text-slate-500">

                                    Ringkasan berita organisasi dapat ditampilkan di sini.

                                </p>

                            </div>

                        </div>
                    @endfor

                </div>

            </div>

        </section>

        {{-- GALERI --}}
        <section class="bg-white py-24">

            <div class="max-w-6xl mx-auto px-6">

                <h2 class="text-4xl font-bold text-center mb-12">
                    Galeri
                </h2>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                    @for ($i = 1; $i <= 8; $i++)
                        <div class="aspect-square rounded-2xl bg-slate-300"></div>
                    @endfor

                </div>

            </div>

        </section>

        {{-- FOOTER --}}
        <footer class="bg-slate-900 text-slate-300">

            <div class="max-w-6xl mx-auto px-6 py-16">

                <div class="grid lg:grid-cols-3 gap-10">

                    <div>

                        <h3 class="text-2xl font-bold text-white">
                            PPGT KMTr
                        </h3>

                        <p class="mt-5 leading-8">

                            Menjadi komunitas pemuda yang bertumbuh dalam Kristus,
                            melayani dengan kasih, dan menjadi berkat bagi sesama.

                        </p>

                    </div>

                    <div>

                        <h4 class="text-white font-semibold mb-5">
                            Menu
                        </h4>

                        <div class="space-y-3">

                            <a href="#" class="block hover:text-white">Beranda</a>
                            <a href="#" class="block hover:text-white">Profil</a>
                            <a href="#" class="block hover:text-white">Berita</a>
                            <a href="#" class="block hover:text-white">Galeri</a>

                        </div>

                    </div>

                    <div>

                        <h4 class="text-white font-semibold mb-5">
                            Kontak
                        </h4>

                        <p>Email : info@ppgt.or.id</p>
                        <p>Instagram : @ppgt</p>
                        <p>Facebook : PPGT</p>

                    </div>

                </div>

                <div class="border-t border-slate-700 mt-12 pt-8 text-center text-sm">

                    © {{ date('Y') }} PPGT KMTr. All Rights Reserved.

                </div>

            </div>

        </footer>

    </div>
@endsection
