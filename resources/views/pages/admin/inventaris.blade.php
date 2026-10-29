@extends('layouts.app')

@section('title', 'Inventaris')
@section('page', 'inventaris')
@section('page_title', 'Inventaris')

@section('content')
    <div class="relative flex w-full min-w-0 max-w-full flex-1 flex-col gap-6 pb-10">

        {{-- Header --}}
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800 md:text-2xl">
                    Data Inventaris
                </h2>
                <p class="mt-1 text-xs text-slate-500 md:text-base">
                    Kelola seluruh inventaris organisasi.
                </p>
            </div>

            <button type="button" onclick="tambahInventaris()"
                class="absolute bottom-0 right-0 flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white hover:bg-primary_hover md:hidden">
                <i class="fa-solid fa-plus"></i>
            </button>

            <button type="button" onclick="tambahInventaris()"
                class="hidden shrink-0 rounded-xl bg-primary px-5 py-3 text-white transition hover:bg-primary_hover md:block">
                <i class="fa-solid fa-plus mr-2"></i>
                Tambah Inventaris
            </button>
        </div>

        {{-- Pencarian --}}
        <div class="w-full min-w-0 rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:rounded-2xl md:p-5">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="search" type="text" placeholder="Cari kode, nama, atau jenis inventaris..."
                    class="w-full min-w-0 rounded-xl border border-slate-300 py-3 pl-11 pr-4 text-xs focus:border-primary focus:ring-primary md:text-base">
            </div>
        </div>

        {{-- Container Data --}}
        <div id="tableContainer" class="w-full min-w-0 max-w-full">

            {{-- Card Tabel Desktop --}}
            <div
                class="hidden w-full min-w-0 max-w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">

                {{-- Scroll horizontal hanya pada pembungkus tabel ini --}}
                <div class="table-scrollbar w-full min-w-0 max-w-full overflow-x-auto overscroll-x-contain">
                    <table class="w-full min-w-[900px] border-collapse text-left text-sm">

                        <thead class="bg-slate-50">
                            <tr class="whitespace-nowrap text-xs text-slate-600 md:text-sm">
                                <th class="min-w-[90px] px-4 py-4 md:px-6">Foto</th>
                                <th class="min-w-[160px] px-4 py-4 md:px-6">Kode Inventaris</th>
                                <th class="min-w-[280px] px-4 py-4 md:px-6">Informasi Inventaris</th>
                                <th class="min-w-[190px] px-4 py-4 md:px-6">Harga & Perolehan</th>
                                <th class="min-w-[110px] px-4 py-4 text-center md:px-6">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableData" class="text-xs md:text-sm">
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-500">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                                    Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Footer tabel --}}
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-arrows-left-right mr-1"></i>
                        Geser tabel ke samping untuk melihat kolom lainnya.
                    </p>
                    <p class="shrink-0 text-xs text-slate-400">
                        Data Inventaris
                    </p>
                </div>
            </div>

            {{-- Pagination Desktop --}}
            <div id="pagination" class="mt-5 hidden md:block"></div>

            {{-- Card Mobile --}}
            <div id="mobileContainer" class="min-w-0 space-y-4 md:hidden"></div>

            {{-- Pagination Mobile --}}
            <div id="paginationMobile" class="mt-5 md:hidden"></div>
        </div>

        {{-- Data Kosong --}}
        <div id="emptyState" class="hidden w-full min-w-0">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col items-center px-4 py-14 text-center md:py-20">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 md:h-24 md:w-24">
                        <i class="fa-solid fa-box-open text-2xl text-slate-400 md:text-4xl"></i>
                    </div>

                    <h3 class="mt-6 text-base font-bold text-slate-700 md:text-2xl">
                        Belum Ada Inventaris
                    </h3>

                    <p class="mt-2 max-w-md text-xs text-slate-500 md:text-sm">
                        Data inventaris masih kosong. Silakan tambahkan inventaris pertama untuk mulai mengelola aset
                        organisasi.
                    </p>

                    <button type="button" onclick="tambahInventaris()"
                        class="mt-8 rounded-xl bg-primary px-6 py-3 text-xs text-white transition hover:bg-primary_hover md:text-base">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Inventaris
                    </button>
                </div>
            </div>
        </div>

    </div>

    @include('modals.inventaris')
