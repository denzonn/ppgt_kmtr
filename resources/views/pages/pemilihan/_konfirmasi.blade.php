{{--
    Dialog konfirmasi bergaya aplikasi untuk form dengan data-konfirmasi.
    Atribut: data-konfirmasi (judul), data-konfirmasi-teks, data-konfirmasi-ok, data-konfirmasi-ikon (nama ikon Font Awesome), data-bahaya.
--}}
@push('addon-script')
    <script>
        window.konfirmasiPemilihan = function(opsi) {
            const bahaya = !!opsi.bahaya;
            const warnaIkon = bahaya ? 'bg-red-50 text-red-600' : 'bg-primary/10 text-primary';

            return Swal.fire({
                html: `
                    <div class="flex flex-col items-center text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl text-xl ${warnaIkon}"><i class="fas ${opsi.ikon || (bahaya ? 'fa-trash' : 'fa-circle-question')}"></i></span>
                        <h3 class="mt-4 text-lg font-bold text-slate-900"></h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500"></p>
                    </div>`,
                didRender: function(popup) {
                    // Diisi lewat textContent supaya teks dari data tidak dibaca sebagai HTML.
                    popup.querySelector('h3').textContent = opsi.judul;
                    popup.querySelector('p').textContent = opsi.teks || '';
                },
                showCancelButton: true,
                confirmButtonText: opsi.ok || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: bahaya,
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-3xl px-2 pb-6 pt-4',
                    htmlContainer: 'm-0',
                    actions: 'mt-6 flex w-full flex-col-reverse gap-2 px-6 sm:flex-row sm:justify-center',
                    // Tanda ! diperlukan karena gaya SweetAlert global di includes/style memakai !important.
                    confirmButton: `!rounded-xl !px-5 !py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-4 ${bahaya ? '!bg-red-600 hover:!bg-red-700 focus:ring-red-200' : '!bg-primary hover:opacity-90 focus:ring-primary/20'}`,
                    cancelButton: '!rounded-xl border border-slate-200 bg-white !px-5 !py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200',
                },
            });
        };

        document.addEventListener('submit', function(event) {
            const form = event.target.closest('form[data-konfirmasi]');
            if (!form || form.dataset.sudahYakin === '1') return;

            event.preventDefault();
            konfirmasiPemilihan({
                judul: form.dataset.konfirmasi,
                teks: form.dataset.konfirmasiTeks,
                ok: form.dataset.konfirmasiOk,
                ikon: form.dataset.konfirmasiIkon,
                bahaya: form.hasAttribute('data-bahaya'),
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;
                form.dataset.sudahYakin = '1';
                form.requestSubmit(event.submitter);
            });
        });

        // Penghitung dan tombol pilih semua pada daftar calon.
        document.querySelectorAll('[data-daftar-calon]').forEach(function(daftar) {
            const kotak = daftar.querySelectorAll('input[type=checkbox]');
            const hitung = () => daftar.querySelector('[data-jumlah-calon]').textContent = [...kotak].filter(k => k.checked).length;
            kotak.forEach(k => k.addEventListener('change', hitung));
            daftar.querySelectorAll('[data-centang]').forEach(function(tombol) {
                tombol.addEventListener('click', function() {
                    kotak.forEach(k => k.checked = tombol.dataset.centang === '1');
                    hitung();
                });
            });
        });

        // Angka pemilih diperbarui sendiri selama pemungutan dibuka.
        const putaranAktif = document.querySelector('[data-putaran-aktif]');
        if (putaranAktif) {
            setInterval(function() {
                fetch(putaranAktif.dataset.url, { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(function(data) {
                        if (data.status !== 'dibuka') return location.reload();
                        const bar = putaranAktif.querySelector('[data-bar]');
                        putaranAktif.querySelector('[data-sudah]').textContent = data.sudah;
                        putaranAktif.querySelector('[data-belum]').textContent = data.belum.join(', ') || '-';
                        bar.style.width = (Number(bar.dataset.total) ? Math.round(data.sudah / Number(bar.dataset.total) * 100) : 0) + '%';
                    })
                    .catch(() => {});
            }, 4000);
        }
    </script>
@endpush
