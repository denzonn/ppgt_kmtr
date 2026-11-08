<div id="modalImportAnggota"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

    <div
        class="modal-content mx-auto my-2 w-full max-w-2xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="font-bold text-slate-800">Import Data Anggota</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Import data anggota dari file Excel.
                </p>
            </div>

            <button type="button" id="btnTutupImport"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formImportAnggota" enctype="multipart/form-data" class="space-y-5 p-6">
            @csrf

            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 mt-0">
                <div class="flex gap-3">
                    <i class="fas fa-info-circle mt-0.5 text-blue-600"></i>

                    <div class="text-sm text-blue-800">
                        <p class="font-semibold">Informasi Import</p>

                        <ul class="mt-2 list-inside list-disc space-y-1 text-blue-700">
                            <li>Nomor anggota menggunakan nomor asli dari Excel.</li>
                            <li>Status anggota otomatis menjadi Aktif.</li>
                            <li>Status wilayah otomatis menjadi Dalam Wilayah.</li>
                            <li>Nomor anggota yang sudah terdaftar akan dilewati.</li>
                            <li>Jemaat harus sudah tersedia di database.</li>
                            <li>Kolom <b>kelompok</b> boleh diisi. Kalau namanya belum terdaftar, anggota masuk tanpa kelompok.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <label for="fileImportAnggota" class="mb-2 block text-sm font-semibold text-slate-700">
                    Pilih File Excel
                </label>

                <input type="file" name="file" id="fileImportAnggota" accept=".xlsx,.xls,.csv" required
                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:font-semibold file:text-white">

                <p class="mt-2 text-xs text-slate-400">
                    Format XLSX, XLS, atau CSV. Maksimal 10 MB.
                </p>
            </div>

            <div id="hasilImportAnggota" class="hidden rounded-xl border border-slate-200 p-4">
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" id="btnBatalImport"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" id="btnProsesImport"
                    class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <i class="fas fa-upload mr-1"></i>
                    Mulai Import
                </button>
            </div>

        </form>
    </div>
</div>
