<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Kartu Kode {{ $pemilihan->nama }}</title>
    {{-- Dompdf hanya mendukung CSS sederhana, jadi tata letak memakai tabel. --}}
    <style>
        @page {
            margin: 12mm 12mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 9pt;
            margin: 0;
        }

        .halaman-baru {
            page-break-before: always;
        }

        .kepala {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            border-bottom: 1.5pt solid #1e3a8a;
        }

        .kepala td {
            padding-bottom: 3mm;
            vertical-align: middle;
        }

        .kepala .logo {
            width: 13mm;
        }

        .kepala img {
            width: 11mm;
            height: 11mm;
        }

        .judul {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a8a;
        }

        .sub {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 1mm;
        }

        .jumlah {
            text-align: right;
            font-size: 8.5pt;
            color: #475569;
        }

        .jumlah strong {
            display: block;
            font-size: 15pt;
            color: #1e3a8a;
        }

        .petunjuk {
            font-size: 8pt;
            color: #64748b;
            margin-bottom: 4mm;
        }

        .kartu-kartu {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 3mm;
            margin: 0 -3mm;
        }

        .kartu-kartu tr {
            page-break-inside: avoid;
        }

        .kartu {
            width: 50%;
            height: 44mm;
            border: 1pt dashed #94a3b8;
            border-radius: 3mm;
            padding: 3.5mm 4mm;
            vertical-align: top;
        }

        .kartu-kosong {
            border: 0;
        }

        .baris-atas {
            width: 100%;
            border-collapse: collapse;
        }

        .baris-atas img {
            width: 7mm;
            height: 7mm;
        }

        .label {
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 1pt;
            color: #1e3a8a;
        }

        .kegiatan {
            font-size: 7pt;
            color: #64748b;
        }

        .nama {
            margin-top: 2.5mm;
            font-size: 11pt;
            font-weight: bold;
        }

        .jemaat {
            font-size: 7.5pt;
            color: #64748b;
        }

        .kode {
            margin-top: 2.5mm;
            padding: 2mm 0;
            border-radius: 2mm;
            background: #eff6ff;
            text-align: center;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 19pt;
            font-weight: bold;
            letter-spacing: 3pt;
            color: #1e3a8a;
        }

        .catatan {
            margin-top: 1.5mm;
            font-size: 6.5pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    @foreach ($kelompok as $jemaat => $daftar)
        <div @class(['halaman-baru' => ! $loop->first])>
            <table class="kepala">
                <tr>
                    <td class="logo"><img src="{{ $logo }}" alt=""></td>
                    <td>
                        <div class="judul">{{ $jemaat !== '' ? 'Jemaat ' . $jemaat : $pemilihan->nama }}</div>
                        <div class="sub">Kartu kode pemungutan suara · {{ $pemilihan->nama }}</div>
                    </td>
                    <td class="jumlah"><strong>{{ $daftar->count() }}</strong>kartu</td>
                </tr>
            </table>

            <p class="petunjuk">Gunting mengikuti garis putus-putus, lalu berikan langsung kepada pemilihnya. Satu kode untuk satu orang, dipakai sekali di setiap pemungutan suara.</p>

            <table class="kartu-kartu">
                @foreach ($daftar->values()->chunk(2) as $baris)
                    <tr>
                        @foreach ($baris as $orang)
                            <td class="kartu">
                                <table class="baris-atas">
                                    <tr>
                                        <td style="width: 9mm"><img src="{{ $logo }}" alt=""></td>
                                        <td>
                                            <div class="label">KARTU SUARA · RAHASIA</div>
                                            <div class="kegiatan">{{ $pemilihan->nama }}</div>
                                        </td>
                                    </tr>
                                </table>
                                <div class="nama">{{ $orang->nama }}</div>
                                <div class="jemaat">{{ $orang->jemaat?->nama ? 'Jemaat ' . $orang->jemaat->nama : '' }}</div>
                                <div class="kode">{{ $orang->kode }}</div>
                                <div class="catatan">Jangan berikan kode ini kepada orang lain.</div>
                            </td>
                        @endforeach
                        @if ($baris->count() === 1)
                            <td class="kartu kartu-kosong"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</body>

</html>
