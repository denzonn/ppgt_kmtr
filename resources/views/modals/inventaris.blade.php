<div id="modalInventaris"
    class="modal-overlay fixed inset-0 z-50 hidden
           overflow-y-auto
           bg-black/40 backdrop-blur-sm
           opacity-0 transition-opacity duration-300">

    <div
        class="modal-content
               relative
               my-[5vh]
               mx-auto
               w-[95%] md:w-full
               max-w-3xl
               rounded-2xl
               bg-white
               shadow-2xl
               scale-95
               h-fit
               -translate-y-6
               transition-all duration-300">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b px-6 py-4">

            <h2 id="modalTitle" class="text-lg md:text-xl font-bold text-slate-800">
                Tambah Inventaris
            </h2>

            <button type="button" onclick="closeModal('modalInventaris')" class="w-10 h-10 rounded-lg hover:bg-slate-100">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <form id="formInventaris" action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <input type="hidden" id="inventaris_id" name="id">
            <input type="hidden" id="formMethod" value="POST">

            <div class="p-6 space-y-6">

                {{-- FOTO --}}
                <div class="flex justify-center">

                    <div class="text-center">

                        <label class="block text-xs md:text-sm font-semibold text-slate-700 mb-3">

                            Foto Inventaris
                            <span class="text-red-500">*</span>

                        </label>

                        <label for="foto"
                            class="group cursor-pointer flex flex-col items-center justify-center
    w-40 h-40 rounded-2xl border-2 border-dashed border-slate-300
    hover:border-primary hover:bg-slate-50 transition overflow-hidden p-2">

                            <img id="previewFoto" src="{{ asset('images/no-image.png') }}"
                                class="hidden w-full h-full object-cover rounded-xl">

                            <div id="placeholderFoto">

                                <i class="fa-solid fa-camera text-4xl text-slate-400 group-hover:text-primary"></i>

                                <p class="mt-3 text-sm text-slate-500">
                                    Klik untuk menambah foto
                                </p>

                            </div>

                        </label>

                        <input type="file" id="foto" name="foto" accept="image/*" capture="environment"
                            class="hidden">

                    </div>

                </div>

                <div class="grid grid-cols-2 gap-5">

                    {{-- Kode --}}
                    <div>

                        <label class="mb-2 block text-xs md:text-sm font-semibold">

                            Kode Inventaris
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" name="kode_inventaris" placeholder="Masukkan kode inventaris"
                            class="w-full rounded-xl border-slate-300">

                    </div>

                    {{-- Nama --}}
                    <div>

                        <label class="mb-2 block text-xs md:text-sm font-semibold">

                            Nama Inventaris
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" name="nama" placeholder="Masukkan nama inventaris"
                            class="w-full rounded-xl border-slate-300">

                    </div>

                    {{-- Harga --}}
                    <div>

                        <label class="mb-2 block text-xs md:text-sm font-semibold">

                            Harga
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" id="harga" name="harga" placeholder="Contoh: 50.000"
                            autocomplete="off" class="w-full rounded-xl border-slate-300">

                    </div>

                    {{-- Tanggal --}}
                    <div>

                        <label class="mb-2 block text-xs md:text-sm font-semibold">

                            Tanggal Perolehan
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="date" name="tanggal_perolehan" class="w-full rounded-xl border-slate-300">

                    </div>

                </div>

                {{-- KONDISI --}}
                <div>

                    <h3 class="font-semibold text-xs md:text-base text-slate-700 mb-4">

                        Jumlah Berdasarkan Kondisi
                        <span class="text-red-500">*</span>

                    </h3>

                    <div class="grid grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs md:text-sm mb-2">

                                Baik
                                <span class="text-red-500">*</span>

                            </label>

                            <input type="number" name="kondisi[Baik]" value="0" min="0"
                                class="w-full rounded-xl border-slate-300">

                        </div>

                        <div>

                            <label class="block text-xs md:text-sm mb-2">

                                Kurang Baik
                                <span class="text-red-500">*</span>

                            </label>

                            <input type="number" name="kondisi[Kurang Baik]" value="0" min="0"
                                class="w-full rounded-xl border-slate-300">

                        </div>

                        <div>

                            <label class="block text-xs md:text-sm mb-2">

                                Rusak
                                <span class="text-red-500">*</span>

                            </label>

                            <input type="number" name="kondisi[Rusak]" value="0" min="0"
                                class="w-full rounded-xl border-slate-300">

                        </div>

                        <div>

                            <label class="block text-xs md:text-sm mb-2">

                                Hilang
                                <span class="text-red-500">*</span>

                            </label>

                            <input type="number" name="kondisi[Hilang]" value="0" min="0"
                                class="w-full rounded-xl border-slate-300">

                        </div>

                    </div>

                </div>

            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 border-t px-6 py-4">

                <button type="button" onclick="closeModal('modalInventaris')"
                    class="rounded-xl border border-slate-300 px-5 py-2">

                    Batal

                </button>

                <button type="submit" class="rounded-xl bg-primary px-5 py-2 text-white hover:bg-primary_hover">

                    <i class="fa-solid fa-floppy-disk mr-2"></i>

                    Simpan

                </button>

            </div>

        </form>

    </div>

</div>

<script>
    $('#foto').on('change', function() {

        const file = this.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(e) {

            $('#previewFoto')
                .attr('src', e.target.result)
                .removeClass('hidden');

            $('#placeholderFoto').addClass('hidden');

        }

        reader.readAsDataURL(file);

    });

    $('#harga').on('input', function() {

        let value = $(this).val();

        // Hanya angka
        value = value.replace(/\D/g, '');

        // Format ribuan
        value = new Intl.NumberFormat('id-ID').format(value);

        $(this).val(value);

    });
</script>
