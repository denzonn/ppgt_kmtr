<div id="modalPeminjaman"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm
    opacity-0 transition-opacity duration-300">

    <div
        class="modal-content w-full max-w-3xl rounded-2xl bg-white shadow-2xl
        scale-95 -translate-y-6 transition-all duration-300">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b px-6 py-4">

            <h2 id="modalTitle" class="text-xl font-bold text-slate-800">
                Detail Peminjaman
            </h2>

            <button type="button" onclick="closeModal('modalPeminjaman')" class="w-10 h-10 rounded-lg hover:bg-slate-100">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <div id="modalBody" class="p-6 max-h-[75vh] overflow-y-auto">

        </div>

    </div>

</div>

<script>
    $(document).on('change', '#fotoPengembalian', function() {

        let file = this.files[0];

        if (!file) return;

        let reader = new FileReader();

        reader.onload = function(e) {

            $('#previewPengembalian')
                .attr('src', e.target.result)
                .removeClass('hidden');

            $('#placeholderPengembalian').hide();

        }

        reader.readAsDataURL(file);

    });

    $(document).on('click', '.btnKonfirmasiPengembalian', function() {

        let id = $(this).data('id');

        if ($('#fotoPengembalian')[0].files.length == 0) {

            swalWarning('Foto pengembalian wajib diupload.');

            return;

        }

        swalConfirm(

            'Konfirmasi Pengembalian',

            'Pastikan semua inventaris sudah diterima.',

            function() {

                let formData = new FormData();

                formData.append('id', id);

                formData.append(
                    'foto_pengembalian',
                    $('#fotoPengembalian')[0].files[0]
                );

                formData.append(
                    '_token',
                    "{{ csrf_token() }}"
                );

                $.ajax({

                    url: "{{ route('peminjaman-admin.pengembalian') }}",

                    type: "POST",

                    data: formData,

                    processData: false,

                    contentType: false,

                    success: function() {

                        swalSuccess('Inventaris berhasil dikembalikan.');

                        closeModal('modalPeminjaman');

                        loadData(page);

                    },

                    error: function(xhr) {

                        swalError(xhr.responseJSON?.message ?? 'Terjadi kesalahan.');

                    }

                });

            }

        );

    });
</script>
