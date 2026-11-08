@extends('layouts.auth')

@section('title', 'Absen ' . $absen->nama)

@include('includes.motif-latar')

@push('addon-style')
    <style>
        .absen-pilihan:has(input:checked) {
            border-color: #1E3A8A;
            background-color: rgba(30, 58, 138, .05);
        }
    </style>
@endpush

@section('content')
    <div class="motif-latar min-h-screen bg-slate-100 pb-10">
        <div class="mx-auto w-full max-w-lg px-4 py-6 sm:py-10">

            <div class="mb-6 text-center sm:mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT"
                    class="mx-auto h-16 w-16 rounded-xl object-contain shadow-sm">

                <p class="mt-4 text-sm font-semibold text-primary">Absen Kegiatan</p>
                <h1 class="mt-1 break-words text-xl font-bold text-slate-800">{{ $absen->nama }}</h1>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                @if (request()->boolean('terkirim'))
                    <div class="p-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <h2 class="mt-4 font-bold text-slate-800">Terima kasih</h2>
                        <p class="mt-1 text-sm text-slate-500">Absen Anda sudah tercatat.</p>
                        <a href="{{ route('absen.isi', $absen->token) }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            <i class="fas fa-plus"></i>
                            Isi Absen Lagi
                        </a>
                    </div>
                @else
                    {{-- Tombol dinonaktifkan saat dikirim supaya klik ganda tidak mencatat dua kali. --}}
                    <form method="POST" action="{{ route('absen.kirim', $absen->token) }}" class="space-y-5 p-6"
                        onsubmit="this.querySelector('button[type=submit]').disabled = true">
                        @csrf

                        <div>
                            <label for="nama" class="mb-2 block text-sm font-semibold text-slate-700">
                                Nama <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required maxlength="255"
                                autocomplete="name" placeholder="Masukkan nama"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                            @error('nama')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <fieldset>
                            <legend class="mb-2 block text-sm font-semibold text-slate-700">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </legend>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach (['Laki-laki', 'Perempuan'] as $pilihan)
                                    <label class="absen-pilihan flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700">
                                        <input type="radio" name="jenis_kelamin" value="{{ $pilihan }}" required
                                            class="text-primary focus:ring-primary" @checked(old('jenis_kelamin') === $pilihan)>
                                        {{ $pilihan }}
                                    </label>
                                @endforeach
                            </div>
                            @error('jenis_kelamin')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div>
                            <label for="asal_jemaat" class="mb-2 block text-sm font-semibold text-slate-700">
                                Asal Jemaat <span class="text-red-500">*</span>
                            </label>
                            <select id="asal_jemaat" name="asal_jemaat" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                                <option value="">Pilih asal jemaat</option>
                                @foreach ($jemaat as $nama)
                                    <option value="{{ $nama }}" @selected(old('asal_jemaat') === $nama)>{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('asal_jemaat')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="alamat" class="mb-2 block text-sm font-semibold text-slate-700">
                                Alamat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" required maxlength="500"
                                autocomplete="street-address" placeholder="Masukkan alamat"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                            @error('alamat')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jabatan" class="mb-2 block text-sm font-semibold text-slate-700">
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <select id="jabatan" name="jabatan" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                                <option value="">Pilih jabatan</option>
                                @foreach ($absen->jabatan as $jabatan)
                                    <option value="{{ $jabatan }}" @selected(old('jabatan') === $jabatan)>{{ $jabatan }}</option>
                                @endforeach
                            </select>
                            @error('jabatan')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60">
                            Kirim
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
