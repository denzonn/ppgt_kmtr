<?php

namespace App\Support;

use App\Models\PendampinganKegiatan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Membaca teks rekap pendampingan yang ditulis bebas, misalnya:
 *
 *   🟦 November 2026 🌈:
 *   TANGGAL 02 NOVEMBER (SENIN)
 *   * Rapat SC RA Tamalanrea
 *   Hadir : Rocky, Cici
 *   Sambutan : Rocky
 *
 * Nama yang hadir disimpan persis seperti ditulis, tanpa dicocokkan ke data anggota.
 */
class RekapPendampingan
{
    private const BULAN = [
        'januari' => 1, 'jan' => 1, 'january' => 1,
        'februari' => 2, 'feb' => 2, 'pebruari' => 2, 'february' => 2,
        'maret' => 3, 'mar' => 3, 'march' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5, 'may' => 5,
        'juni' => 6, 'jun' => 6, 'june' => 6,
        'juli' => 7, 'jul' => 7, 'july' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8, 'august' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
        'november' => 11, 'nov' => 11, 'nopember' => 11,
        'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
    ];

    private const HARI = "senin|selasa|rabu|kamis|jumat|jum'at|sabtu|minggu";

    /**
     * @return array{kegiatan: list<array{tanggal: string, nama: string, hadir: list<string>, sambutan: list<string>}>, abaikan: list<string>}
     */
    public static function baca(string $teks, ?int $tahunBawaan = null): array
    {
        $namaBulan = implode('|', array_keys(self::BULAN));
        $tahun = $tahunBawaan ?? now()->year;
        $tanggal = null;
        $kegiatan = [];
        $abaikan = [];
        $terakhir = null;   // indeks kegiatan yang sedang diisi
        $lanjut = null;     // 'hadir' atau 'sambutan' bila daftar nama berlanjut ke baris berikutnya

        foreach (preg_split('/\r\n|\r|\n/', $teks) as $baris) {
            $bersih = trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $baris));

            if ($bersih === '') {
                $lanjut = null;

                continue;
            }

            // TANGGAL 02 NOVEMBER (SENIN), Tgl 2 Nov 2026, atau tanpa kata tanggal: "> 26 Oktober 2026 :".
            // Tanpa kata tanggal, barisnya harus hanya berisi tanggal, supaya "• 17 Agustus ..." tetap terbaca sebagai kegiatan.
            if (preg_match('/^\P{L}*(?:tanggal|tgl)\.?\s*(\d{1,2})\s+('.$namaBulan.')\.?(?:\s+(\d{4}))?/iu', $bersih, $m)
                || preg_match('/^[^\p{L}\d*•\-–]*(?:'.self::HARI.')?,?\s*(\d{1,2})\s+('.$namaBulan.')\.?(?:\s+(\d{4}))?[^\p{L}]*(?:\(\s*\p{L}+\s*\)[^\p{L}]*)?$/iu', $bersih, $m)) {
                $bulan = self::BULAN[mb_strtolower($m[2])];
                $tahun = isset($m[3]) ? (int) $m[3] : $tahun;
                $tanggal = checkdate($bulan, (int) $m[1], $tahun) ? CarbonImmutable::create($tahun, $bulan, (int) $m[1])->toDateString() : null;
                $lanjut = null;
                $terakhir = null;

                if (! $tanggal) {
                    $abaikan[] = $bersih;
                }

                continue;
            }

            // Hadir : Denson, Bill, Rocky   atau   Sambutan : Fila   (juga tebal ala WhatsApp: *Hadir :* Denson)
            if (preg_match('/^\P{L}*(hadir|sambutan)[\s:：*_]*(.*)$/iu', $bersih, $m)) {
                if ($terakhir === null) {
                    $abaikan[] = $bersih;

                    continue;
                }
                $kolom = mb_strtolower($m[1]);
                $kegiatan[$terakhir][$kolom] = [...$kegiatan[$terakhir][$kolom], ...self::pisahNama($m[2])];
                $lanjut = str_ends_with(rtrim($m[2]), ',') ? $kolom : null;

                continue;
            }

