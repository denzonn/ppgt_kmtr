<?php

// ponytail: rincian kegiatan ditulis manual per nama dan tidak ikut berubah saat deskripsi diedit di admin.
// Kalau kegiatan makin banyak, pindahkan ke kolom terstruktur pada tabel kegiatans.
// Kegiatan yang namanya tidak ada di sini memakai deskripsi dari database.
return [
    'bidang' => [
        'Umum' => 'Koordinasi kelembagaan, perlawatan, dan relasi antarjemaat.',
        'Pengakaran Iman & Spiritualitas' => 'Penguatan kedalaman firman, spiritualitas, serta pemeliharaan persekutuan pemuda di lingkup klasis.',
        'Kaderisasi & Organisasi' => 'Pembinaan pelayanan, struktur kepemimpinan pemuda, dan tata kelola organisasi.',
        'Pengembangan SDM' => 'Peningkatan kapasitas keterampilan, bakat, minat, dan pengetahuan generasi muda.',
        'Pelayanan Sosial & Kemasyarakatan' => 'Aksi kepedulian sosial dan kemasyarakatan di lingkungan sekitar.',
    ],

    'kegiatan' => [
        'Penyusunan & Pendistribusian Bahan Khotbah PPGT KMTr' => [
            ['jenis' => 'kotak', 'judul' => 'Tim penyusun khotbah (5 orang):', 'isi' => 'Sdri. Dhance Ariana Patibang, S.Psi sebagai ketua; Sdri. Raflesia Yuannisa Roreng sebagai sekretaris; Sdri. Dedet Maretty, S.E; Sdri. Sherlly Ochtaviyani, S.T; dan Sdr. Deni Sandi Lestari, S.T sebagai anggota.'],
            ['jenis' => 'teks', 'isi' => 'Penulis terdiri dari 34 orang dengan total bahan 43 minggu, kemudian diberikan tugas menulis dengan 1 ataupun 2 bahan sekaligus, dan ditulis sesuai dengan format yang telah disediakan oleh tim. Bahan yang digunakan SMC 3. Dalam penulisan ini juga terdapat beberapa editor, yaitu Dedet Maretty, S.E; Deni Sandi Lestari, S.T; Dhance Ariana Patibang, S.Psi; Raflesia Yuannisa Roreng, S.KM; dan Sherlly Ochtaviyani, S.T yang ditugaskan untuk mengecek dan memperbaiki tulisan yang telah dibuat.'],
            ['jenis' => 'kotak', 'latar' => 'bg-blue-50', 'judul' => 'Pendeta pemeriksa ajaran:', 'isi' => 'Pdt. Abigael Tangke Parung, S.TH., M.M; Pdt. Elyaser Palondongan, M.Th; Pdt. Firman Panggarra, M.Th., Kons; Pdt. Ida Theresia Toban, S.Th., M.M; Pdt. Jehezkiel K. Akakib, S.Th., M.M; Pdt. Meike R. Tapparan, M.Th., Kons; Pdt. Ribrate Sallata, S.T., S.Th; Pdt. Wesly, S.Th; dan Pdt. Yosama M. Sagala, M.Th, agar tulisan bahan khotbah ini tetap pada jalurnya.'],
            ['jenis' => 'catatan', 'isi' => 'Penerbitan bahan khotbah dikerjakan kurang lebih 3 bulan, dengan tahap pertama untuk edisi Februari dan tahap kedua pada bulan April, sehingga buku bahan khotbah itu rampung pada tanggal 3 Juni dengan judul buku ‘Suluh’ yang artinya Obor, sebagai penerang perjalanan kehidupan pemuda dalam masa mudanya. (Nama-nama penulis & asal jemaat terlampir)'],
        ],

        'Workshop Penulisan Bahan Khotbah PPGT KMTr' => [
            'waktu' => '10–11 Januari 2026 · BPK KMTr & online via Zoom',
            ['jenis' => 'teks', 'isi' => 'Tim ini bergerak di akhir tahun 2025 dengan membuat sebuah program pembinaan atau workshop pada tanggal 10–11 Januari 2026, bertempat di BPK KMTr & online via Zoom, dengan pemateri:'],
            ['jenis' => 'pemateri', 'orang' => [
                ['Pdt. Lukas Dayung, M.Th', 'Materi pengantar penyusunan tafsiran dasar bahan khotbah'],
                ['Sdr. Eltuin, S.Si.', 'Materi kaidah dasar penulisan bahan khotbah'],
            ]],
            ['jenis' => 'kecil', 'isi' => 'Kegiatan ini dihadiri oleh perwakilan-perwakilan jemaat sebagai penulis.'],
        ],
    ],
];
