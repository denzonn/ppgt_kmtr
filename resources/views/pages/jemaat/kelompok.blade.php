@extends('layouts.app')

@section('title', 'Kelompok')
@section('page', 'jemaat_kelompok')
@section('page_title', 'Kelompok')

@section('content')

    <div class="space-y-6">
        {{-- Ringkasan: menjawab "berapa anggota yang masih perlu dilengkapi?" --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-blue-700 p-6 text-white shadow-lg sm:p-8">
            <div aria-hidden="true" class="absolute -right-12 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-24 right-32 h-48 w-48 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-blue-100">{{ auth()->user()->jemaat?->nama }}</p>
                    <h2 class="mt-1 text-3xl font-bold"><span id="ringkasKelompok">0</span> Kelompok</h2>

                    <div class="mt-5 max-w-xl">
                        <div class="flex items-baseline justify-between gap-3 text-sm">
                            <span id="ringkasTeks" class="text-blue-50"></span>
                            <span id="ringkasPersen" class="font-bold"></span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/20">
                            <div id="ringkasBatang" class="h-full w-0 rounded-full bg-white transition-all duration-700"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                    <a id="ringkasBelum" href="{{ route('data_anggota.index', ['kelompok' => 'kosong']) }}"
                        class="hidden items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/25">
                        <i class="fa-solid fa-user-clock text-xs"></i>
                        <span id="ringkasBelumTeks"></span>
                    </a>
                    <button type="button" onclick="tambahKelompok()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary shadow-sm transition hover:bg-blue-50">
                        <i class="fas fa-plus"></i>
                        Tambah Kelompok
                    </button>
                </div>
            </div>
        </section>

        <div id="wadahCari" class="relative hidden max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="search" id="cariKelompok" placeholder="Cari kelompok..." aria-label="Cari kelompok"
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-4 focus:ring-primary/10">
        </div>

        <div id="daftarKelompok" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"></div>
    </div>

    <div id="modalKelompok"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-16 w-full max-w-md scale-95 -translate-y-6 overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300">
            <div class="relative bg-gradient-to-br from-primary to-blue-700 px-6 pb-12 pt-6 text-white">
                <h3 id="modalKelompokTitle" class="text-lg font-bold">Tambah Kelompok</h3>
                <p class="mt-0.5 text-sm text-blue-100">Cukup isi nama kelompoknya.</p>
                <button type="button" onclick="closeModal('modalKelompok')" aria-label="Tutup"
                    class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formKelompok" class="px-6 pb-6">
                <input type="hidden" id="kelompok_id">

                <div aria-hidden="true" id="kelompokMonogramForm"
                    class="relative -mt-8 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-2xl font-bold text-primary shadow-lg ring-4 ring-white">?</div>

                <label for="kelompok_nama" class="mb-2 mt-5 block text-sm font-semibold text-slate-700">Nama Kelompok</label>
                <input type="text" id="kelompok_nama" required maxlength="100" placeholder="Contoh: Kelompok Hermon"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalKelompok')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanKelompok"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const kelompokUrl = @json(route('jemaat.kelompok.index'));
        const kelompokDataUrl = @json(route('jemaat.kelompok.getData'));
        const anggotaUrl = @json(route('data_anggota.index'));
        const csrfKelompok = '{{ csrf_token() }}';

        let kelompokItems = [];

        function escapeKelompok(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        /** "Kelompok Hermon" menjadi "H", bukan "K" yang sama untuk semua kartu. */
        function monogram(nama) {
            const inti = String(nama ?? '').replace(/^\s*kelompok\s+/i, '').trim();

            return (inti.charAt(0) || '?').toUpperCase();
        }

        function inisial(nama) {
            return String(nama ?? '').trim().split(/\s+/).slice(0, 2).map(k => k.charAt(0)).join('').toUpperCase();
        }

        function pesanGagalKelompok(xhr, bawaan) {
            const errors = xhr.responseJSON?.errors;

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || bawaan)
            });
        }

        function kartuKelompok(item) {
            const lainnya = item.anggota_count - item.contoh.length;
            const persenLaki = item.anggota_count ? Math.round(item.laki / item.anggota_count * 100) : 0;
            const persenPerempuan = item.anggota_count ? Math.round(item.perempuan / item.anggota_count * 100) : 0;
            const nama = escapeKelompok(item.nama);

            const isi = item.anggota_count === 0 ?
                '<p class="rounded-xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-500">Belum ada anggota di kelompok ini.</p>' : `
                <div class="flex items-center justify-between gap-3">
                    <div class="flex -space-x-2" aria-hidden="true">
                        ${item.contoh.map(n => `<span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-[11px] font-bold text-slate-600 ring-2 ring-white" title="${escapeKelompok(n)}">${escapeKelompok(inisial(n))}</span>`).join('')}
                        ${lainnya > 0 ? `<span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white ring-2 ring-white">+${lainnya}</span>` : ''}
                    </div>
                    <p class="text-right text-2xl font-bold leading-none text-slate-800">${Number(item.anggota_count).toLocaleString('id-ID')}<span class="mt-1 block text-xs font-normal text-slate-500">anggota</span></p>
                </div>
                <div class="mt-4">
                    <div class="flex h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="bg-primary" style="width: ${persenLaki}%"></div>
                        <div class="bg-primary/30" style="width: ${persenPerempuan}%"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-slate-500">
                        <span><i class="fas fa-mars mr-1 text-primary"></i>${item.laki} laki-laki</span>
                        <span><i class="fas fa-venus mr-1 text-primary/50"></i>${item.perempuan} perempuan</span>
                    </div>
                </div>`;

            return `
                <article class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative h-20 bg-primary/10">
                        <div class="absolute right-3 top-3 flex gap-1.5">
                            <button onclick="editKelompok(${item.id})" title="Edit" aria-label="Edit ${nama}"
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm transition hover:text-primary">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button onclick="hapusKelompok(${item.id})" title="Hapus" aria-label="Hapus ${nama}"
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm transition hover:text-red-600">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col px-5 pb-5">
                        <div aria-hidden="true" class="relative -mt-8 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-2xl font-bold shadow-md ring-4 ring-white text-primary">${escapeKelompok(monogram(item.nama))}</div>
                        <h3 class="mb-4 mt-3 break-words text-lg font-bold text-slate-800">${nama}</h3>

                        <div class="flex-1">${isi}</div>

                        <a href="${anggotaUrl}?kelompok=${item.id}"
                            class="mt-5 inline-flex items-center gap-1.5 border-t border-slate-100 pt-4 text-sm font-semibold text-primary">
                            Lihat anggota
                            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </article>`;
        }

        function kartuTambah() {
            return `
                <button type="button" onclick="tambahKelompok()"
                    class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-3xl border-2 border-dashed border-slate-300 bg-white/50 p-6 text-slate-500 transition hover:border-primary hover:bg-white hover:text-primary">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl"><i class="fas fa-plus"></i></span>
                    <span class="text-sm font-semibold">Tambah Kelompok</span>
                </button>`;
        }

        function renderDaftarKelompok() {
            const cari = $('#cariKelompok').val().trim().toLowerCase();

            if (kelompokItems.length === 0) {
                $('#daftarKelompok').html(`
                    <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary"><i class="fa-solid fa-users-rectangle text-2xl"></i></span>
                        <h3 class="mt-4 text-lg font-bold text-slate-800">Belum ada kelompok</h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">Buat kelompok pertama, misalnya Kelompok Hermon, lalu pilih kelompok untuk setiap anggota.</p>
                        <button type="button" onclick="tambahKelompok()" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"><i class="fas fa-plus"></i> Tambah Kelompok</button>
                    </div>`);
                return;
            }

            const hasil = kelompokItems.filter(k => k.nama.toLowerCase().includes(cari));

            $('#daftarKelompok').html(hasil.length === 0 ?
                '<div class="col-span-full rounded-3xl border border-slate-200 bg-white py-14 text-center text-sm text-slate-500">Kelompok tidak ditemukan.</div>' :
                hasil.map(kartuKelompok).join('') + (cari ? '' : kartuTambah()));
        }

        function renderRingkasan(ringkasan) {
            const { total_anggota: total, belum_berkelompok: belum } = ringkasan;
            const sudah = total - belum;
            const persen = total ? Math.floor(sudah / total * 100) : 0;

            $('#ringkasKelompok').text(kelompokItems.length);
            $('#ringkasTeks').text(`${sudah.toLocaleString('id-ID')} dari ${total.toLocaleString('id-ID')} anggota sudah punya kelompok`);
            $('#ringkasPersen').text(sudah > 0 && persen === 0 ? '<1%' : `${persen}%`);
            $('#ringkasBatang').css('width', `${sudah > 0 ? Math.max(persen, 1) : 0}%`);
            $('#ringkasBelum').toggleClass('hidden', belum === 0).toggleClass('inline-flex', belum > 0);
            $('#ringkasBelumTeks').text(`${belum.toLocaleString('id-ID')} belum punya kelompok`);
        }

        function loadKelompok() {
            $.get(kelompokDataUrl).done(function(res) {
                kelompokItems = res.data;
                renderRingkasan(res.ringkasan);
                $('#wadahCari').toggleClass('hidden', kelompokItems.length < 4);
                renderDaftarKelompok();
            }).fail(function() {
                $('#daftarKelompok').html('<div class="col-span-full py-12 text-center text-sm text-red-600">Gagal memuat data kelompok.</div>');
            });
        }

        function tampilkanMonogramForm() {
            $('#kelompokMonogramForm').text(monogram($('#kelompok_nama').val()));
        }

        function tambahKelompok() {
            $('#formKelompok')[0].reset();
            $('#kelompok_id').val('');
            $('#modalKelompokTitle').text('Tambah Kelompok');
            tampilkanMonogramForm();
            showModal('modalKelompok');
        }

        function editKelompok(id) {
            $('#kelompok_id').val(id);
            $('#kelompok_nama').val(kelompokItems.find(k => k.id === id).nama);
            $('#modalKelompokTitle').text('Edit Kelompok');
            tampilkanMonogramForm();
            showModal('modalKelompok');
        }

        $('#kelompok_nama').on('input', tampilkanMonogramForm);
        $('#cariKelompok').on('input', renderDaftarKelompok);

        $('#formKelompok').on('submit', function(e) {
            e.preventDefault();

            const id = $('#kelompok_id').val();
            const btn = $('#btnSimpanKelompok').prop('disabled', true);

            $.ajax({
                url: id ? `${kelompokUrl}/${id}` : kelompokUrl,
                type: 'POST',
                data: {
                    _token: csrfKelompok,
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#kelompok_nama').val()
                }
            }).done(function(res) {
                closeModal('modalKelompok');
                loadKelompok();
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                pesanGagalKelompok(xhr, 'Kelompok gagal disimpan.');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        function hapusKelompok(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus kelompok ini?',
                text: kelompokItems.find(k => k.id === id).nama,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${kelompokUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfKelompok,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadKelompok();
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    pesanGagalKelompok(xhr, 'Kelompok gagal dihapus.');
                });
            });
        }

        loadKelompok();
    </script>
@endpush