            // Judul tebal seperti "*Absensi kegiatan :*" bukan kegiatan.
            if (preg_match('/^\*[^*]*[:：]\s*\*$/u', $bersih)) {
                continue;
            }

            // * Nama kegiatan (juga -, •, atau 1.)
            if (preg_match('/^(?:[*•\-–]|\d{1,2}[.)])\s*(.+)$/u', $bersih, $m)) {
                if (! $tanggal) {
                    $abaikan[] = $bersih;

                    continue;
                }
                $kegiatan[] = ['tanggal' => $tanggal, 'nama' => Str::limit(Str::squish($m[1]), 255, ''), 'hadir' => [], 'sambutan' => []];
                $terakhir = array_key_last($kegiatan);
                $lanjut = null;

                continue;
            }

            // Judul bulan seperti "🟦 November 2026 🌈:" hanya mengatur tahun.
            if (preg_match('/\b('.$namaBulan.')\s+(\d{4})\b/iu', $bersih, $m)) {
                $tahun = (int) $m[2];

                continue;
            }

            // Daftar hadir boleh berlanjut ke baris berikutnya, asal baris sebelumnya diakhiri koma.
            // Tanpa koma, baris itu dianggap catatan supaya tidak ikut terbaca sebagai nama.
            if ($lanjut && $terakhir !== null) {
                $kegiatan[$terakhir][$lanjut] = [...$kegiatan[$terakhir][$lanjut], ...self::pisahNama($bersih)];
                $lanjut = str_ends_with($bersih, ',') ? $lanjut : null;

                continue;
            }

            // Judul rekap seperti "[🤝 PJ Pendampingan ... 🤝]" tidak perlu dilaporkan.
            if (! preg_match('/^\[.*\]$/u', $bersih)) {
                $abaikan[] = $bersih;
            }
        }

        foreach ($kegiatan as &$item) {
            $item['hadir'] = self::unik($item['hadir']);
            $item['sambutan'] = self::unik($item['sambutan']);
        }

        return ['kegiatan' => $kegiatan, 'abaikan' => $abaikan];
    }

    /**
     * Menyimpan hasil baca. Kegiatan dengan tanggal dan nama yang sama diperbarui, jadi rekap bisa dikirim ulang.
     *
     * @return array{baru: int, diperbarui: int, sama: int}
     */
    public static function simpan(array $kegiatan, string $sumber): array
    {
        $hasil = ['baru' => 0, 'diperbarui' => 0, 'sama' => 0];

        DB::transaction(function () use ($kegiatan, $sumber, &$hasil) {
            foreach ($kegiatan as $item) {
                $rekap = PendampinganKegiatan::firstOrNew(['tanggal' => $item['tanggal'], 'nama' => $item['nama']]);
                $rekap->fill(['hadir' => $item['hadir'], 'sambutan' => $item['sambutan'] ?? [], 'sumber' => $sumber]);

                if (! $rekap->exists) {
                    $rekap->save();
                    $hasil['baru']++;
                } elseif ($rekap->isDirty(['hadir', 'sambutan'])) {
                    $rekap->save();
                    $hasil['diperbarui']++;
                } else {
                    $hasil['sama']++;
                }
            }
        });

        return $hasil;
    }

    /** @return list<string> */
    public static function pisahNama(string $teks): array
    {
        return array_values(array_filter(array_map(
            fn ($nama) => trim(Str::squish($nama), " .\t"),
            preg_split('/\s*(?:,|;|\/|&|\bdan\b)\s*/iu', $teks)
        ), fn ($nama) => $nama !== '' && $nama !== '-'));
    }

    /** Nama yang sama ditulis dua kali dalam satu kegiatan hanya dihitung sekali. */
    private static function unik(array $nama): array
    {
        $hasil = [];
        foreach ($nama as $n) {
            $hasil[mb_strtolower($n)] ??= Str::limit($n, 100, '');
        }

        return array_values($hasil);
    }
}
