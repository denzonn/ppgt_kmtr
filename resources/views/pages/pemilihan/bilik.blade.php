@extends('layouts.auth')

@section('title', 'Bilik Suara')

@push('prepend-style')
    @include('includes.publik.gaya')
    {{-- Layar selalu menyegarkan diri: cepat setelah suara masuk, dan berkala supaya form tidak kedaluwarsa. --}}
    <meta http-equiv="refresh" content="{{ session('terkirim') ? 5 : ($putaran ? 300 : 10) }};url={{ route('bilik.index', $pemilihan->token) }}">
@endpush

@section('content')
    <div class="beranda beranda-gelap flex min-h-screen flex-col items-center justify-center gap-6 p-5 text-slate-800">
        <header class="flex items-center gap-3 text-white">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" class="h-11 w-11 rounded-full bg-white p-0.5">
            <div class="leading-tight">
                <p class="text-xs text-blue-200">Bilik suara</p>
                <p class="font-bold">{{ $pemilihan->nama }}</p>
            </div>
        </header>

        <main class="w-full max-w-md rounded-3xl bg-white p-6 text-center shadow-2xl sm:p-8">
            @if (session('terkirim'))
                @include('pages.pemilihan._langkah-bilik', ['langkah' => 3])
                <span class="mx-auto mt-8 flex h-24 w-24 items-center justify-center rounded-full bg-primary text-4xl text-white shadow-lg shadow-primary/30" aria-hidden="true"><i class="fas fa-check"></i></span>
                <h1 class="mt-6 text-3xl font-bold text-slate-900">Suara tersimpan</h1>
                <p class="mt-2 text-slate-500">Terima kasih. Silakan kembali ke tempat duduk.</p>
                <p class="mt-6 text-xs text-slate-400">Layar kembali untuk pemilih berikutnya dalam <span data-hitung>5</span> detik.</p>
            @elseif (! $putaran)
                <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl text-slate-400" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                <h1 class="mt-5 text-2xl font-bold text-slate-900">Belum ada pemungutan suara</h1>
                <p class="mt-2 text-slate-500">Halaman ini terbuka sendiri saat pemungutan suara dimulai.</p>
                @if (session('ditolak'))
                    <p role="alert" class="mt-5 flex items-start gap-2 rounded-2xl bg-red-50 p-3 text-left text-sm font-semibold text-red-700"><i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>{{ session('ditolak') }}</p>
                @endif
            @else
                @include('pages.pemilihan._langkah-bilik', ['langkah' => 1])

                <p class="mt-6 text-sm font-semibold text-primary">{{ $putaran->tahap }}</p>
                <h1 class="mt-1 text-3xl font-bold leading-tight text-slate-900">{{ $putaran->jabatan->nama }}</h1>

                <form method="POST" action="{{ route('bilik.periksa', $pemilihan->token) }}" class="mt-6 text-left">
                    @csrf
                    <label for="kode" class="block text-center text-sm font-semibold text-slate-600">Ketik kode pada kartu Anda</label>
                    <input type="text" id="kode" name="kode" required autofocus autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="10" inputmode="text"
                        placeholder="••••••" aria-describedby="kodeBantuan"
                        class="mt-3 h-16 w-full rounded-2xl border-2 border-slate-200 text-center font-mono text-3xl font-bold uppercase tracking-[.35em] text-slate-900 placeholder:text-slate-300 focus:border-primary focus:ring-4 focus:ring-primary/15">
                    <p id="kodeBantuan" class="mt-2 text-center text-xs text-slate-400">6 karakter, huruf besar atau kecil sama saja.</p>

                    @if (session('ditolak'))
                        <p role="alert" class="mt-4 flex items-start gap-2 rounded-2xl bg-red-50 p-3 text-sm font-semibold text-red-700"><i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>{{ session('ditolak') }}</p>
                    @endif

                    <button type="submit" class="mt-5 flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-primary text-lg font-semibold text-white shadow-lg shadow-primary/25 transition hover:opacity-90">
                        Lanjut <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                    </button>
                </form>
            @endif
        </main>

        <p class="flex items-center gap-2 text-xs text-blue-200"><i class="fas fa-lock" aria-hidden="true"></i> Pilihan Anda rahasia. Sistem tidak mencatat siapa memilih siapa.</p>
    </div>
@endsection

@if (session('terkirim'))
    @push('addon-script')
        <script>
            const hitung = document.querySelector('[data-hitung]');
            let sisa = 5;
            setInterval(() => hitung.textContent = Math.max(0, --sisa), 1000);
        </script>
    @endpush
@endif
