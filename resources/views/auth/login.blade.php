@extends('layouts.auth')

@section('title', 'Login - PPGT KMTr')

@push('prepend-style')
    @include('includes.publik.gaya')
@endpush

@section('content')
    @php
        $isian = 'h-12 w-full rounded-xl border-slate-200 px-4 text-sm text-slate-800 transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20';
    @endphp

    <div class="beranda grid min-h-screen bg-[#f5f7fd] text-slate-800 lg:grid-cols-12">

        <section class="beranda-gelap flex flex-col justify-between gap-10 px-5 py-8 text-white sm:px-8 lg:col-span-7 lg:px-12 lg:py-12 xl:px-16">
            <a href="{{ url('/') }}" class="flex items-center gap-4">
                <span class="flex-none rounded-full bg-white p-1 shadow-[0_0_30px_rgba(147,197,253,.45)]">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" class="h-12 w-12 rounded-full lg:h-14 lg:w-14">
                </span>
                <span class="leading-tight">
                    <span class="block text-lg font-bold">PPGT KMTr</span>
                    <span class="mt-0.5 block text-xs text-blue-100 sm:text-sm">Persekutuan Pemuda Gereja Toraja Klasis Makassar Timur</span>
                </span>
            </a>

            <div class="hidden lg:block">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-blue-50">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                    Sistem administrasi pelayanan
                </p>
                <p class="mt-6 text-5xl font-extrabold leading-[1.12] tracking-tight xl:text-[4rem]">
                    Bersekutu, bersaksi,<br>
                    <span class="beranda-gradasi">dan melayani.</span>
                </p>
                <p class="mt-6 max-w-xl text-lg leading-8 text-blue-100">
                    Pusat pengelolaan data anggota, inventaris organisasi, peminjaman barang, dan kegiatan pelayanan PPGT Klasis Makassar Timur.
                </p>
            </div>

            <figure class="hidden items-center justify-between gap-6 rounded-2xl border border-blue-300/20 bg-[#10245a]/60 p-5 lg:flex">
                <blockquote class="beranda-ayat text-lg italic">"Kader Siap Utus, Teguh dalam Kristus."</blockquote>
                <figcaption class="flex-none text-right text-xs font-bold uppercase tracking-wider text-amber-300">
                    <i class="fa-solid fa-bookmark mr-1" aria-hidden="true"></i> {{ date('Y') }}
                </figcaption>
            </figure>
        </section>

        <main class="flex items-center justify-center px-5 py-10 sm:px-8 lg:col-span-5">
            <div class="w-full max-w-md">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-blue-950/10 sm:p-8 lg:p-10">
                    <p class="pil-publik"><i class="fa-solid fa-lock" aria-hidden="true"></i> Khusus pengurus</p>
                    <h1 class="judul-publik mt-4">Selamat datang</h1>
                    <p class="mt-2 text-sm text-slate-500">Silakan login untuk melanjutkan.</p>

                    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="username" autofocus
                                placeholder="Masukkan email" class="{{ $isian }}">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <input type="password" name="password" id="password" autocomplete="current-password"
                                placeholder="Masukkan password" class="{{ $isian }}">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-700 focus:ring-blue-700">
                            Ingat saya
                        </label>

                        <button type="submit" class="h-12 w-full rounded-xl bg-blue-700 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800">
                            Login
                        </button>
                    </form>
                </div>

                <p class="mt-6 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <a href="{{ url('/') }}" class="font-semibold text-primary transition hover:text-blue-600">
                        <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i> Kembali ke beranda
                    </a>
                    <span>&copy; {{ date('Y') }} PPGT KMTr</span>
                </p>
            </div>
        </main>
    </div>
@endsection
