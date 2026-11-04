<div id="modalPeminjaman"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

    <div
        class="modal-content mx-auto my-6 w-full max-w-3xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i class="fas fa-right-left text-lg"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-lg font-bold text-slate-800">Detail Peminjaman</h2>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Rincian barang dan status pengembalian.
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeModal('modalPeminjaman')"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="modalBody" class="max-h-[70vh] overflow-y-auto p-6">

        </div>

    </div>

</div>

<script>
    // ==========================================
    // PREVIEW FOTO PENGEMBALIAN
    // ==========================================

    $(document).on('change', '#fotoPengembalian', function() {

        const file = this.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(e) {

            $('#previewPengembalian')
                .attr('src', e.target.result)
                .removeClass('hidden');

            $('#placeholderPengembalian').addClass('hidden');

        };

        reader.readAsDataURL(file);

    });

    // ==========================================
    // KONFIRMASI PENGEMBALIAN
    // ==========================================

    $(document).on('click', '.btnKonfirmasiPengembalian', function() {

        const id = $(this).data('id');
        const input = $('#fotoPengembalian')[0];

        if (!input || input.files.length === 0) {

            swalWarning('Foto pengembalian wajib diupload.');

            return;

        }

        swalConfirm(
            'Konfirmasi Pengembalian',
            'Pastikan semua inventaris sudah diterima.',
            function() {

                const formData = new FormData();

                formData.append('id', id);
                formData.append('foto_pengembalian', input.files[0]);
                formData.append('_token', @json(csrf_token()));

                $.ajax({

                    url: @json(route('peminjaman-admin.pengembalian')),

                    type: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    success: function(res) {

                        closeModal('modalPeminjaman');

                        loadData(page);

                        swalSuccess(res?.message || 'Inventaris berhasil dikembalikan.');

                    },

                    error: function(xhr) {

                        swalError(xhr.responseJSON?.message || 'Terjadi kesalahan.');

                    }

                });

            },
            {
                icon: 'question',
                confirmButtonText: 'Ya, Kembalikan',
                confirmButtonColor: '#16a34a'
            }
        );

    });
</script>
