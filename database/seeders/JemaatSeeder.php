<?php

namespace Database\Seeders;

use App\Models\Jemaat;
use Illuminate\Database\Seeder;

class JemaatSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            "Baji' Ma'rumpa",
            "Sudiang",
            "Filadelfia Citra Daya",
            "Biring Romang",
            "Lanraki Biringkanaya",
            "Satria Kasih",
            "Biringkanaya",
            "Tamalanrea",
            "Bukit Tamalanrea",
        ];

        foreach ($data as $nama) {
            Jemaat::firstOrCreate([
                'nama' => $nama,
            ]);
        }
    }
}
