<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Konfigurasi global SweetAlert2
        window.SwalGlobal = Swal.mixin({
            customClass: {
                popup: 'rounded-2xl',
                title: 'text-lg font-bold text-slate-900',
                htmlContainer: 'text-sm text-slate-600',
                confirmButton: 'rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700',
                cancelButton: 'rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200',
            },
            buttonsStyling: false,
            reverseButtons: true,
            focusCancel: true,
        });

        document.addEventListener('submit', async function(event) {
            const form = event.target.closest('form[data-confirm-delete]');

            if (!form) return;

            // Izinkan submit kedua setelah konfirmasi berhasil.
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            // Cegah submit pertama sampai pengguna mengonfirmasi.
            event.preventDefault();

            const result = await window.SwalGlobal.fire({
                icon: 'warning',
                title: 'Hapus berita?',
                text: form.dataset.confirmMessage ||
                    'Data yang dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                allowOutsideClick: false,
                allowEscapeKey: true,
            });

            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
        });

    });
</script>

{{-- ================= SWEET ALERT ================= --}}
@if (session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
            @endif

            @if (session('warning'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: @json(session('warning')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: @json(session('info')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    html: `{!! collect($errors->all())->map(fn($e) => '• ' . $e)->implode('<br>') !!}`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#2563eb',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                });
            @endif

        });
    </script>
@endif

<script>
    window.App = window.App || {};

    App.loading = {

        show(message = 'Sedang memproses...') {

            Swal.fire({
                html: `
                <div class="flex flex-col items-center py-4">

                    <div class="relative w-14 h-14">

                        <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>

                        <div class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin"></div>

                    </div>

                    <h3 class="mt-5 text-lg font-semibold text-slate-800">
                        Mohon Tunggu
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        ${message}
                    </p>

                </div>
            `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                background: '#fff',
                width: 380,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl'
                }
            });

        },

        hide() {
            Swal.close();
        }

    };

    App.submitLoading = function(formSelector = 'form', buttonSelector = 'button[type="submit"]') {

        $(document).on('submit', formSelector, function() {

            // Kotak tautan dan teks alternatif CKEditor juga berupa form, tetapi tidak mengirim apa pun.
            if (this.closest('.ck')) return;

            App.loading.show('Sedang menyimpan data...');

        });

    };
</script>
