@extends('layouts.auth')

@section('title', 'Link PPGT KMTr')

@push('addon-style')
    <style>
        .lt-halaman {
            min-height: 100vh;
            background-color: #f1f5f9;
            background-image: radial-gradient(rgba(30, 58, 138, .13) 1.5px, transparent 1.5px);
            background-size: 22px 22px;
            padding-bottom: 40px;
        }

        .lt-hero {
            position: relative;
            overflow: hidden;
            padding: 44px 16px 84px;
            text-align: center;
            color: #fff;
            background: linear-gradient(135deg, #1E3A8A, #1d4ed8 60%, #0891b2);
            border-radius: 0 0 50% 50% / 0 0 36px 36px;
        }

        .lt-hero::before,
        .lt-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .lt-hero::before {
            top: -70px;
            right: -50px;
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, .10);
        }

        .lt-hero::after {
            bottom: -90px;
            left: -60px;
            width: 260px;
            height: 260px;
            border: 28px solid rgba(255, 255, 255, .07);
        }

        .lt-logo {
            position: relative;
            z-index: 1;
            width: 96px;
            height: 96px;
            margin: 0 auto;
            padding: 5px;
            border-radius: 50%;
            object-fit: contain;
            background: #fff;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, .22), 0 12px 28px rgba(15, 23, 42, .30);
        }

        .lt-nama {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.25;
        }

        .lt-sub {
            position: relative;
            z-index: 1;
            margin-top: 4px;
            font-size: 14px;
            color: rgba(255, 255, 255, .85);
        }

        .lt-isi {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            margin: -52px auto 0;
            padding: 0 16px;
        }

        .lt-tombol,
        .lt-judul {
            animation: lt-muncul .45s ease both;
            animation-delay: calc(var(--i, 0) * 45ms);
        }

        .lt-tombol {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            padding: 12px 16px 12px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            color: #1e293b;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .lt-tombol:hover,
        .lt-tombol:focus-visible {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 12px 24px rgba(30, 58, 138, .18);
        }

        .lt-tombol:hover .lt-panah {
            transform: translateX(3px);
            color: #1E3A8A;
        }

        .lt-ikon {
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(135deg, #1E3A8A, #2563EB);
        }

        .lt-teks {
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .lt-panah {
            flex: none;
            font-size: 12px;
            color: #94a3b8;
            transition: transform .18s ease, color .18s ease;
        }

        .lt-judul {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 26px 0 14px;
            font-size: 15px;
            font-weight: 700;
            color: #1E3A8A;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .lt-judul:first-child {
            margin-top: 8px;
        }

        .lt-judul::before,
        .lt-judul::after {
            content: "";
            flex: 1;
            min-width: 20px;
            height: 2px;
            border-radius: 2px;
            background: #bfdbfe;
        }

        .lt-kosong {
            padding: 40px 16px;
            border-radius: 16px;
            background: #fff;
            color: #64748b;
            font-size: 14px;
            text-align: center;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
        }

        .lt-kaki {
            margin-top: 28px;
            font-size: 13px;
            text-align: center;
            color: #64748b;
        }

        .lt-kaki a {
            font-weight: 600;
            color: #1E3A8A;
        }

        @keyframes lt-muncul {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .lt-tombol,
            .lt-judul {
                animation: none;
                transition: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="lt-halaman">
        <header class="lt-hero">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT" class="lt-logo">
            <h1 class="lt-nama">PPGT KMTr</h1>
            <p class="lt-sub">Klasis Makassar Timur</p>
        </header>

        <main class="lt-isi">
            @forelse ($links as $item)
                @if ($item->link)
                    <a href="{{ $item->link }}" class="lt-tombol" style="--i: {{ $loop->index }}">
                        <span class="lt-ikon"><i class="fa-solid fa-link"></i></span>
                        <span class="lt-teks">{{ $item->nama }}</span>
                        <i class="fa-solid fa-chevron-right lt-panah"></i>
                    </a>
                @else
                    <h2 class="lt-judul" style="--i: {{ $loop->index }}">{{ $item->nama }}</h2>
                @endif
            @empty
                <p class="lt-kosong">Belum ada link.</p>
            @endforelse

            <p class="lt-kaki"><a href="{{ url('/') }}">Kunjungi website PPGT KMTr</a></p>
        </main>
    </div>
@endsection
