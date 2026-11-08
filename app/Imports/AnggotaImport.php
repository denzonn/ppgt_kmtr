<?php

namespace App\Imports;

use App\Models\DataAnggota;
use App\Models\Jemaat;
use App\Models\Kelompok;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class AnggotaImport implements OnEachRow, WithHeadingRow
{
    public int $diproses = 0;
    public int $berhasil = 0;
    public int $dilewati = 0;

    public array $gagal = [];

    /** Baris yang tetap masuk, tetapi kelompoknya tidak dikenali. */
    public array $catatan = [];

    private array $jemaatMap = [];

    private array $kelompokMap = [];

    public function __construct()
    {
        foreach (Jemaat::all(['id', 'nama']) as $jemaat) {
            $this->jemaatMap[$this->normalisasi($jemaat->nama)] = $jemaat->id;
        }

        foreach (Kelompok::all(['id', 'jemaat_id', 'nama']) as $kelompok) {
            $this->kelompokMap[$kelompok->jemaat_id.'|'.$this->normalisasi($kelompok->nama)] = $kelompok->id;
        }
    }

    private function normalisasi($value): string
    {
        return Str::upper(
            preg_replace('/\s+/', ' ', trim((string) $value))
        );
    }

    private function bersihkan($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if (
            $value === '' ||
            $value === '-' ||
            Str::lower($value) === 'tidak tahu'
        ) {
            return null;
        }

        return $value;
    }

    private function tanggal($value): ?string
    {
        if ($value === null || trim((string) $value) === '' || $value === '-') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromDate(1899, 12, 30)
                    ->addDays((int) $value)
                    ->format('Y-m-d');
            }

            return Carbon::createFromFormat('d/m/Y', trim((string) $value))
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    public function onRow(Row $row): void
    {
        $this->diproses++;

        $baris = $row->getIndex();
        $data = $row->toArray();

        try {
            $noAnggota = $this->bersihkan($data['no_anggota'] ?? null);
            $nama = $this->bersihkan($data['nama'] ?? null);
            $jenisKelamin = $this->bersihkan($data['jenis_kelamin'] ?? null);
            $namaJemaat = $this->bersihkan($data['jemaat'] ?? null);

            if (!$noAnggota || !$nama || !$jenisKelamin || !$namaJemaat) {
                $this->catatGagal(
                    $baris,
                    $nama,
                    'Nomor anggota, nama, jenis kelamin, atau jemaat kosong.'
                );
                return;
            }

            // Normalisasi jenis kelamin.
            $jenisKelamin = Str::lower($jenisKelamin);

            if (in_array($jenisKelamin, ['laki-laki', 'laki laki', 'l'])) {
                $jenisKelamin = 'Laki-laki';
            } elseif (in_array($jenisKelamin, ['perempuan', 'p'])) {
                $jenisKelamin = 'Perempuan';
            } else {
                $this->catatGagal(
                    $baris,
                    $nama,
                    'Jenis kelamin tidak dikenali.'
                );
                return;
            }

            // Cocokkan nama jemaat.
            $jemaatId = $this->jemaatMap[$this->normalisasi($namaJemaat)] ?? null;

            if (!$jemaatId) {
                $this->catatGagal(
                    $baris,
                    $nama,
                    "Jemaat '{$namaJemaat}' tidak ditemukan."
                );
                return;
            }

            // Nomor anggota asli dari Excel dipertahankan.
            if (DataAnggota::where('no_anggota', $noAnggota)->exists()) {
                $this->dilewati++;
                return;
            }

            $tanggalLahir = $this->tanggal($data['tanggal_lahir'] ?? null);

            if (
                !empty($data['tanggal_lahir']) &&
                $data['tanggal_lahir'] !== '-' &&
                !$tanggalLahir
            ) {
                $this->catatGagal(
                    $baris,
                    $nama,
                    'Format tanggal lahir tidak valid.'
                );
                return;
            }

            // Golongan darah.
            $golonganDarah = strtoupper(
                $this->bersihkan($data['golongan_darah'] ?? null) ?? ''
            );

            if (!in_array($golonganDarah, ['A', 'B', 'AB', 'O'])) {
                $golonganDarah = null;
            }

            // Rhesus, contoh: Positif (+), Negatif (-).
            $rhesusRaw = Str::lower(
                $this->bersihkan($data['golongan_darah_rhesus'] ?? null) ?? ''
            );

            $rhesus = null;

            if (str_contains($rhesusRaw, 'positif')) {
                $rhesus = 'Positif';
            } elseif (str_contains($rhesusRaw, 'negatif')) {
                $rhesus = 'Negatif';
            }

            $namaKelompok = $this->bersihkan($data['kelompok'] ?? null);
            $kelompokId = $namaKelompok ? ($this->kelompokMap[$jemaatId.'|'.$this->normalisasi($namaKelompok)] ?? null) : null;

            DataAnggota::create([
                'no_anggota' => $noAnggota,
                'jemaat_id' => $jemaatId,
                'kelompok_id' => $kelompokId,
                'nama' => $nama,
                'jenis_kelamin' => $jenisKelamin,
                'tempat_lahir' => $this->bersihkan($data['tempat_lahir'] ?? null),
                'tanggal_lahir' => $tanggalLahir,

                // Nilai default.
                'status_anggota' => 'Aktif',
                'status_wilayah' => 'dalam_wilayah',

                'alamat' => $this->bersihkan($data['alamat'] ?? null),
                'telepon' => $this->bersihkan($data['telepon'] ?? null),
                'email' => $this->bersihkan($data['email'] ?? null),
                'golongan_darah' => $golonganDarah,
                'golongan_darah_rhesus' => $rhesus,
                'pendidikan' => null,
            ]);

            $this->berhasil++;

            if ($namaKelompok && ! $kelompokId) {
                $this->catatan[] = ['baris' => $baris, 'nama' => $nama, 'alasan' => "Kelompok '{$namaKelompok}' belum terdaftar di jemaat {$namaJemaat}. Anggota masuk tanpa kelompok."];
            }
        } catch (\Throwable $e) {
            $this->catatGagal(
                $baris,
                $nama ?? ($data['nama'] ?? '-'),
                $e->getMessage()
            );
        }
    }

    private function catatGagal($baris, $nama, $alasan): void
    {
        $this->gagal[] = [
            'baris' => $baris,
            'nama' => $nama ?? '-',
            'alasan' => $alasan,
        ];
    }
}
