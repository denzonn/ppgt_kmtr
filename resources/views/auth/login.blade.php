@extends('layouts.auth')

@section('title', 'Login - PPGT KMTr')

@section('content')
    <div class="min-h-screen grid lg:grid-cols-2 bg-slate-100">

        {{-- ===================== --}}
        {{-- LEFT SIDE --}}
        {{-- ===================== --}}
        <div class="hidden lg:flex relative overflow-hidden bg-gradient-to-br from-primary via-primary to-primary">

            {{-- Background Decoration --}}
            <div class="absolute inset-0">

                <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-white/10"></div>

                <div class="absolute -bottom-52 -right-52 w-[500px] h-[500px] rounded-full bg-white/5"></div>

                <div class="absolute inset-0 opacity-[0.05]"
                    style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size:24px 24px;">
                </div>

            </div>

            <div class="relative z-10 flex flex-col justify-between h-full px-16 py-14">

                {{-- Header --}}
                <div>

                    <div class="flex items-center gap-5">
                        <img src="{{ asset('images/logo.png') }}" class="w-16 h-16 object-contain" alt="Logo">
                        <div>

                            <h1 class="text-4xl font-bold text-white">
                                PPGT KMTr
                            </h1>

                            <p class="text-white/75 mt-1">
                                Persekutuan Pemuda Gereja Toraja
                            </p>

                            <p class="text-white/60 text-sm">
                                Klasis Makassar Timur
                            </p>

                        </div>

                    </div>

                    <div class="mt-14">

                        <span
                            class="inline-flex items-center rounded-full bg-white/10 text-white px-4 py-2 text-sm backdrop-blur">
                            ✨ Official Website
                        </span>

                        <h2 class="mt-8 text-5xl font-bold leading-tight text-white">
                            Bertumbuh dalam
                            <br>
                            Iman, Pelayanan,
                            <br>
                            dan Persekutuan.
                        </h2>

                        <p class="mt-8 max-w-xl text-lg leading-8 text-white/80">
                            Website ini menjadi pusat informasi dan pengelolaan administrasi
                            PPGT Klasis Makassar Timur, mulai dari data anggota,
                            inventaris organisasi, peminjaman barang,
                            hingga berbagai kegiatan pelayanan.
                        </p>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between border-t border-white/10 pt-8">

                    <div>

                        <p class="text-white font-semibold">
                            "Kader Siap Utus,
                            Teguh dalam Kristus."
                        </p>

                        <p class="text-white/60 text-sm mt-2">
                            PPGT Klasis Makassar Timur
                        </p>

                    </div>

                    <div class="text-right">

                        <div class="text-3xl font-bold text-white">
                            {{ date('Y') }}
                        </div>

                        <div class="text-sm text-white/60">
                            Official Website
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- ===================== --}}
        {{-- RIGHT SIDE --}}
        {{-- ===================== --}}
        <div class="flex items-center justify-center p-8">

            <div class="w-full max-w-md">

                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-8">

                    <img src="{{ asset('images/logo.png') }}" class="w-16 h-16 mx-auto mb-4">

                    <h2 class="text-2xl font-bold text-gray-800">
                        PPGT KMTr
                    </h2>

                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-10">

                    <h2 class="text-3xl font-bold text-gray-800">
                        Selamat Datang
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Silakan login untuk melanjutkan.
                    </p>

                    <form method="POST" action="{{ route('login') }}" class="mt-8">

                        @csrf

                        {{-- Email --}}
                        <div class="mb-5">

                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Email
                            </label>

                            <input type="email" name="email" id="email" placeholder="Masukkan email"
                                value="{{ old('email') }}"
                                class="w-full h-12 rounded-xl border border-gray-300 px-4
                            focus:border-primary
                            focus:ring-2
                            focus:ring-primary/20
                            outline-none transition">

                            <x-input-error :messages="$errors->get('email')" class="mt-2" />

                        </div>

                        {{-- Password --}}
                        <div>

                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>

                            <input type="password" name="password" id="password" placeholder="Masukkan password"
                                class="w-full h-12 rounded-xl border border-gray-300 px-4
                            focus:border-primary
                            focus:ring-2
                            focus:ring-primary/20
                            outline-none transition">

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />

                        </div>

                        {{-- Remember --}}
                        <div class="flex items-center justify-between mt-6">

                            <label class="flex items-center gap-2 text-sm text-gray-600">

                                <input type="checkbox" name="remember"
                                    class="rounded border-gray-300 text-primary focus:ring-primary">

                                Ingat saya

                            </label>

                        </div>

                        {{-- Button --}}
                        <button type="submit"
                            class="mt-8 w-full h-12 rounded-xl bg-primary text-white font-semibold
                        hover:brightness-95 transition">

                            Login

                        </button>

                    </form>

                </div>

                <p class="text-center text-sm text-gray-500 mt-8">
                    © {{ date('Y') }} PPGT KMTr. All Rights Reserved.
                </p>

            </div>

        </div>

    </div>
@endsection
