<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PemilihanPutaran extends Model
{
    protected $table = 'pemilihan_putaran';

    protected $fillable = ['pemilihan_id', 'jabatan_id', 'tahap', 'status', 'dibuka_pada', 'ditutup_pada'];

    protected $casts = ['dibuka_pada' => 'datetime', 'ditutup_pada' => 'datetime'];

    public function pemilihan()
    {
        return $this->belongsTo(Pemilihan::class);
    }

    public function calon()
    {
        return $this->hasMany(PemilihanCalon::class, 'putaran_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(PemilihanJabatan::class, 'jabatan_id');
    }

    public function jumlahMemilih(): int
    {
        return DB::table('pemilihan_sudah_memilih')->where('putaran_id', $this->id)->count();
    }

    /** Peserta yang punya hak suara tetapi belum memilih. Hanya berisi kehadiran, bukan pilihan. */
    public function belumMemilih(): Collection
    {
        return $this->pemilihan->peserta()
            ->whereIn('peran', PemilihanPeserta::PERAN_MEMILIH)
            ->whereNotIn('id', DB::table('pemilihan_sudah_memilih')->where('putaran_id', $this->id)->select('peserta_id'))
            ->orderBy('nama')
            ->pluck('nama');
    }

    /** Hasil hanya boleh terbaca setelah ditutup supaya tidak memengaruhi pemilih. */
    public function hasil(): Collection
    {
        if ($this->status !== 'ditutup') {
            return collect();
        }

        $suara = DB::table('pemilihan_surat_suara')
            ->where('putaran_id', $this->id)
            ->selectRaw('calon_id, COUNT(*) as jumlah')
            ->groupBy('calon_id')
            ->pluck('jumlah', 'calon_id');

        return $this->calon()->with('peserta.jemaat')->get()
            ->each(fn (PemilihanCalon $c) => $c->suara = (int) ($suara[$c->id] ?? 0))
            ->sortBy(fn (PemilihanCalon $c) => [-$c->suara, $c->peserta->nama])
            ->values();
    }

    /** AD Pasal 12 dan Memori Penjelasannya: suara terbanyak mutlak adalah setengah ditambah satu. */
    public static function batasMutlak(int $totalSuara): int
    {
        return (int) floor($totalSuara / 2) + 1;
    }
}
