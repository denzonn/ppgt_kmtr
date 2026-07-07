@extends('layouts.app')

@section('title', 'Dashboard')

@section('page', 'dashboard')

@section('page_title', 'Dashboard')

@section('content')

    <div class="space-y-8">

        {{-- Welcome --}}
        <div
            class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-primary via-blue-600 to-blue-500 p-8 shadow-xl">

            {{-- Decoration --}}
            <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10">
            </div>

            <div class="absolute bottom-0 right-24 w-24 h-24 rounded-full bg-white/10">
            </div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm text-white backdrop-blur">

                        <i class="fa-solid fa-hand-peace"></i>

                        Shalom, Selamat Datang

                    </span>

                    <h1 class="mt-5 text-4xl font-bold text-white">

                        {{ auth()->user()->nama_lengkap }}

                    </h1>

                    <p class="mt-4 max-w-2xl text-white/85 leading-7">

                        Selamat datang kembali di Sistem Informasi
                        <b>PPGT Klasis Makassar Timur.</b>

                        Semoga pelayanan dan pekerjaan hari ini
                        berjalan dengan baik.

                    </p>

                </div>

                <div class="hidden lg:block text-right">

                    <img src="" class="w-28 opacity-20 ml-auto">

                    <p class="text-white/70 mt-4">

                        {{ now()->translatedFormat('l') }}

                    </p>

                    <h2 class="text-2xl font-bold text-white">

                        {{ now()->translatedFormat('d F Y') }}

                    </h2>

                </div>

            </div>

        </div>


        {{-- Statistik --}}

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

            {{-- Card --}}

            <div
                class="group rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm text-slate-500">

                            Inventaris

                        </p>

                        <h2 class="mt-4 text-5xl font-black text-slate-800">

                            {{ $inventarisCount }}

                        </h2>

                        <p class="mt-2 text-sm text-green-600">

                            +{{ $inventarisThisMonthCount }} bulan ini

                        </p>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">

                        <i class="fa-solid fa-box-archive text-xl"></i>

                    </div>

                </div>

            </div>

            {{-- Card --}}

            <div
                class="group rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm text-slate-500">

                            Dipinjam

                        </p>

                        <h2 class="mt-4 text-5xl font-black">

                            {{ $peminjamanCount }}

                        </h2>

                        <p class="mt-2 text-sm text-amber-600">

                            {{ $menungguKembaliPinjamanCount }} menunggu kembali

                        </p>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">

                        <i class="fa-solid fa-arrow-right-arrow-left text-xl"></i>

                    </div>

                </div>

            </div>

            {{-- Card --}}

            <div
                class="group rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm text-slate-500">

                            Anggota

                        </p>

                        <h2 class="mt-4 text-5xl font-black">

                            247

                        </h2>

                        <p class="mt-2 text-sm text-blue-600">

                            Aktif

                        </p>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">

                        <i class="fa-solid fa-users text-xl"></i>

                    </div>

                </div>

            </div>

            {{-- Card --}}

            <div
                class="group rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm text-slate-500">

                            Agenda

                        </p>

                        <h2 class="mt-4 text-5xl font-black">

                            5

                        </h2>

                        <p class="mt-2 text-sm text-emerald-600">

                            Minggu ini

                        </p>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">

                        <i class="fa-solid fa-calendar-days text-xl"></i>

                    </div>

                </div>

            </div>

        </div>

        {{-- Aktivitas --}}
        <div class="bg-white rounded-2xl border border-slate-200">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="font-semibold text-lg">
                    Aktivitas Terbaru
                </h2>

            </div>

            <div class="divide-y divide-slate-100">

                @foreach ([1, 2, 3] as $i)
                    <div class="flex items-start gap-4 p-6">

                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">

                            <i class="fa-solid fa-clock text-primary"></i>

                        </div>

                        <div>

                            <p class="font-medium">
                                Laptop ASUS dipinjam oleh Denson
                            </p>

                            <p class="text-sm text-slate-500 mt-1">
                                10 menit yang lalu
                            </p>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </div>

@endsection
