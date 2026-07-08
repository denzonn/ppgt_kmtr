<script>
    /* ===========================
|  Modal Helper
=========================== */

    window.showModal = function(id) {

        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex', 'justify-center');
        modal.classList.remove('items-center');

        requestAnimationFrame(() => {

            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');

            const content = modal.querySelector('.modal-content');

            content.classList.remove('scale-95', '-translate-y-6');
            content.classList.add('scale-100', 'translate-y-0');

        });

        document.body.classList.add('overflow-hidden');
    }

    window.closeModal = function(id) {

        const modal = document.getElementById(id);

        if (!modal) return;

        const content = modal.querySelector('.modal-content');

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');

        content.classList.remove('scale-100', 'translate-y-0');
        content.classList.add('scale-95', '-translate-y-6');

        setTimeout(() => {

            modal.classList.remove('block');
            modal.classList.add('hidden');

            document.body.classList.remove('overflow-hidden');

        }, 300);

    }

    window.formatTanggal = function(tanggal) {
        if (!tanggal) return '-';

        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    /* ===========================
    |  Close Modal
    |  - Klik Overlay
    |  - Tombol ESC
    =========================== */

    document.addEventListener('click', function(e) {

        if (e.target.classList.contains('modal-overlay')) {

            closeModal(e.target.id);

        }

    });

    document.addEventListener('keydown', function(e) {

        if (e.key !== 'Escape') return;

        document.querySelectorAll('.modal-overlay').forEach(function(modal) {

            if (!modal.classList.contains('hidden')) {

                modal.classList.remove('flex');
                modal.classList.add('hidden');

            }

        });

        document.body.classList.remove('overflow-hidden');

    });

    window.swalSuccess = function(message = 'Berhasil disimpan.') {

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: message,
            timer: 1800,
            showConfirmButton: false
        });

    }

    window.swalError = function(message = 'Terjadi kesalahan.') {

        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonText: 'OK'
        });

    }

    window.swalWarning = function(message = 'Periksa kembali data.') {

        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: message,
            confirmButtonText: 'OK'
        });

    }

    window.swalConfirm = function(
        title = 'Yakin?',
        text = 'Data akan dihapus.',
        callback = null
    ) {

        Swal.fire({

            icon: 'warning',

            title: title,

            text: text,

            showCancelButton: true,

            confirmButtonColor: '#DC2626',

            cancelButtonColor: '#94A3B8',

            confirmButtonText: 'Ya',

            cancelButtonText: 'Batal',

            reverseButtons: true

        }).then((result) => {

            if (result.isConfirmed && callback) {

                callback();

            }

        });

    }
</script>
