<div id="modalInventaris"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-black/40 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-300">

    <div
        class="modal-content relative mx-auto my-[5vh] h-fit w-[95%] max-w-3xl -translate-y-6 scale-95 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 md:px-6">
            <div>
                <h2 id="modalTitle" class="text-lg font-bold text-slate-800 md:text-xl">
                    Tambah Inventaris
                </h2>
                <p class="mt-1 text-xs text-slate-500 md:text-sm">
                    Lengkapi informasi inventaris di bawah ini.
                </p>
            </div>

            <button type="button" onclick="closeModal('modalInventaris')"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formInventaris" action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <input type="hidden" id="inventaris_id" name="id">
            <input type="hidden" id="formMethod" value="POST">

            <div class="max-h-[70vh] space-y-6 overflow-y-auto p-5 md:p-6">

                {{-- Foto --}}
                <div class="flex justify-center">
                    <div class="text-center">
                        <label class="mb-3 block text-sm font-semibold text-slate-700">
                            Foto Inventaris
                            <span class="text-red-500">*</span>
                        </label>

                        <label for="foto"
                            class="group flex h-36 w-36 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 p-2 transition hover:border-primary hover:bg-slate-50 md:h-40 md:w-40">

                            <img id="previewFoto" src="{{ asset('images/no-image.png') }}" alt="Preview foto inventaris"
                                class="hidden h-full w-full rounded-xl object-cover">

                            <div id="placeholderFoto" class="text-center">
                                <i
                                    class="fa-solid fa-camera text-3xl text-slate-400 transition group-hover:text-primary md:text-4xl"></i>

                                <p class="mt-3 text-xs text-slate-500 md:text-sm">
                                    Klik untuk menambah foto
                                </p>
                            </div>
                        </label>

                        <input type="file" id="foto" name="foto"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" capture="environment"
                            required class="hidden">

                        <p class="mt-2 text-xs text-slate-400">
                            JPG, PNG, GIF, SVG. Maksimal 2 MB.
                        </p>
                    </div>
                </div>

                {{-- Informasi Inventaris --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Jenis --}}
                    <div class="md:col-span-2">
                        <label for="jenis" class="mb-2 block text-sm font-semibold text-slate-700">
                            Jenis Inventaris
                            <span class="text-red-500">*</span>
                        </label>

                        <select id="jenis" name="jenis" required
                            class="w-full rounded-xl border border-slate-300 bg-white text-sm focus:border-primary focus:ring-primary md:text-base">

                            <option value="">Pilih Jenis Inventaris</option>

                            <option value="PAK">
                                Perlengkapan Administrasi Kesekretariatan
                            </option>

                            <option value="PDP">
                                Perlengkapan Dapur Perkakas
                            </option>

                            <option value="PPL">
                                Perlengkapan Penunjang Lainnya
                            </option>
                        </select>

                        <p class="mt-2 text-xs text-slate-500">
                            Kode inventaris akan dibuat otomatis berdasarkan jenis yang dipilih.
                        </p>
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label for="nama" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nama Inventaris
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="nama" name="nama" required maxlength="255"
                            placeholder="Masukkan nama inventaris"
                            class="w-full rounded-xl border border-slate-300 text-sm focus:border-primary focus:ring-primary md:text-base">
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label for="harga" class="mb-2 block text-sm font-semibold text-slate-700">
                            Harga
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">
                                Rp
                            </span>

                            <input type="text" id="harga" name="harga" required inputmode="numeric"
                                autocomplete="off" placeholder="Contoh: 50.000"
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-12 pr-4 text-sm focus:border-primary focus:ring-primary md:text-base">
                        </div>
                    </div>

                    {{-- Tanggal Perolehan --}}
                    <div class="md:col-span-2">
                        <label for="tanggal_perolehan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Tanggal Perolehan
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="date" id="tanggal_perolehan" name="tanggal_perolehan" required
                            class="w-full rounded-xl border border-slate-300 text-sm focus:border-primary focus:ring-primary md:text-base">
                    </div>
                </div>

                {{-- Kondisi --}}
                <div>
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-slate-800 md:text-base">
                            Jumlah Berdasarkan Kondisi
                            <span class="text-red-500">*</span>
                        </h3>

                        <p class="mt-1 text-xs text-slate-500">
                            Masukkan jumlah barang untuk setiap kondisi. Isi 0 jika tidak ada.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        {{-- Baik --}}
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                            <label for="kondisiBaik" class="mb-2 block text-sm font-semibold text-emerald-700">
                                <i class="fa-solid fa-circle-check mr-1"></i>
                                Baik
                            </label>

                            <input type="number" id="kondisiBaik" name="kondisi[Baik]" value="0" min="0"
                                required
                                class="w-full rounded-lg border border-emerald-200 bg-white text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- Kurang Baik --}}
                        <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-4">
                            <label for="kondisiKurangBaik" class="mb-2 block text-sm font-semibold text-amber-700">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                Kurang Baik
                            </label>

                            <input type="number" id="kondisiKurangBaik" name="kondisi[Kurang Baik]" value="0"
                                min="0" required
                                class="w-full rounded-lg border border-amber-200 bg-white text-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>

                        {{-- Rusak --}}
                        <div class="rounded-xl border border-red-100 bg-red-50/50 p-4">
                            <label for="kondisiRusak" class="mb-2 block text-sm font-semibold text-red-700">
                                <i class="fa-solid fa-circle-xmark mr-1"></i>
                                Rusak
                            </label>

                            <input type="number" id="kondisiRusak" name="kondisi[Rusak]" value="0"
                                min="0" required
                                class="w-full rounded-lg border border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500">
                        </div>

                        {{-- Hilang --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label for="kondisiHilang" class="mb-2 block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-box-open mr-1"></i>
                                Hilang
                            </label>

                            <input type="number" id="kondisiHilang" name="kondisi[Hilang]" value="0"
                                min="0" required
                                class="w-full rounded-lg border border-slate-300 bg-white text-sm focus:border-primary focus:ring-primary">
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4 md:px-6">

                <button type="button" onclick="closeModal('modalInventaris')"
                    class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 md:px-5 md:text-base">
                    Batal
                </button>

                <button type="submit"
                    class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary_hover disabled:cursor-not-allowed disabled:opacity-60 md:px-6 md:text-base">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Simpan
                </button>

            </div>
        </form>
    </div>
</div>

<script>
    // ==========================================
    // PREVIEW FOTO
    // ==========================================

    $('#foto').on('change', function() {
        const file = this.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/gif',
            'image/svg+xml'
        ];

        if (!allowedTypes.includes(file.type)) {
            $(this).val('');
            swalError('Format foto tidak didukung.');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            $(this).val('');
            swalError('Ukuran foto maksimal 2 MB.');
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            $('#previewFoto')
                .attr('src', e.target.result)
                .removeClass('hidden');

            $('#placeholderFoto').addClass('hidden');
        };

        reader.readAsDataURL(file);
    });

    // ==========================================
    // FORMAT HARGA
    // ==========================================

    $('#harga').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');

        $(this).val(
            value ? new Intl.NumberFormat('id-ID').format(Number(value)) : ''
        );
    });
</script>
