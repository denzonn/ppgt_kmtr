@extends('layouts.app')

@section('title', 'Data Anggota')
@section('page', 'anggota')
@section('page_title', 'Data Anggota')

@section('content')
    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Data Anggota</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola data dan informasi seluruh anggota PPGT.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" id="btnImportAnggota"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-file-excel text-primary"></i>
                    Import Excel
                </button>

                <button type="button" id="btnTambahAnggota"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                    <i class="fas fa-plus"></i>
                    Tambah Anggota
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Anggota</p>
                        <h3 id="totalAnggota" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Seluruh anggota terdaftar</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Laki-laki</p>
                        <h3 id="totalLakiLaki" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-mars text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jumlah anggota laki-laki</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Perempuan</p>
                        <h3 id="totalPerempuan" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-50 text-pink-600">
                        <i class="fas fa-venus text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jumlah anggota perempuan</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Jemaat</p>
                        <h3 id="totalJemaat" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-church text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jemaat terdaftar</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Anggota</h3>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Data anggota yang telah terdaftar.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative sm:col-span-2 xl:col-span-1">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="search" placeholder="Cari nama / nomor anggota..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 xl:w-64">
                        </div>

                        <select id="filterWilayah"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                            <option value="">Semua Wilayah</option>
                            <option value="dalam_wilayah">Dalam Wilayah Pelayanan</option>
                            <option value="luar_wilayah">Luar Wilayah</option>
                        </select>

                        <select id="filterJemaat"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                            <option value="">Semua Jemaat</option>
                        </select>

                        <button type="button" id="btnReset"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-primary/30 hover:bg-primary/5 hover:text-primary">
                            <i class="fas fa-rotate-left text-xs"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80">
                            <th
                                class="w-16 px-6 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                No.</th>
                            <th class="w-40 px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">No.
                                Anggota</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Nama Anggota
                            </th>
                            <th class="w-36 px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Jenis
                                Kelamin</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Jemaat</th>
                            <th class="min-w-52 px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Status Wilayah</th>
                            <th
                                class="w-36 px-6 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="tableAnggota" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm text-slate-400">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Memuat data anggota...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-4 border-t border-slate-100 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <p id="paginationInfo" class="text-sm text-slate-500">Menampilkan 0 data</p>

                    <select id="perPage"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    <span class="text-xs text-slate-400">data per halaman</span>
                </div>

                <div id="pagination" class="flex items-center gap-1.5"></div>
            </div>
        </div>
    </div>

    @include('modals.anggota')
    @include('modals.detail-anggota')
    @include('modals.import-anggota')
@endsection