@endsection

@push('addon-script')
    <script>
        const inventarisUrl = @json(route('inventaris'));
        const inventarisStoreUrl = @json(route('inventaris.store'));
        const inventarisDataUrl = @json(route('inventaris.getData'));
        const csrfToken = @json(csrf_token());
        const noImageUrl = @json(asset('images/no-image.png'));

        const jenisInventaris = {
            PAK: 'Perlengkapan Administrasi Kesekretariatan',
            PDP: 'Perlengkapan Dapur Perkakas',
            PPL: 'Perlengkapan Penunjang Lainnya'
        };

        let page = 1;
        let searchTimer;
        let requestInventaris = null;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // ==========================================
        // FORMAT DATA
        // ==========================================

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [char];
            });
        }

        function formatTanggal(tanggal) {
            if (!tanggal) return '-';

            const parts = String(tanggal).substring(0, 10).split('-');

            if (parts.length !== 3) return tanggal;

            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        function formatRupiah(angka) {
            return Number(angka || 0).toLocaleString('id-ID');
        }

        function fotoUrl(foto) {
            if (!foto) return noImageUrl;

            return @json(asset('storage')) + '/' + String(foto)
                .split('/')
                .map(encodeURIComponent)
                .join('/');
        }

        function renderJenis(jenis) {
            const kode = String(jenis || '').toUpperCase();
            const nama = jenisInventaris[kode] || jenis || '-';

            const warna = {
                PAK: 'bg-blue-50 text-blue-700 ring-blue-200',
                PDP: 'bg-amber-50 text-amber-700 ring-amber-200',
                PPL: 'bg-emerald-50 text-emerald-700 ring-emerald-200'
            };

            return `
                <div class="mt-1.5 flex min-w-0 items-start gap-2">
                    <span class="inline-flex shrink-0 items-center rounded-md px-2 py-1 text-[10px] font-bold ring-1 ring-inset ${warna[kode] || 'bg-slate-50 text-slate-600 ring-slate-200'}">
                        ${escapeHtml(kode || '-')}
                    </span>
                    <span class="min-w-0 whitespace-normal break-words text-xs leading-5 text-slate-500">
                        ${escapeHtml(nama)}
                    </span>
                </div>
            `;
        }

        // ==========================================
        // LOAD DATA
        // ==========================================

        $(document).ready(function() {
            loadData();

            $('#search').on('input', function() {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(function() {
                    loadData(1);
                }, 400);
            });
        });

        function loadData(currentPage = 1) {
            page = currentPage;

            if (requestInventaris) {
                requestInventaris.abort();
            }

            $('#emptyState').addClass('hidden');
            $('#tableContainer').removeClass('hidden');

            $('#tableData').html(`
                <tr>
                    <td colspan="5" class="py-10 text-center text-slate-500">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Memuat data...
                    </td>
                </tr>
            `);

            $('#mobileContainer').html(`
                <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary"></i>
                    <p class="mt-3 text-sm text-slate-500">Memuat data...</p>
                </div>
            `);

            requestInventaris = $.ajax({
                url: inventarisDataUrl,
                method: 'GET',
                data: {
                    page: page,
                    search: $('#search').val()
                },

                success: function(res) {
                    if (res.total === 0 && !$('#search').val().trim()) {
                        $('#tableContainer').addClass('hidden');
                        $('#emptyState').removeClass('hidden');
                        return;
                    }

                    $('#emptyState').addClass('hidden');
                    $('#tableContainer').removeClass('hidden');

                    let desktopHtml = '';
                    let mobileHtml = '';

                    if (!res.data || res.data.length === 0) {
                        desktopHtml = `
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300"></i>
                                    <h3 class="mt-4 font-semibold text-slate-700">
                                        Data tidak ditemukan
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Coba gunakan kata kunci lain.
                                    </p>
                                </td>
                            </tr>
                        `;

                        mobileHtml = `
                            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                                <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300"></i>
                                <h3 class="mt-4 font-semibold text-slate-700">
                                    Data tidak ditemukan
                                </h3>
                                <p class="mt-2 text-sm text-slate-500">
                                    Coba gunakan kata kunci lain.
                                </p>
                            </div>
                        `;
                    } else {
                        $.each(res.data, function(i, item) {
                            const nama = escapeHtml(item.nama);
                            const kode = escapeHtml(item.kode_inventaris);
                            const jenis = renderJenis(item.jenis);
                            const harga = formatRupiah(item.harga);
                            const tanggal = escapeHtml(formatTanggal(item.tanggal_perolehan));
                            const gambar = escapeHtml(fotoUrl(item.foto));
                            const id = Number(item.id);

                            // Desktop
                            desktopHtml += `
                                <tr class="border-t transition hover:bg-slate-50">
                                    <td class="px-4 py-3 md:px-6">
                                        <img src="${gambar}"
                                            alt="${nama}"
                                            onerror="this.onerror=null;this.src=${JSON.stringify(noImageUrl)}"
                                            class="h-12 w-12 rounded-xl border border-slate-200 object-cover md:h-14 md:w-14">
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700 md:px-6">
                                        ${kode}
                                    </td>

                                    <td class="px-4 py-3 md:px-6">
                                        <div class="min-w-0 max-w-[360px]">
                                            <p class="break-words font-semibold leading-5 text-slate-800">
                                                ${nama}
                                            </p>
                                            ${jenis}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 md:px-6">
                                        <div class="flex min-w-[150px] flex-col gap-1">
                                            <span class="whitespace-nowrap font-semibold text-slate-800">
                                                Rp ${harga}
                                            </span>
                                            <span class="whitespace-nowrap text-xs text-slate-500">
                                                <i class="fa-regular fa-calendar mr-1.5"></i>${tanggal}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 md:px-6">
                                        <div class="flex justify-center gap-2">
                                            <button type="button"
                                                onclick="editInventaris(${id})"
                                                title="Edit"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <button type="button"
                                                onclick="hapusInventaris(${id})"
                                                title="Hapus"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;

                            // Mobile
                            mobileHtml += `
                                <div class="w-full min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <img src="${gambar}"
                                            alt="${nama}"
                                            onerror="this.onerror=null;this.src=${JSON.stringify(noImageUrl)}"
                                            class="h-20 w-20 shrink-0 rounded-xl border border-slate-200 object-cover">

                                        <div class="min-w-0 flex-1">
                                            <h3 class="break-words font-semibold text-slate-800">
                                                ${nama}
                                            </h3>

                                            <p class="mt-1 break-all text-xs font-medium text-slate-500">
                                                ${kode}
                                            </p>

                                            <div class="mt-2">
                                                ${jenis}
                                            </div>

                                            <p class="mt-2 font-bold text-primary">
                                                Rp ${harga}
                                            </p>

                                            <p class="mt-2 text-xs text-slate-500">
                                                <i class="fa-regular fa-calendar mr-1"></i>
                                                ${tanggal}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-3">
                                        <button type="button"
                                            onclick="editInventaris(${id})"
                                            class="flex h-9 items-center gap-2 rounded-lg bg-blue-50 px-3 text-sm text-blue-600 hover:bg-blue-100">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </button>

                                        <button type="button"
                                            onclick="hapusInventaris(${id})"
                                            class="flex h-9 items-center gap-2 rounded-lg bg-red-50 px-3 text-sm text-red-600 hover:bg-red-100">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    $('#tableData').html(desktopHtml);
                    $('#mobileContainer').html(mobileHtml);

                    renderPagination(res);
                },

                error: function(xhr, status) {
                    if (status === 'abort') return;

                    $('#tableData').html(`
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-red-500">
                                Gagal memuat data inventaris.
                                <button type="button" onclick="loadData(page)"
                                    class="ml-2 font-semibold underline">
                                    Coba lagi
                                </button>
                            </td>
                        </tr>
                    `);

                    $('#mobileContainer').html(`
                        <div class="rounded-xl border border-red-200 bg-white p-6 text-center text-sm text-red-500">
                            Gagal memuat data.
                            <button type="button" onclick="loadData(page)"
                                class="mt-2 block w-full font-semibold underline">
                                Coba lagi
                            </button>
                        </div>
                    `);
                },

                complete: function(xhr, status) {
                    if (requestInventaris && requestInventaris.statusText !== 'abort') {
                        requestInventaris = null;
                    }
                }
            });
        }

        // ==========================================
        // PAGINATION
        // ==========================================

        function renderPagination(res) {
            const totalData = Number(res.total || 0);
            const dari = totalData > 0 ? Number(res.from || ((res.current_page - 1) * (res.per_page || res.data.length) +
                1)) : 0;
            const sampai = totalData > 0 ? Number(res.to || Math.min(dari + (res.data?.length || 0) - 1, totalData)) : 0;

            const infoHtml = `
                <p class="text-xs text-slate-500 sm:text-sm">
                    Menampilkan
                    <span class="font-semibold text-slate-700">${dari}–${sampai}</span>
                    dari
                    <span class="font-semibold text-slate-700">${totalData}</span>
                    data
                </p>
            `;

            if (!res.last_page || res.last_page <= 1) {
                $('#pagination').html(`
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                        ${infoHtml}
                    </div>
                `);
                $('#paginationMobile').html(`
                    <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                        ${infoHtml}
                    </div>
                `);
                return;
            }

            let html =
                '<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">';
            html += infoHtml;
            html += '<div class="flex flex-wrap items-center justify-end gap-1.5">';

            html += `
                <button type="button"
                    ${res.current_page <= 1 ? 'disabled' : `onclick="loadData(${res.current_page - 1})"`}
                    aria-label="Halaman sebelumnya"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm transition
                    ${res.current_page <= 1
                        ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100'}">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            `;

            const total = Number(res.last_page);
            const current = Number(res.current_page);
            let pages = [];

            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');

                for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
                    pages.push(i);
                }

                if (current < total - 2) pages.push('...');
                pages.push(total);
            }

            pages.forEach(function(item) {
                if (item === '...') {
                    html += '<span class="px-1 text-xs text-slate-400">...</span>';
                    return;
                }

                html += `
                    <button type="button"
                        ${item === current ? 'aria-current="page"' : ''}
                        onclick="loadData(${item})"
                        class="h-9 min-w-9 rounded-lg border px-3 text-sm transition
                        ${item === current
                            ? 'border-primary bg-primary font-semibold text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100'}">
                        ${item}
                    </button>
                `;
            });

            html += `
                <button type="button"
                    ${current >= total ? 'disabled' : `onclick="loadData(${current + 1})"`}
                    aria-label="Halaman berikutnya"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm transition
                    ${current >= total
                        ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100'}">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            `;

            html += '</div></div>';

            $('#pagination').html(html);
            $('#paginationMobile').html(html);
        }

        // ==========================================
        // TAMBAH INVENTARIS
        // ==========================================

        function tambahInventaris() {
            const form = $('#formInventaris')[0];

            form.reset();

            $('#inventaris_id').val('');
            $('#formMethod').val('POST');
            $('#jenis').val('');
            $('#modalTitle').text('Tambah Inventaris');

            $('#foto').prop('required', true).val('');

            $('#previewFoto')
                .attr('src', noImageUrl)
                .addClass('hidden');

            $('#placeholderFoto').removeClass('hidden');
            $('#formInventaris [name^="kondisi"]').val(0);

            $('#formInventaris button[type="submit"]')
                .prop('disabled', false)
                .html('<i class="fa-solid fa-floppy-disk mr-2"></i> Simpan');

            showModal('modalInventaris');
        }

        // ==========================================
        // EDIT INVENTARIS
        // ==========================================

        function editInventaris(id) {
            $.ajax({
                url: inventarisUrl + '/' + id,
                method: 'GET',

                success: function(res) {
                    $('#formInventaris')[0].reset();

                    $('#modalTitle').text('Edit Inventaris');
                    $('#inventaris_id').val(res.id);
                    $('#formMethod').val('PUT');

                    $('#jenis').val(res.jenis);
                    $('[name="nama"]').val(res.nama);
                    $('#harga').val(formatRupiah(res.harga));

                    $('[name="tanggal_perolehan"]').val(
                        String(res.tanggal_perolehan || '').substring(0, 10)
                    );

                    $('#foto').val('').prop('required', false);

                    $('#previewFoto')
                        .attr('src', fotoUrl(res.foto))
                        .removeClass('hidden');

                    $('#placeholderFoto').addClass('hidden');
                    $('#formInventaris [name^="kondisi"]').val(0);

                    (res.kondisi || []).forEach(function(item) {
                        $(`#formInventaris [name="kondisi[${item.kondisi}]"]`)
                            .val(item.jumlah);
                    });

                    showModal('modalInventaris');
                },

                error: function() {
                    swalError('Data inventaris gagal dimuat.');
                }
            });
        }

        // ==========================================
        // SIMPAN / UPDATE
        // ==========================================

        $('#formInventaris').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const id = $('#inventaris_id').val();
            const method = $('#formMethod').val();

            const url = method === 'POST' ?
                inventarisStoreUrl :
                inventarisUrl + '/' + id;

            const formData = new FormData(form);

            formData.set('harga', $('#harga').val().replace(/\D/g, ''));

            if (method === 'PUT') {
                formData.set('_method', 'PUT');
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function() {
                    $('#formInventaris button[type="submit"]')
                        .prop('disabled', true)
                        .html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menyimpan...');
                },

                success: function(res) {
                    if (!res.status) {
                        swalError(res.message || 'Inventaris gagal disimpan.');
                        return;
                    }

                    closeModal('modalInventaris');

                    form.reset();

                    $('#inventaris_id').val('');
                    $('#formMethod').val('POST');
                    $('#foto').prop('required', true);

                    $('#previewFoto')
                        .attr('src', noImageUrl)
                        .addClass('hidden');

                    $('#placeholderFoto').removeClass('hidden');

                    loadData(1);

                    swalSuccess(
                        res.message ||
                        (method === 'POST' ?
                            'Inventaris berhasil ditambahkan.' :
                            'Inventaris berhasil diperbarui.')
                    );
                },

                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const message = Object.values(xhr.responseJSON.errors)
                            .flat()
                            .map(escapeHtml)
                            .join('<br>');

                        swalError(message);
                        return;
                    }

                    swalError(
                        xhr.responseJSON?.message ||
                        'Inventaris gagal disimpan.'
                    );
                },

                complete: function() {
                    $('#formInventaris button[type="submit"]')
                        .prop('disabled', false)
                        .html('<i class="fa-solid fa-floppy-disk mr-2"></i> Simpan');
                }
            });
        });

        // ==========================================
        // HAPUS INVENTARIS
        // ==========================================

        function hapusInventaris(id) {
            swalConfirm(
                'Hapus Inventaris?',
                'Data yang dihapus tidak dapat dikembalikan.',
                function() {
                    $.ajax({
                        url: inventarisUrl + '/' + id,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        },

                        beforeSend: function() {
                            Swal.showLoading();
                        },

                        success: function(res) {
                            swalSuccess(
                                res.message || 'Inventaris berhasil dihapus.'
                            );

                            loadData(1);
                        },

                        error: function(xhr) {
                            swalError(
                                xhr.responseJSON?.message ||
                                'Inventaris gagal dihapus.'
                            );
                        }
                    });
                }
            );
        }
    </script>
@endpush
