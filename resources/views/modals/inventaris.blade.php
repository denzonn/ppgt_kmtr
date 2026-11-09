<div id="modalInventaris"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

    <div
        class="modal-content mx-auto my-6 w-full max-w-3xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        <form id="formInventaris">
            @csrf

            <input type="hidden" name="_method" id="methodInventaris" value="POST">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-box text-lg"></i>
                    </div>
                    <div>
                        <h2 id="modalTitle" class="text-lg font-bold text-slate-800">Tambah Inventaris</h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Lengkapi informasi inventaris di bawah ini.
                        </p>
                    </div>
                </div>

                <button type="button" onclick="closeModal('modalInventaris')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-6 p-6">

                {{-- Foto --}}
                <div class="flex justify-center">
                    <div class="text-center">
                        <label class="mb-3 block text-sm font-semibold text-slate-700">
                            Foto Inventaris
                            <span class="text-slate-400">(opsional)</span>
                        </label>

                        <label for="foto"
                            class="group relative flex h-36 w-36 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-primary hover:bg-primary/5 md:h-40 md:w-40">

                            <img id="previewFoto" src="{{ asset('images/no-image.svg') }}" alt="Preview foto inventaris"
                                class="hidden h-full w-full rounded-xl object-cover">

                            <div id="placeholderFoto" class="text-center">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm transition group-hover:text-primary">
                                    <i class="fa-solid fa-camera text-xl"></i>
                                </div>

                                <p class="mt-3 text-xs text-slate-500 md:text-sm">
                                    Klik untuk menambah foto
                                </p>
                            </div>
                        </label>

                        <input type="file" id="foto" name="foto"
                            accept="image/jpeg,image/png,image/jpg,image/gif" capture="environment"
                            class="hidden">

                        <p class="mt-2 text-xs text-slate-400">
                            JPG, PNG, GIF. Maksimal 2 MB.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Informasi Inventaris --}}
                <div>
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-circle-info text-primary"></i>
                        <h3 class="font-bold text-slate-800">Informasi Inventaris</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        {{-- Jenis --}}
                        <div class="sm:col-span-2">
                            <label for="jenis" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jenis Inventaris
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="jenis" name="jenis" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">

                                <option value="">Pilih Jenis Inventaris</option>

                                <option value="PAK">
                                    PAK - Perlengkapan Administrasi Kesekretariatan
                                </option>

                                <option value="PDP">
                                    PDP - Perlengkapan Dapur Perkakas
                                </option>

                                <option value="PPL">
                                    PPL - Perlengkapan Penunjang Lainnya
                                </option>
                            </select>

                            <p class="mt-2 text-xs text-slate-500">
                                Kode inventaris akan dibuat otomatis berdasarkan jenis yang dipilih.
                            </p>
                        </div>

                        {{-- Nama --}}
                        <div>
                            <label for="nama" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Nama Inventaris
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="text" id="nama" name="nama" required maxlength="255"
                                placeholder="Masukkan nama inventaris"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        {{-- Harga --}}
                        <div>
                            <label for="harga" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Harga
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">
                                    Rp
                                </span>

                                <input type="text" id="harga" name="harga" required inputmode="numeric"
                                    autocomplete="off" placeholder="Contoh: 50.000"
                                    class="w-full rounded-xl border border-slate-200 py-2.5 pl-12 pr-4 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                            </div>
                        </div>

                        {{-- Tanggal Perolehan --}}
                        <div class="sm:col-span-2">
                            <label for="tanggal_perolehan" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Perolehan
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="date" id="tanggal_perolehan" name="tanggal_perolehan" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Kondisi --}}
                <div>
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-primary"></i>
                        <h3 class="font-bold text-slate-800">Jumlah Berdasarkan Kondisi</h3>
                    </div>

                    <p class="-mt-2 mb-4 text-xs text-slate-500">
                        Isi jumlah barang kondisi Baik. Klik tombol di bawah untuk mengisi kondisi lainnya.
                    </p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        {{-- Baik --}}
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                            <label for="kondisiBaik" class="mb-2 block text-sm font-semibold text-emerald-700">
                                <i class="fa-solid fa-circle-check mr-1"></i>
                                Baik
                            </label>

                            <input type="number" id="kondisiBaik" placeholder="Masukkan jumlah" name="kondisi[Baik]"
                                min="0"
                                class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        </div>

                        {{-- Kurang Baik --}}
                        <div id="boxKondisiKurangBaik" class="hidden rounded-xl border border-amber-100 bg-amber-50/50 p-4">
                            <label for="kondisiKurangBaik" class="mb-2 block text-sm font-semibold text-amber-700">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                Kurang Baik
                            </label>

                            <input type="number" id="kondisiKurangBaik" placeholder="Masukkan jumlah" name="kondisi[Kurang Baik]"
                                min="0"
                                class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                        </div>

                        {{-- Rusak --}}
                        <div id="boxKondisiRusak" class="hidden rounded-xl border border-red-100 bg-red-50/50 p-4">
                            <label for="kondisiRusak" class="mb-2 block text-sm font-semibold text-red-700">
                                <i class="fa-solid fa-circle-xmark mr-1"></i>
                                Rusak
                            </label>

                            <input type="number" id="kondisiRusak" placeholder="Masukkan jumlah" name="kondisi[Rusak]"
                                min="0"
                                class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-500/10">
                        </div>

                        {{-- Hilang --}}
                        <div id="boxKondisiHilang" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label for="kondisiHilang" class="mb-2 block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-box-open mr-1"></i>
                                Hilang
                            </label>

                            <input type="number" id="kondisiHilang" placeholder="Masukkan jumlah" name="kondisi[Hilang]"
                                min="0"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                    </div>

                    {{-- Tombol untuk menampilkan input kondisi lainnya --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" aria-controls="boxKondisiKurangBaik" aria-expanded="false"
                            class="btnKondisi inline-flex items-center rounded-lg border border-amber-200 text-amber-700 hover:bg-amber-50 px-3 py-2 text-xs font-semibold transition">
                            <i class="fas fa-plus mr-2"></i>
                            Kurang Baik
                        </button>
                        <button type="button" aria-controls="boxKondisiRusak" aria-expanded="false"
                            class="btnKondisi inline-flex items-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-3 py-2 text-xs font-semibold transition">
                            <i class="fas fa-plus mr-2"></i>
                            Rusak
                        </button>
                        <button type="button" aria-controls="boxKondisiHilang" aria-expanded="false"
                            class="btnKondisi inline-flex items-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 px-3 py-2 text-xs font-semibold transition">
                            <i class="fas fa-plus mr-2"></i>
                            Hilang
                        </button>
                    </div>
                </div>

                {{-- Keterangan masuk ke riwayat barang --}}
                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-semibold text-slate-700">
                        Keterangan
                        <span class="text-slate-400">(opsional)</span>
                    </label>
                    <input type="text" id="keterangan" name="keterangan" maxlength="255"
                        placeholder="Contoh: 2 kursi patah waktu retreat"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                    <p class="mt-1.5 text-xs text-slate-400">Dicatat di riwayat barang saat kondisinya berubah.</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-white px-6 py-4">

                <button type="button" onclick="closeModal('modalInventaris')"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" id="btnSimpanInventaris"
                    class="inline-flex items-center rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                    <i class="fas fa-floppy-disk mr-2"></i>
                    Simpan
                </button>

            </div>
        </form>
    </div>
</div>
