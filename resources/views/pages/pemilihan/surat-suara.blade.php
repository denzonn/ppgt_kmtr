@extends('layouts.auth')

@section('title', 'Surat Suara')

@push('prepend-style')
    @include('includes.publik.gaya')
    {{--
        Gaya halaman ini sengaja ditulis sendiri, tidak bergantung pada kelas Tailwind,
        supaya tampilan bilik tetap benar walau aset belum dibangun ulang.
    --}}
    <style>
        .ss-halaman {
            min-height: 100vh;
            background: #f1f5f9;
            color: #1e293b;
            padding-bottom: 96px;
        }

        .ss-kepala {
            padding: 18px 16px 44px;
            text-align: center;
            color: #fff;
        }

        .ss-kepala .ss-kecil {
            font-size: 12px;
            color: #bfdbfe;
        }

        .ss-kepala h1 {
            margin: 2px 0 0;
            font-size: 22px;
            font-weight: 700;
        }

        .ss-kepala .ss-tahap {
            font-size: 13px;
            font-weight: 600;
            color: #fcd34d;
        }

        .ss-pemilih {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            font-size: 12px;
        }

        .ss-kartu {
            max-width: 960px;
            margin: -28px auto 0;
            padding: 14px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .ss-petunjuk {
            margin: 0 0 10px;
            text-align: center;
            font-size: 13px;
            color: #475569;
        }

        .ss-cari {
            width: 100%;
            height: 40px;
            margin-bottom: 10px;
            padding: 0 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 14px;
        }

        .ss-cari:focus {
            outline: none;
            border-color: #1e3a8a;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, .12);
        }

        .ss-daftar {
            display: grid;
            gap: 6px;
        }

        @media (min-width: 640px) {
            .ss-daftar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .ss-daftar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .ss-calon {
            display: block;
            cursor: pointer;
        }

        .ss-calon[hidden],
        .ss-kosong[hidden] {
            display: none;
        }

        .ss-calon input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ss-isi {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            transition: border-color .15s, background-color .15s;
        }

        .ss-calon:hover .ss-isi {
            border-color: #93c5fd;
        }

        .ss-inisial {
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ss-teks {
            flex: 1;
            min-width: 0;
        }

        .ss-nama {
            display: block;
            overflow: hidden;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
            text-overflow: ellipsis;
            color: #0f172a;
        }

        .ss-asal {
            display: block;
            font-size: 12px;
            color: #64748b;
        }

        .ss-tanda {
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 999px;
            color: #fff;
            font-size: 10px;
        }

        .ss-tanda i {
            opacity: 0;
        }

        /* Calon yang dipilih harus langsung terlihat. */
        .ss-calon input:checked + .ss-isi {
            border-color: #1e3a8a;
            background: #eff6ff;
        }

        .ss-calon input:checked + .ss-isi .ss-inisial {
            background: #1e3a8a;
            color: #fff;
        }

        .ss-calon input:checked + .ss-isi .ss-tanda {
            border-color: #1e3a8a;
            background: #1e3a8a;
        }

        .ss-calon input:checked + .ss-isi .ss-tanda i {
            opacity: 1;
        }

        .ss-calon input:focus-visible + .ss-isi {
            box-shadow: 0 0 0 3px rgba(30, 58, 138, .25);
        }

        .ss-kosong {
            padding: 24px 0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .ss-bawah {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 10;
            border-top: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 -6px 20px rgba(15, 23, 42, .06);
        }

        .ss-bawah-isi {
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 960px;
            margin: 0 auto;
            padding: 10px 16px;
        }

        .ss-pilihan {
            flex: 1;
            min-width: 0;
            font-size: 12px;
            color: #64748b;
        }

        .ss-pilihan strong {
            display: block;
            overflow: hidden;
            font-size: 14px;
            white-space: nowrap;
            text-overflow: ellipsis;
            color: #94a3b8;
        }

        .ss-pilihan.ss-ada strong {
            color: #1e3a8a;
        }

        .ss-tombol {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
        }

        .ss-batal {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
        }

        .ss-kirim {
            border: 0;
            background: #1e3a8a;
            color: #fff;
        }

        .ss-kirim:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        /* Dialog konfirmasi. !important diperlukan untuk menimpa gaya SweetAlert global. */
        .ss-dialog {
            border-radius: 20px !important;
            padding: 20px 4px !important;
        }

        .ss-dialog-aksi {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
            margin-top: 18px !important;
            padding: 0 20px !important;
        }

        .ss-dialog-aksi button {
            height: 46px;
            margin: 0 !important;
            border-radius: 12px !important;
            font-size: 15px !important;
            font-weight: 600;
        }

        .ss-dialog .ss-dialog-ok {
            border: 0;
            background: #1e3a8a !important;
            color: #fff;
        }

        .ss-dialog .ss-dialog-batal {
            border: 1px solid #cbd5e1 !important;
            background: #fff !important;
            color: #475569;
        }
    </style>
@endpush

@section('content')
    @php
        $inisial = fn (string $nama) => collect(explode(' ', preg_replace('/^(Pdt|Sdr|Sdri|Pnt|Dkn)\.?\s+/i', '', $nama)))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->join('');
    @endphp

    <div class="beranda ss-halaman">
        <header class="beranda-gelap ss-kepala">
            <div class="ss-kecil">{{ $pemilihan->nama }}</div>
            <h1>{{ $putaran->jabatan->nama }}</h1>
            <div class="ss-tahap">{{ $putaran->tahap }}</div>
            <div class="ss-pemilih">Surat suara untuk <strong>{{ $peserta->nama }}</strong></div>
        </header>

        <form id="formSuara" method="POST" action="{{ route('bilik.pilih', $pemilihan->token) }}" style="padding: 0 12px">
            @csrf
            <input type="hidden" name="kode" value="{{ $peserta->kode }}">

            <div class="ss-kartu">
                <p class="ss-petunjuk">Ketuk <strong>satu</strong> nama, lalu tekan <strong>Kirim Suara</strong>.</p>

                @if ($calon->count() > 8)
                    <input type="search" id="cariCalon" class="ss-cari" placeholder="Cari nama calon" aria-label="Cari nama calon" autocomplete="off">
                @endif

                <div class="ss-daftar" role="radiogroup" aria-label="Daftar calon">
                    @foreach ($calon as $item)
                        <label class="ss-calon" data-nama="{{ mb_strtolower($item->peserta->nama) }}">
                            <input type="radio" name="calon_id" value="{{ $item->id }}" data-label="{{ $item->peserta->nama }}">
                            <span class="ss-isi">
                                <span class="ss-inisial" aria-hidden="true">{{ $inisial($item->peserta->nama) }}</span>
                                <span class="ss-teks">
                                    <span class="ss-nama">{{ $item->peserta->nama }}</span>
                                    <span class="ss-asal">{{ $item->peserta->jemaat ? 'Jemaat ' . $item->peserta->jemaat->nama : 'Pengurus Klasis' }}</span>
                                </span>
                                <span class="ss-tanda" aria-hidden="true"><i class="fas fa-check"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p id="calonKosong" class="ss-kosong" hidden>Nama tidak ditemukan.</p>
            </div>

            <div class="ss-bawah">
                <div class="ss-bawah-isi">
                    <div class="ss-pilihan" data-pilihan>Pilihan Anda <strong>Belum memilih</strong></div>
                    <a href="{{ route('bilik.index', $pemilihan->token) }}" class="ss-tombol ss-batal">Batal</a>
                    <button type="submit" id="tombolKirim" class="ss-tombol ss-kirim" disabled>
                        <i class="fas fa-check-to-slot" aria-hidden="true"></i> Kirim Suara
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('addon-script')
    <script>
        // Layar kembali sendiri kalau ditinggal, supaya surat suara tidak terbuka untuk orang berikutnya.
        setTimeout(() => location.replace(@json(route('bilik.index', $pemilihan->token))), 120000);

        const form = document.getElementById('formSuara');
        const cari = document.getElementById('cariCalon');
        const kirim = document.getElementById('tombolKirim');
        const pilihan = form.querySelector('[data-pilihan]');

        // Tombol kirim baru aktif setelah memilih, jadi tidak ada pesan bawaan browser.
        form.addEventListener('change', function(event) {
            if (event.target.name !== 'calon_id') return;
            kirim.disabled = false;
            pilihan.classList.add('ss-ada');
            pilihan.querySelector('strong').textContent = event.target.dataset.label;
        });

        cari?.addEventListener('input', function() {
            const kata = cari.value.trim().toLowerCase();
            let ada = 0;
            form.querySelectorAll('[data-nama]').forEach(function(el) {
                const cocok = el.dataset.nama.includes(kata);
                el.hidden = !cocok;
                ada += cocok;
            });
            document.getElementById('calonKosong').hidden = ada > 0;
        });
        cari?.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());

        let sudahYakin = false;
        form.addEventListener('submit', function(e) {
            const dipilih = form.querySelector('input[name=calon_id]:checked');
            if (sudahYakin) return;
            e.preventDefault();
            if (!dipilih) return;

            Swal.fire({
                html: '<div style="text-align:center"><div style="font-size:13px;color:#64748b">Anda memilih</div><div data-nama style="margin-top:4px;font-size:22px;font-weight:700;color:#0f172a"></div><div style="margin-top:6px;font-size:13px;color:#64748b">Suara tidak bisa diubah setelah dikirim.</div></div>',
                didRender: popup => popup.querySelector('[data-nama]').textContent = dipilih.dataset.label,
                showCancelButton: true,
                confirmButtonText: 'Ya, kirim',
                cancelButtonText: 'Ubah',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'ss-dialog',
                    actions: 'ss-dialog-aksi',
                    confirmButton: 'ss-dialog-ok',
                    cancelButton: 'ss-dialog-batal',
                },
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;
                sudahYakin = true;
                kirim.disabled = true;
                kirim.textContent = 'Mengirim...';
                form.submit();
            });
        });
    </script>
@endpush
