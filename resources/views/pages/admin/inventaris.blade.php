@extends('layouts.app')

@section('title', 'Inventaris')

@section('page', 'inventaris')

@section('page_title', 'Inventaris')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h2 class="text-lg md:text-2xl font-bold">
                    Data Inventaris
                </h2>

                <p class="text-slate-500 md:mt-1 text-xs md:text-base">
                    Kelola seluruh inventaris organisasi.
                </p>

            </div>

            <button onclick="tambahInventaris()"
                class="bg-primary text-white rounded-xl absolute bottom-3 right-3 w-10 h-10 flex justify-center items-center hover:bg-primary_hover transition md:hidden">

                <i class="fa-solid fa-plus"></i>
            </button>

            <button onclick="tambahInventaris()"
                class="bg-primary text-white rounded-xl px-5 py-3 hover:bg-primary_hover transition hidden md:block">

                <i class="fa-solid fa-plus mr-2"></i>

                Tambah Inventaris

            </button>

        </div>

        {{-- Search --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-200 p-3 md:p-5">

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                </i>

                <input id="search" type="text" placeholder="Cari inventaris..."
                    class="w-full rounded-xl border border-slate-300 pl-11 pr-4 py-3 focus:border-primary focus:ring-primary text-xs md:text-base">
            </div>

        </div>

        {{-- Table --}}
        <div id="tableContainer">
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-[87vw] hidden md:block">

                <div class="w-full overflow-x-auto">

                    <table class="w-full min-w-[900px]">

                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs md:text-sm text-slate-600 whitespace-nowrap">
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[90px]">Foto</th>
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[100px]">Kode</th>
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[220px]">Nama</th>
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[150px]">Harga</th>
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[180px]">Tanggal Perolehan</th>
                                <th class="px-4 py-2 md:px-6 md:py-4 min-w-[140px] text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableData" class="text-xs md:text-base">

                        </tbody>

                    </table>

                </div>

            </div>

            <div id="mobileContainer" class="space-y-4 md:hidden"></div>

            <div id="paginationMobile" class="mt-5 md:hidden"></div>

        </div>

        <div id="emptyState" class="hidden">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">

                <div class="py-14 md:py-20 px-4 flex flex-col items-center text-center">

                    <div class="w-16 h-16 md:w-24 md:h-24 rounded-full bg-slate-100 flex items-center justify-center">

                        <i class="fa-solid fa-box-open text-2xl md:text-4xl text-slate-400"></i>

                    </div>

                    <h2 class="mt-6 text-base md:text-2xl font-bold text-slate-700">

                        Belum Ada Inventaris

                    </h2>

                    <p class="mt-1 md:mt-3 text-slate-500 text-center max-w-md text-xs md:text-sm">

                        Data inventaris masih kosong.
                        Silakan tambahkan inventaris pertama
                        untuk mulai mengelola aset organisasi.

                    </p>

                    <button onclick="tambahInventaris()"
                        class="text-xs md:text-base mt-8 bg-primary hover:bg-primary_hover text-white px-6 py-3 rounded-xl">

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
        $('#formInventaris').on('submit', function(e) {

            e.preventDefault();

            let id = $('#inventaris_id').val();

            let method = $('#formMethod').val();

            let url =
                method === 'POST' ?
                "{{ route('inventaris.store') }}" :
                "/admin/inventaris/" + id;

            // ubah harga
            $('#harga').val(
                $('#harga').val().replace(/\./g, '')
            );

            // BUAT DULU
            let formData = new FormData(this);

            // baru append
            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }


            // Ubah format harga
            let harga = $('#harga').val().replace(/\./g, '');
            $('#harga').val(harga);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function() {

                    $('#formInventaris button[type="submit"]')
                        .prop('disabled', true)
                        .html(`
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Menyimpan...
                `);

                },

                success: function(res) {

                    if (res.status) {

                        closeModal('modalInventaris');

                        $('#formInventaris')[0].reset();

                        $('#previewFoto').addClass('hidden');
                        $('#placeholderFoto').removeClass('hidden');

                        loadData();

                        swalSuccess('Inventaris berhasil' + (method === 'POST' ? ' ditambahkan' :
                            ' diubah'));
                    }

                },

                error: function(xhr) {

                    let message = 'Terjadi kesalahan.';

                    if (xhr.status === 422) {

                        const errors = xhr.responseJSON.errors;

                        message = Object.values(errors).map(e => e[0]).join('<br>');

                    }

                    swalError('Inventaris gagal disimpan.');
                },

                complete: function() {

                    $('#formInventaris button[type="submit"]')
                        .prop('disabled', false)
                        .html(`
                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Simpan
                `);

                }

            });

        });
    </script>
    <script>
        let page = 1;
        let searchTimer;
        let html = '';
        let mobileHtml = '';

        loadData();

        $('#search').on('input', function() {

            clearTimeout(searchTimer);

            searchTimer = setTimeout(function() {

                page = 1;
                loadData();

            }, 500); // tunggu 500ms setelah user berhenti mengetik

        });

        function loadData(currentPage = 1) {

            page = currentPage;

            $('#tableData').html(`
        <tr>
            <td colspan="6" class="py-10 text-center text-slate-500">
                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                Memuat data...
            </td>
        </tr>
    `);

            $('#mobileContainer').html(`
        <div class="bg-white rounded-2xl p-10 text-center">
            <i class="fa-solid fa-spinner fa-spin text-primary text-2xl"></i>
            <p class="mt-3 text-slate-500">Memuat data...</p>
        </div>
    `);

            $.get("{{ route('inventaris.getData') }}", {
                page: page,
                search: $('#search').val()
            }, function(res) {

                const isSearch = $('#search').val().trim() !== '';

                // ==========================
                // DATABASE MASIH KOSONG
                // ==========================
                if (res.total === 0 && !isSearch) {

                    $('#tableContainer').addClass('hidden');
                    $('#mobileContainer').addClass('hidden');
                    $('#pagination').html('');
                    $('#paginationMobile').html('');
                    $('#emptyState').removeClass('hidden');

                    return;
                }

                $('#emptyState').addClass('hidden');
                $('#tableContainer').removeClass('hidden');
                $('#mobileContainer').removeClass('hidden');

                let html = '';
                let mobileHtml = '';

                // ==========================
                // HASIL PENCARIAN KOSONG
                // ==========================
                if (res.data.length === 0) {

                    html = `
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center">
                                <i class="fa-solid fa-magnifying-glass text-2xl text-slate-400"></i>
                            </div>

                            <h3 class="mt-4 text-lg font-semibold text-slate-700">
                                Data tidak ditemukan
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Coba gunakan kata kunci lain.
                            </p>
                        </div>
                    </td>
                </tr>
            `;

                    mobileHtml = `
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 text-center">

                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass text-2xl text-slate-400"></i>
                    </div>

                    <h3 class="mt-4 font-semibold text-slate-700">
                        Data tidak ditemukan
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Coba gunakan kata kunci lain.
                    </p>

                </div>
            `;

                } else {

                    $.each(res.data, function(i, item) {
                        const baseStorage = "{{ asset('storage') }}";

                        // ==========================
                        // DESKTOP TABLE
                        // ==========================
                        html += `
                    <tr class="border-t hover:bg-slate-50 transition">

                        <td class="px-4 py-2 md:px-6 md:py-4">
                            <img
                                src="${baseStorage}/${item.foto}"
                                class="w-12 h-12 md:w-14 md:h-14 rounded-xl object-cover border">
                        </td>

                        <td class="px-4 py-2 md:px-6 md:py-4">
                            ${item.kode_inventaris}
                        </td>

                        <td class="px-4 py-2 md:px-6 md:py-4 font-medium">
                            ${item.nama}
                        </td>

                        <td class="px-4 py-2 md:px-6 md:py-4">
                            Rp ${Number(item.harga).toLocaleString('id-ID')}
                        </td>

                        <td class="px-4 py-2 md:px-6 md:py-4">
                            ${formatTanggal(item.tanggal_perolehan)}
                        </td>

                        <td class="px-4 py-2 md:px-6 md:py-4">

                            <div class="flex justify-center gap-2">

                                <button
                                    onclick="editInventaris(${item.id})"
                                    class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">

                                    <i class="fa-solid fa-pen"></i>

                                </button>

                                <button
                                    onclick="hapusInventaris(${item.id})"
                                    class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>
                `;

                        // ==========================
                        // MOBILE CARD
                        // ==========================
                        mobileHtml += `
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">

                        <div class="flex gap-4">

                            <img
                                src="/images/inventaris/${item.foto}"
                                class="w-20 h-20 rounded-xl object-cover border">

                            <div class="flex-1 min-w-0">

                                <h3 class="font-semibold text-slate-800">
                                    ${item.nama}
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    ${item.kode_inventaris}
                                </p>

                                <p class="text-primary font-bold mt-2">
                                    Rp ${Number(item.harga).toLocaleString('id-ID')}
                                </p>

                                <p class="text-xs text-slate-500 mt-2">
                                    <i class="fa-regular fa-calendar mr-1"></i>
                                    ${formatTanggal(item.tanggal_perolehan)}
                                </p>

                            </div>

                        </div>

                        <div class="flex justify-end gap-2 mt-4">

                            <button
                                onclick="editInventaris(${item.id})"
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">

                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>

                            <button
                                onclick="hapusInventaris(${item.id})"
                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">

                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>

                        </div>

                    </div>
                `;

                    });

                }

                $('#tableData').html(html);
                $('#mobileContainer').html(mobileHtml);

                renderPagination(res);

            });

        }

        function renderPagination(res) {

            if (res.total === 0) {
                $('#pagination').html('');
                return;
            }

            let html = '<div class="flex justify-end items-center gap-2 flex-wrap">';

            // Prev
            html += `
                <button
                    ${res.current_page == 1 ? 'disabled' : `onclick="loadData(${res.current_page-1})"`}
                    class="w-7 h-7 md:w-9 md:h-9 text-xs md:text-base rounded-lg border flex items-center justify-center
                    ${res.current_page == 1
                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                        : 'bg-white hover:bg-slate-100'}">

                    <i class="fa-solid fa-chevron-left text-xs"></i>

                </button>
            `;

            const total = res.last_page;
            const current = res.current_page;

            let pages = [];

            if (total <= 7) {

                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }

            } else {

                pages.push(1);

                if (current > 3) {
                    pages.push('...');
                }

                let start = Math.max(2, current - 1);
                let end = Math.min(total - 1, current + 1);

                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }

                if (current < total - 2) {
                    pages.push('...');
                }

                pages.push(total);

            }

            pages.forEach(function(item) {

                if (item === '...') {

                    html += `
                <span class="w-9 h-9 flex items-center justify-center text-slate-500">
                    ...
                </span>
            `;

                } else {

                    html += `
                <button
                    onclick="loadData(${item})"
                    class="w-7 h-7 md:w-9 md:h-9 rounded-lg border transition text-xs md:text-base

                    ${item == current
                        ? 'bg-primary text-white border-primary'
                        : 'bg-white hover:bg-slate-100'}">

                    ${item}

                </button>
            `;

                }

            });

            // Next
            html += `
        <button
            ${res.current_page == total ? 'disabled' : `onclick="loadData(${res.current_page+1})"`}
            class="w-7 h-7 md:w-9 md:h-9 text-xs md:text-base rounded-lg border flex items-center justify-center
            ${res.current_page == total
                ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                : 'bg-white hover:bg-slate-100'}">

            <i class="fa-solid fa-chevron-right text-xs"></i>

        </button>
    `;

            html += '</div>';

            $('#pagination').html(html);
            $('#paginationMobile').html(html);
        }

        function editInventaris(id) {

            $.get('/admin/inventaris/' + id, function(res) {

                $('#modalTitle').text('Edit Inventaris');

                $('#inventaris_id').val(res.id);

                $('[name=kode_inventaris]').val(res.kode_inventaris);
                $('[name=nama]').val(res.nama);

                $('#harga').val(
                    Number(res.harga).toLocaleString('id-ID')
                );

                $('[name=tanggal_perolehan]').val(res.tanggal_perolehan);

                $('#previewFoto')
                    .attr('src', '/images/inventaris/' + res.foto)
                    .removeClass('hidden');

                $('#placeholderFoto').addClass('hidden');

                $('[name="kondisi[Baik]"]').val(0);
                $('[name="kondisi[Kurang Baik]"]').val(0);
                $('[name="kondisi[Rusak]"]').val(0);
                $('[name="kondisi[Hilang]"]').val(0);

                res.kondisi.forEach(function(item) {

                    $(`[name="kondisi[${item.kondisi}]"]`)
                        .val(item.jumlah);

                });

                $('#formMethod').val('PUT');

                showModal('modalInventaris');

            });

        }

        function tambahInventaris() {

            $('#formInventaris')[0].reset();

            $('#inventaris_id').val('');

            $('#formMethod').val('POST');

            $('#modalTitle').text('Tambah Inventaris');

            $('#previewFoto').addClass('hidden');

            $('#placeholderFoto').removeClass('hidden');

            showModal('modalInventaris');

        }

        function hapusInventaris(id) {

            swalConfirm(
                'Hapus Inventaris?',
                'Data yang dihapus tidak dapat dikembalikan.',
                function() {

                    $.ajax({

                        url: '/admin/inventaris/' + id,

                        type: 'POST',

                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },

                        beforeSend() {

                            Swal.showLoading();

                        },

                        success(res) {

                            swalSuccess(res.message);

                            loadData(page);

                        },

                        error() {

                            swalError('Inventaris gagal dihapus.');

                        }

                    });

                }

            );

        }
    </script>
@endpush