@push('addon-script')
    <script>
        $(document).ready(function() {
            const urlGetData = @json(route('data_anggota.getData'));
            const urlStore = @json(route('data_anggota.store'));
            const urlShow = @json(route('data_anggota.show', ':id'));
            const urlEdit = @json(route('data_anggota.edit', ':id'));
            const urlUpdate = @json(route('data_anggota.update', ':id'));
            const urlDestroy = @json(route('data_anggota.destroy', ':id'));
            const csrfToken = @json(csrf_token());
            const urlImport = @json(route('data_anggota.import'));

            let searchTimer = null;
            let request = null;

            function escapeHtml(value) {
                return $('<div>').text(value ?? '-').html();
            }

            function formatNumber(value) {
                return Number(value || 0).toLocaleString('id-ID');
            }

            function formatTanggal(tanggal) {
                if (!tanggal) return '-';

                const parts = String(tanggal).split('-');
                if (parts.length !== 3) return tanggal;

                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            function setDetail(id, value) {
                $(`#${id}`).text(value || '-');
            }

            function loadingTable() {
                $('#tableAnggota').html(`
            <tr>
                <td colspan="7" class="px-6 py-16 text-center text-sm text-slate-400">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Memuat data anggota...
                </td>
            </tr>
        `);
            }

            function showError(message) {
                $('#tableAnggota').html(`
            <tr>
                <td colspan="7" class="px-6 py-16 text-center text-sm text-red-500">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    ${escapeHtml(message)}
                </td>
            </tr>
        `);

                $('#paginationInfo').text('Data tidak dapat dimuat');
                $('#pagination').empty();
            }

            function getData(page = 1) {
                const params = {
                    page: page,
                    search: $('#search').val(),
                    status_wilayah: $('#filterWilayah').val(),
                    jemaat: $('#filterJemaat').val(),
                    per_page: $('#perPage').val()
                };

                loadingTable();

                if (request && request.readyState !== 4) {
                    request.abort();
                }

                request = $.ajax({
                    url: urlGetData,
                    type: 'GET',
                    data: params,
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            showError('Respons server tidak valid.');
                            return;
                        }

                        renderStatistics(response.statistics);
                        renderFilters(response.filters);
                        renderTable(response.data);
                        renderPagination(response.data);
                    },
                    error: function(xhr, status) {
                        if (status === 'abort') return;

                        console.error(xhr.responseText);
                        showError('Gagal mengambil data anggota.');
                    }
                });
            }

            function renderStatistics(data) {
                $('#totalAnggota').text(formatNumber(data.total_anggota));
                $('#totalLakiLaki').text(formatNumber(data.total_laki_laki));
                $('#totalPerempuan').text(formatNumber(data.total_perempuan));
                $('#totalJemaat').text(formatNumber(data.total_jemaat));
            }

            function renderFilters(filters) {
                const selectedJemaat = $('#filterJemaat').val();

                const jemaatOptions = (filters.jemaat || []).map(item => `
            <option value="${item.id}"
                ${String(item.id) === String(selectedJemaat) ? 'selected' : ''}>
                ${escapeHtml(item.nama)}
            </option>
        `).join('');

                $('#filterJemaat').html(`
            <option value="">Semua Jemaat</option>
            ${jemaatOptions}
        `);
            }

            function renderTable(pagination) {
                const rows = pagination.data || [];

                if (rows.length === 0) {
                    $('#tableAnggota').html(`
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <p class="mt-4 font-semibold text-slate-700">Data Anggota Tidak Ditemukan</p>
                            <p class="mt-1 text-sm text-slate-400">Coba gunakan kata kunci atau filter lainnya.</p>
                        </div>
                    </td>
                </tr>
                `);
                    return;
                }

                let html = '';

                rows.forEach((item, index) => {
                    const nomor = (pagination.from || 0) + index;
                    const jenisKelamin = (item.jenis_kelamin || '').toLowerCase();
                    const status = (item.status_anggota || '').toLowerCase();
                    const namaJemaat = item.jemaat?.nama || '-';

                    let genderBadge = 'bg-slate-100 text-slate-600';
                    let genderIcon = 'fa-user';

                    if (jenisKelamin.includes('laki') || jenisKelamin === 'l') {
                        genderBadge = 'bg-blue-50 text-blue-700';
                        genderIcon = 'fa-mars';
                    } else if (jenisKelamin.includes('perempuan') || jenisKelamin === 'p') {
                        genderBadge = 'bg-pink-50 text-pink-700';
                        genderIcon = 'fa-venus';
                    }

                    const statusBadge = status === 'aktif' ?
                        'bg-primary/10 text-primary' :
                        'bg-slate-100 text-slate-500';

                    const statusDot = status === 'aktif' ?
                        'bg-primary' :
                        'bg-slate-400';

                    html += `
                <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                    <td class="px-6 py-5 text-center text-sm font-medium text-slate-400">
                        ${nomor}
                    </td>

                    <td class="whitespace-nowrap px-5 py-5">
                        <span class="inline-flex items-center rounded-lg bg-primary/5 px-3 py-1.5 text-xs font-bold tracking-wide text-primary">
                            ${escapeHtml(item.no_anggota)}
                        </span>
                    </td>

                    <td class="px-5 py-5">
                        <div class="font-semibold text-slate-800">${escapeHtml(item.nama)}</div>
                    </td>

                    <td class="whitespace-nowrap px-5 py-5">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold ${genderBadge}">
                            <i class="fas ${genderIcon} text-[10px]"></i>
                            ${escapeHtml(item.jenis_kelamin)}
                        </span>
                    </td>

                    <td class="px-5 py-5">
                        <span class="text-sm font-medium text-slate-600">${escapeHtml(namaJemaat)}</span>
                    </td>

                    <td class="px-5 py-5">
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ${
                            item.status_wilayah === 'dalam_wilayah'
                                ? 'bg-green-50 text-green-700'
                                : 'bg-amber-50 text-amber-700'
                        }">
                            ${item.status_wilayah === 'dalam_wilayah'
                                ? 'Dalam Wilayah'
                                : item.status_wilayah === 'luar_wilayah'
                                    ? 'Luar Wilayah'
                                    : '-'}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-6 py-5">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button"
                                data-id="${item.id}"
                                title="Detail Anggota"
                                class="btnDetail flex h-9 w-9 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition hover:border-blue-200 hover:bg-blue-600 hover:text-white">
                                <i class="fas fa-eye text-xs"></i>
                            </button>

                            <button type="button"
                                data-id="${item.id}"
                                title="Edit Anggota"
                                class="btnEdit flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                                <i class="fas fa-pen text-xs"></i>
                            </button>

                            <button type="button"
                                data-id="${item.id}"
                                data-nama="${encodeURIComponent(item.nama || '')}"
                                title="Hapus Anggota"
                                class="btnHapus flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
                });

                $('#tableAnggota').html(html);
            }

            function renderPagination(data) {
                const from = data.from || 0;
                const to = data.to || 0;
                const total = data.total || 0;
                const current = data.current_page || 1;
                const last = data.last_page || 1;

                $('#paginationInfo').html(`
            Menampilkan
            <span class="font-semibold text-slate-700">${formatNumber(from)}-${formatNumber(to)}</span>
            dari
            <span class="font-semibold text-slate-700">${formatNumber(total)}</span>
            anggota
        `);

                let html = `
            <button type="button" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-40">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>
        `;

                const start = Math.max(1, current - 2);
                const end = Math.min(last, current + 2);

                for (let i = start; i <= end; i++) {
                    html += `
                <button type="button" data-page="${i}"
                    class="flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold transition
                    ${i === current
                        ? 'bg-primary text-white shadow-sm shadow-primary/20'
                        : 'border border-slate-200 bg-white text-slate-600 hover:border-primary hover:text-primary'}">
                    ${i}
                </button>
            `;
                }

                html += `
            <button type="button" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-40">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        `;

                $('#pagination').html(html);
            }

            $('#search').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => getData(1), 400);
            });

            $('#filterWilayah, #filterJemaat, #perPage').on('change', function() {
                getData(1);
            });

            $(document).on('click', '#pagination button:not(:disabled)', function() {
                const page = Number($(this).data('page'));

                if (page > 0) {
                    getData(page);
                }
            });

            $('#btnReset').on('click', function() {
                $('#search').val('');
                $('#filterWilayah').val('');
                $('#filterJemaat').val('');
                $('#perPage').val('10');

                getData(1);
            });

            function resetFormAnggota() {
                const form = $('#formAnggota');

                if (form.length && form[0]) {
                    form[0].reset();
                }

                form.removeData('id');
                $('#methodAnggota').val('POST');
                $('#anggotaError').addClass('hidden').empty();
                form.find('.border-red-500').removeClass('border-red-500');

                $('#judulModalAnggota').text('Tambah Anggota');
                $('#deskripsiModalAnggota').text('Lengkapi informasi anggota yang akan didaftarkan.');
                $('#btnSimpanAnggota').html('<i class="fas fa-save"></i> Simpan Anggota');

                // Nomor anggota dibuat otomatis oleh server saat tambah data.
                $('#nomorAnggotaInfo').removeClass('hidden');
            }

            $('#btnTambahAnggota').on('click', function() {
                resetFormAnggota();
                showModal('modalAnggota');
            });

            // Ambil data anggota dan tampilkan pada modal yang sama untuk proses edit.
            $(document).on('click', '.btnEdit', function() {
                const id = $(this).data('id');
                const button = $(this);

                button.prop('disabled', true);

                $.ajax({
                    url: urlEdit.replace(':id', encodeURIComponent(id)),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success || !response.data) {
                            alert(response.message || 'Data anggota tidak ditemukan.');
                            return;
                        }

                        const data = response.data;

                        resetFormAnggota();
                        $('#formAnggota').data('id', id);
                        $('#methodAnggota').val('PUT');

                        Object.keys(data).forEach(function(field) {
                            const input = $('#formAnggota').find(`[name="${field}"]`);
                            if (input.length) {
                                input.val(data[field] ?? '');
                            }
                        });

                        // Mendukung field relasi jemaat jika response mengirim objek jemaat.
                        if (data.jemaat_id) {
                            $('#formAnggota').find('[name="jemaat_id"]').val(data.jemaat_id);
                        }

                        $('#judulModalAnggota').text('Edit Data Anggota');
                        $('#deskripsiModalAnggota').text(
                            'Perbarui informasi anggota yang dipilih.');
                        $('#btnSimpanAnggota').html(
                            '<i class="fas fa-save"></i> Simpan Perubahan');
                        $('#nomorAnggotaInfo').addClass('hidden');

                        showModal('modalAnggota');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        const message = xhr.responseJSON?.message ||
                            'Gagal mengambil data anggota.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        } else {
                            alert(message);
                        }
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });

            $('#formAnggota').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const button = $('#btnSimpanAnggota');
                const originalButton = button.html();
                const id = form.data('id');
                const isEdit = Boolean(id);
                const url = isEdit ?
                    urlUpdate.replace(':id', encodeURIComponent(id)) :
                    urlStore;

                $('#anggotaError').addClass('hidden').empty();
                form.find('.border-red-500').removeClass('border-red-500');

                // Laravel menerima PUT melalui method spoofing.
                $('#methodAnggota').val(isEdit ? 'PUT' : 'POST');

                button.prop('disabled', true).html(`
                    <i class="fas fa-spinner fa-spin"></i>
                    Menyimpan...
                `);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            const message = response.message || 'Data anggota gagal disimpan.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: message
                                });
                            } else {
                                alert(message);
                            }
                            return;
                        }

                        closeModal('modalAnggota');
                        resetFormAnggota();
                        getData(1);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            let errorHtml = '<ul class="list-disc space-y-1 pl-5">';

                            Object.keys(errors).forEach(function(field) {
                                errorHtml += `<li>${escapeHtml(errors[field][0])}</li>`;
                                form.find(`[name="${field}"]`).addClass(
                                    'border-red-500');
                            });

                            errorHtml += '</ul>';
                            $('#anggotaError').removeClass('hidden').html(errorHtml);
                            $('#anggotaError')[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            return;
                        }

                        console.error(xhr.responseText);
                        const message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data anggota.';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        } else {
                            alert(message);
                        }
                    },
                    complete: function() {
                        button.prop('disabled', false).html(originalButton);
                    }
                });
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                const nama = decodeURIComponent($(this).attr('data-nama') || '');

                function hapusData() {
                    $.ajax({
                        url: urlDestroy.replace(':id', encodeURIComponent(id)),
                        type: 'DELETE',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            getData(1);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);

                            const message = xhr.responseJSON?.message ||
                                'Gagal menghapus data anggota.';

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: message
                                });
                            } else {
                                alert(message);
                            }
                        }
                    });
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus Anggota?',
                        text: `Apakah kamu yakin ingin menghapus ${nama}?`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b'
                    }).then(function(result) {
                        if (result.isConfirmed) hapusData();
                    });
                } else if (confirm(`Apakah kamu yakin ingin menghapus ${nama}?`)) {
                    hapusData();
                }
            });

            // Handler modal detail anggota
            // Klik tombol detail
            $(document).on('click', '.btnDetail', function() {
                const id = $(this).data('id');

                // Reset tampilan modal
                $('#detailAnggotaContent').addClass('hidden');
                $('#detailAnggotaLoading').removeClass('hidden');

                showModal('modalDetailAnggota');

                $.ajax({
                    url: urlShow.replace(':id', encodeURIComponent(id)),
                    type: 'GET',
                    dataType: 'json',

                    success: function(response) {
                        if (!response.success || !response.data) {
                            $('#detailAnggotaLoading').html(`
                        <p class="text-sm text-red-500">
                            Data anggota tidak ditemukan.
                        </p>
                    `);
                            return;
                        }

                        const data = response.data;

                        // Identitas utama
                        setDetail('detailNoAnggota', data.no_anggota);
                        setDetail('detailNama', data.nama);

                        // Status anggota
                        setDetail('detailStatusAnggota', data.status_anggota);

                        // Status wilayah
                        const statusWilayah = data.status_wilayah === 'dalam_wilayah' ?
                            'Dalam Wilayah' :
                            data.status_wilayah === 'luar_wilayah' ?
                            'Luar Wilayah' :
                            '-';

                        $('#detailStatusWilayah')
                            .text(statusWilayah)
                            .removeClass(
                                'bg-green-50 text-green-700 bg-amber-50 text-amber-700')
                            .addClass(
                                data.status_wilayah === 'dalam_wilayah' ?
                                'bg-green-50 text-green-700' :
                                'bg-amber-50 text-amber-700'
                            );

                        // Informasi pribadi
                        setDetail('detailNik', data.nik);
                        setDetail('detailJenisKelamin', data.jenis_kelamin);

                        const tempatTanggalLahir = [
                            data.tempat_lahir,
                            data.tanggal_lahir ? formatTanggal(data.tanggal_lahir) : null
                        ].filter(Boolean).join(', ');

                        setDetail('detailTempatTanggalLahir', tempatTanggalLahir);

                        const golonganDarah = [
                            data.golongan_darah,
                            data.golongan_darah_rhesus
                        ].filter(Boolean).join(' ');

                        setDetail('detailGolonganDarah', golonganDarah);
                        setDetail('detailPendidikan', data.pendidikan);

                        // Informasi keanggotaan
                        setDetail('detailJemaat', data.jemaat?.nama);
                        setDetail('detailWilayahText', statusWilayah);

                        // Informasi kontak
                        setDetail('detailTelepon', data.telepon);
                        setDetail('detailEmail', data.email);
                        setDetail('detailAlamat', data.alamat);

                        // Tampilkan konten
                        $('#detailAnggotaLoading').addClass('hidden');
                        $('#detailAnggotaContent').removeClass('hidden');
                    },

                    error: function(xhr) {
                        console.error(xhr.responseText);

                        $('#detailAnggotaLoading').html(`
                    <div class="flex flex-col items-center gap-2 text-red-500">
                        <i class="fas fa-exclamation-circle text-2xl"></i>
                        <p class="text-sm">Gagal mengambil detail anggota.</p>
                    </div>
                `);
                    }
                });
            });

            // ==========================================
            // IMPORT DATA ANGGOTA
            // Semua handler import berada di dalam ready
            // agar dapat mengakses urlImport, getData,
            // escapeHtml, dan fungsi modal.
            // ==========================================

            function resetImportAnggota() {
                const form = $('#formImportAnggota');

                if (form.length && form[0]) {
                    form[0].reset();
                }

                $('#hasilImportAnggota').addClass('hidden').empty();

                $('#btnProsesImport')
                    .prop('disabled', false)
                    .html('<i class="fas fa-upload mr-1"></i> Mulai Import');
            }

            $(document).on('click', '#btnImportAnggota', function(e) {
                e.preventDefault();

                resetImportAnggota();

                const modal = $('#modalImportAnggota');

                if (!modal.length) {
                    console.error('Modal #modalImportAnggota tidak ditemukan!');
                    return;
                }

                showModal('modalImportAnggota');

                // Fallback jika helper showModal tidak membuka modal.
                modal.removeClass('hidden').addClass('flex');
            });

            // Tutup modal menggunakan helper modal yang sudah ada.
            $('#btnTutupImport, #btnBatalImport').on('click', function() {
                closeModal('modalImportAnggota');
            });

            // Submit form import.
            $('#formImportAnggota').on('submit', function(e) {
                e.preventDefault();

                const form = this;
                const fileInput = $('#fileImportAnggota')[0];
                const file = fileInput.files[0];

                if (!file) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Belum Dipilih',
                        text: 'Silakan pilih file Excel terlebih dahulu.'
                    });
                    return;
                }

                const formData = new FormData(form);
                const button = $('#btnProsesImport');
                const buttonAwal = button.html();

                $('#hasilImportAnggota').addClass('hidden').empty();

                button
                    .prop('disabled', true)
                    .html(`
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Sedang Mengimport...
                    `);

                $.ajax({
                    url: urlImport,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',

                    success: function(response) {
                        if (!response.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Import Gagal',
                                text: response.message || 'Import data gagal.'
                            });
                            return;
                        }

                        const hasil = response.hasil || {};
                        const diproses = Number(hasil.diproses || 0);
                        const berhasil = Number(hasil.berhasil || 0);
                        const dilewati = Number(hasil.dilewati || 0);
                        const gagal = Number(hasil.gagal || 0);
                        const detailGagal = Array.isArray(hasil.detail_gagal) ?
                            hasil.detail_gagal : [];

                        let html = `
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-slate-700">
                                    Hasil Import Data Anggota
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Total baris diproses: ${formatNumber(diproses)}
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-xl bg-green-50 p-3">
                                    <p class="text-xs text-green-600">Berhasil</p>
                                    <p class="mt-1 text-xl font-bold text-green-700">
                                        ${formatNumber(berhasil)}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-amber-50 p-3">
                                    <p class="text-xs text-amber-600">Dilewati</p>
                                    <p class="mt-1 text-xl font-bold text-amber-700">
                                        ${formatNumber(dilewati)}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-red-50 p-3">
                                    <p class="text-xs text-red-600">Gagal</p>
                                    <p class="mt-1 text-xl font-bold text-red-700">
                                        ${formatNumber(gagal)}
                                    </p>
                                </div>
                            </div>
                        `;

                        if (detailGagal.length > 0) {
                            html += `
                                <div class="mt-4">
                                    <p class="mb-2 text-sm font-semibold text-slate-700">
                                        Detail Data Gagal (maksimal 100 baris)
                                    </p>

                                    <div class="max-h-52 space-y-2 overflow-y-auto">
                                        ${detailGagal.map(item => `
                                                    <div class="rounded-lg bg-red-50 p-3 text-xs text-red-700">
                                                        <p class="font-semibold">
                                                            Baris ${escapeHtml(item.baris)} -
                                                            ${escapeHtml(item.nama)}
                                                        </p>
                                                        <p class="mt-1">
                                                            ${escapeHtml(item.alasan)}
                                                        </p>
                                                    </div>
                                                `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        $('#hasilImportAnggota')
                            .removeClass('hidden')
                            .html(html);

                        // Kosongkan input file setelah berhasil diproses.
                        $('#fileImportAnggota').val('');

                        // Muat ulang tabel dan statistik anggota.
                        getData(1);

                        Swal.fire({
                            icon: 'success',
                            title: 'Import Selesai!',
                            html: `
                                <div class="space-y-1 text-sm text-slate-600">
                                    <p>Total diproses: <b>${formatNumber(diproses)}</b></p>
                                    <p>Berhasil: <b class="text-green-600">${formatNumber(berhasil)}</b></p>
                                    <p>Dilewati: <b class="text-amber-600">${formatNumber(dilewati)}</b></p>
                                    <p>Gagal: <b class="text-red-600">${formatNumber(gagal)}</b></p>
                                </div>
                            `,
                            confirmButtonColor: '#15803d'
                        });
                    },

                    error: function(xhr) {
                        console.error('IMPORT ERROR:', xhr.responseText);

                        let message = 'Terjadi kesalahan saat mengimport data.';

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            message = Object.values(errors).flat().join('\n') || message;
                        } else {
                            message = xhr.responseJSON?.message || message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Import Gagal!',
                            text: message
                        });
                    },

                    complete: function() {
                        button
                            .prop('disabled', false)
                            .html(buttonAwal);
                    }
                });
            });

            // Load data awal halaman.
            getData();
        });
    </script>
@endpush
