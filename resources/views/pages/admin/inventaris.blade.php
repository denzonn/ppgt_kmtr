@extends('layouts.app')

@section('title', 'Inventaris')

@section('page', 'inventaris')

@section('page_title', 'Inventaris')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h2 class="text-2xl font-bold">
                    Data Inventaris
                </h2>

                <p class="text-slate-500 mt-1">
                    Kelola seluruh inventaris organisasi.
                </p>

            </div>

            <button onclick="tambahInventaris()"
                class="bg-primary text-white rounded-xl px-5 py-3 hover:bg-primary_hover transition">

                <i class="fa-solid fa-plus mr-2"></i>

                Tambah Inventaris

            </button>

        </div>

        {{-- Search --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                </i>

                <input id="search" type="text" placeholder="Cari inventaris..."
                    class="w-full rounded-xl border border-slate-300 pl-11 pr-4 py-3 focus:border-primary focus:ring-primary">

            </div>

        </div>

        {{-- Table --}}
        <div id="tableContainer">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-slate-50">

                            <tr class="text-left text-sm text-slate-600">
                                <th class="px-6 py-4">Foto</th>
                                <th class="px-6 py-4">Kode</th>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">Harga</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>

                        </thead>

                        <tbody id="tableData">

                        </tbody>

                    </table>

                </div>

            </div>

            <div id="pagination" class="mt-5"></div>

        </div>

        <div id="emptyState" class="hidden">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">

                <div class="py-20 flex flex-col items-center">

                    <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center">

                        <i class="fa-solid fa-box-open text-4xl text-slate-400"></i>

                    </div>

                    <h2 class="mt-6 text-2xl font-bold text-slate-700">

                        Belum Ada Inventaris

                    </h2>

                    <p class="mt-3 text-slate-500 text-center max-w-md">

                        Data inventaris masih kosong.
                        Silakan tambahkan inventaris pertama
                        untuk mulai mengelola aset organisasi.

                    </p>

                    <button onclick="tambahInventaris()"
                        class="mt-8 bg-primary hover:bg-primary_hover text-white px-6 py-3 rounded-xl">

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

            $.get("{{ route('inventaris.getData') }}", {
                page: page,
                search: $('#search').val()
            }, function(res) {
                console.log(res);


                const isSearch = $('#search').val().trim() !== '';

                // ==========================
                // DATABASE MASIH KOSONG
                // ==========================

                if (res.total === 0 && !isSearch) {

                    $('#tableContainer').addClass('hidden');
                    $('#emptyState').removeClass('hidden');

                    return;

                }

                $('#emptyState').addClass('hidden');
                $('#tableContainer').removeClass('hidden');

                let html = '';

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

                } else {

                    $.each(res.data, function(i, item) {

                        html += `

                <tr class="border-t hover:bg-slate-50 transition">

                    <td class="px-6 py-4">

                        <img
                            src="/images/inventaris/${item.foto}"
                            class="w-14 h-14 rounded-xl object-cover border">

                    </td>

                    <td class="px-6 py-4">

                        ${item.kode_inventaris}

                    </td>

                    <td class="px-6 py-4 font-medium">

                        ${item.nama}

                    </td>

                    <td class="px-6 py-4">

                        Rp ${Number(item.harga).toLocaleString('id-ID')}

                    </td>

                    <td class="px-6 py-4">

                        ${formatTanggal(item.tanggal_perolehan)}

                    </td>

                    <td class="px-6 py-4">

                        <div class="flex justify-center gap-2">

                            <button
onclick="editInventaris(${item.id})"
class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">

    <i class="fa-solid fa-pen"></i>

</button>
                            <button onclick="hapusInventaris(${item.id})"
                                class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </div>

                    </td>

                </tr>

                `;

                    });

                }

                $('#tableData').html(html);

                renderPagination(res);

            });

        }

        function renderPagination(res) {

            if (res.total === 0) {

                $('#pagination').html('');
                return;

            }

            let html = '<div class="flex justify-end gap-2">';

            for (let i = 1; i <= res.last_page; i++) {

                html += `
            <button
                onclick="loadData(${i})"
                class="w-10 h-10 rounded-lg border transition

                ${i == res.current_page
                    ? 'bg-primary text-white border-primary'
                    : 'bg-white hover:bg-slate-100'}

                ">

                ${i}

            </button>
        `;

            }

            html += '</div>';

            $('#pagination').html(html);

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
