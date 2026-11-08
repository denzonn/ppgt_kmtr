<div id="modalDetailAnggota"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

    <div
        class="modal-content mx-auto my-6 w-full max-w-4xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i class="fas fa-id-card text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Detail Anggota</h3>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Informasi lengkap data anggota PPGT.
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeModal('modalDetailAnggota')"
                class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Loading --}}
        <div id="detailAnggotaLoading" class="px-6 py-16 text-center text-sm text-slate-400">
            <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>
            Memuat detail anggota...
        </div>

        {{-- Content --}}
        <div id="detailAnggotaContent" class="hidden space-y-6 p-6">

            {{-- Identitas utama --}}
            <div class="rounded-2xl border border-primary/10 bg-primary/[0.04] p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Nomor Anggota
                        </p>
                        <h4 id="detailNoAnggota" class="mt-1 text-xl font-bold text-primary">
                            -
                        </h4>
                        <p id="detailNama" class="mt-1 text-lg font-medium text-slate-600">
                            -
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span id="detailStatusAnggota"
                            class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary">
                            -
                        </span>

                        <span id="detailStatusWilayah"
                            class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold">
                            -
                        </span>
                    </div>
                </div>
            </div>

            {{-- Data pribadi --}}
            <div>
                <div class="mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Informasi Pribadi</h4>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs text-slate-400">NIK</p>
                        <p id="detailNik" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Jenis Kelamin</p>
                        <p id="detailJenisKelamin" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Tempat, Tanggal Lahir</p>
                        <p id="detailTempatTanggalLahir" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Golongan Darah</p>
                        <p id="detailGolonganDarah" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Pendidikan</p>
                        <p id="detailPendidikan" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- Data organisasi --}}
            <div>
                <div class="mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i class="fas fa-church text-sm"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Informasi Keanggotaan</h4>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Jemaat</p>
                        <p id="detailJemaat" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Kelompok</p>
                        <p id="detailKelompok" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Status Wilayah</p>
                        <p id="detailWilayahText" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- Kontak dan alamat --}}
            <div>
                <div class="mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 text-green-600">
                        <i class="fas fa-address-book text-sm"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Informasi Kontak</h4>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Nomor Telepon</p>
                        <p id="detailTelepon" class="mt-1 text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Email</p>
                        <p id="detailEmail" class="mt-1 break-all text-sm font-semibold text-slate-700">-</p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs text-slate-400">Alamat</p>
                        <p id="detailAlamat" class="mt-1 whitespace-pre-line text-sm font-semibold text-slate-700">-</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end border-t border-slate-100 px-6 py-4">
            <button type="button" onclick="closeModal('modalDetailAnggota')"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <i class="fas fa-times"></i>
                Tutup
            </button>
        </div>
    </div>
</div>
