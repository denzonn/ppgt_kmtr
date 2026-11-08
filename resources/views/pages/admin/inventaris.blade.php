@extends('layouts.app')

@section('title', 'Inventaris')
@section('page', 'inventaris')
@section('page_title', 'Inventaris')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Data Inventaris</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola seluruh aset, kondisi, dan nilai perolehan inventaris organisasi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="btnTambahInventaris"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                    <i class="fas fa-plus"></i>
                    Tambah Inventaris
                </button>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Inventaris</p>
                        <h3 id="totalInventaris" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-boxes-stacked text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jenis aset terdaftar</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Nilai Perolehan</p>
                        <h3 id="totalNilai" class="mt-2 text-2xl font-bold text-slate-800">Rp 0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Akumulasi harga perolehan</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Barang Baik</p>
                        <h3 id="totalBaik" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-circle-check text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Kondisi siap digunakan</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Rusak & Hilang</p>
                        <h3 id="totalRusak" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600">
                        <i class="fas fa-triangle-exclamation text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Perlu perbaikan atau pemulihan</p>
            </div>
        </div>

        {{-- Panel Data --}}
        <div id="panelData" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Toolbar --}}
            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-box-archive"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Inventaris</h3>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Data inventaris yang telah terdaftar.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative sm:col-span-2 xl:col-span-1">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="search" placeholder="Cari kode, nama, atau jenis..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 xl:w-72">
                        </div>

                        <select id="filterJenis"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                            <option value="">Semua Jenis</option>
                            <option value="PAK">PAK - Kesekretariatan</option>
                            <option value="PDP">PDP - Dapur Perkakas</option>
                            <option value="PPL">PPL - Penunjang Lainnya</option>
                        </select>

                        <button type="button" id="btnReset"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-primary/30 hover:bg-primary/5 hover:text-primary">
                            <i class="fas fa-rotate-left text-xs"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabel Desktop --}}
            <div id="tableContainer" class="hidden w-full min-w-0 max-w-full md:block">
                <div class="table-scrollbar w-full min-w-0 max-w-full overflow-x-auto overscroll-x-contain">
                    <table class="w-full min-w-[960px] border-collapse text-left">

                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80">
                                <th
                                    class="w-20 px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Foto</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Kode Inventaris</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Informasi Inventaris</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Kondisi</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Harga & Perolehan</th>
                                <th
                                    class="w-28 px-5 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableData" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Memuat data inventaris...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Desktop --}}
            <div id="pagination" class="hidden border-t border-slate-100 bg-white px-5 py-4 md:block"></div>
        </div>

        {{-- Card Mobile --}}
        <div id="mobileContainer" class="min-w-0 space-y-4 md:hidden"></div>

        {{-- Pagination Mobile --}}
        <div id="paginationMobile" class="md:hidden"></div>

        {{-- Data Kosong --}}
        <div id="emptyState" class="hidden w-full min-w-0">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col items-center px-4 py-14 text-center md:py-20">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100">
                        <i class="fa-solid fa-box-open text-3xl text-slate-400"></i>
                    </div>

                    <h3 class="mt-6 text-lg font-bold text-slate-700 md:text-2xl">
                        Belum Ada Inventaris
                    </h3>

                    <p class="mt-2 max-w-md text-sm text-slate-500">
                        Data inventaris masih kosong. Silakan tambahkan inventaris pertama untuk mulai
                        mengelola aset organisasi.
                    </p>

                    <button type="button" id="btnTambahInventarisKosong"
                        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90">
                        <i class="fa-solid fa-plus"></i>
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
        // Fungsi halaman dibuat lokal agar tidak tertimpa helper global dari app.js
        // (formatRupiah, renderPagination, formatTanggal).
        $(document).ready(function() {
            const inventarisUrl = @json(route('inventaris.index'));
            const inventarisStoreUrl = @json(route('inventaris.store'));
            const inventarisDataUrl = @json(route('inventaris.getData'));
            const csrfToken = @json(csrf_token());
            const noImageUrl = @json(asset('images/no-image.svg'));

            const jenisInventaris = {
                PAK: 'Perlengkapan Administrasi Kesekretariatan',
                PDP: 'Perlengkapan Dapur Perkakas',
                PPL: 'Perlengkapan Penunjang Lainnya'
            };

            const jenisStyle = {
                PAK: {
                    badge: 'bg-blue-50 text-blue-700 ring-blue-100',
                    icon: 'fa-folder-open'
                },
                PDP: {
                    badge: 'bg-amber-50 text-amber-700 ring-amber-100',
                    icon: 'fa-utensils'
                },
                PPL: {
                    badge: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    icon: 'fa-boxes-stacked'
                }
            };

            const kondisiStyle = {
                'Baik': {
                    badge: 'bg-emerald-50 text-emerald-700',
                    icon: 'fa-solid fa-circle-check'
                },
                'Kurang Baik': {
                    badge: 'bg-amber-50 text-amber-700',
                    icon: 'fa-solid fa-circle-exclamation'
                },
                'Rusak': {
                    badge: 'bg-red-50 text-red-700',
                    icon: 'fa-solid fa-circle-xmark'
                },
                'Hilang': {
                    badge: 'bg-slate-100 text-slate-600',
                    icon: 'fa-solid fa-box-open'
                }
            };

            let page = 1;
            let searchTimer = null;
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

            // Harga disimpan sebagai angka polos, konversi aman untuk data lama.
            function formatRupiah(angka) {
                const nilai = Number(String(angka ?? '0').replace(/[^\d-]/g, '')) || 0;

                return nilai.toLocaleString('id-ID');
            }

            function fotoUrl(foto) {
                return foto || noImageUrl;
            }

            function renderJenis(jenis) {
                const kode = String(jenis || '').toUpperCase();
                const nama = jenisInventaris[kode] || jenis || '-';
                const style = jenisStyle[kode] || {
                    badge: 'bg-slate-50 text-slate-600 ring-slate-200',
                    icon: 'fa-box'
                };

                return `
                    <div class="mt-2 flex min-w-0 flex-wrap items-center gap-2">
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset ${style.badge}">
                            <i class="fas ${style.icon} text-[9px]"></i>
                            ${escapeHtml(kode || '-')}
                        </span>
                        <span class="min-w-0 whitespace-normal break-words text-xs leading-5 text-slate-500">
                            ${escapeHtml(nama)}
                        </span>
                    </div>
                `;
            }

            function renderKondisi(kondisi) {
                if (!kondisi || kondisi.length === 0) {
                    return '<span class="text-xs text-slate-400">-</span>';
                }

                const chips = kondisi.map(function(item) {
                    const style = kondisiStyle[item.kondisi] || {
                        badge: 'bg-slate-100 text-slate-600',
                        icon: 'fa-solid fa-circle'
                    };

                    const jumlah = Number(item.jumlah || 0);

                    if (jumlah <= 0) return '';

                    return `
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ${style.badge}">
                            <i class="${style.icon} text-[9px]"></i>
                            ${escapeHtml(item.kondisi)} ${jumlah}
                        </span>
                    `;
                }).join('');

                return chips || '<span class="text-xs text-slate-400">-</span>';
            }

            // ==========================================
            // LOAD DATA
            // ==========================================

            loadData();

            $('#search').on('input', function() {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(function() {
                    loadData(1);
                }, 400);
            });

            $('#filterJenis').on('change', function() {
                loadData(1);
            });

            $('#btnReset').on('click', function() {
                $('#search').val('');
                $('#filterJenis').val('');

                loadData(1);
            });

            $(document).on('click', '#pagination button[data-page], #paginationMobile button[data-page]', function() {
                loadData(Number($(this).data('page')));
            });

            $(document).on('click', '[data-reload]', function() {
                loadData(page);
            });

            function loadData(currentPage = 1) {
                page = currentPage;

                if (requestInventaris) {
                    requestInventaris.abort();
                }

                const adaFilter = Boolean($('#search').val().trim() || $('#filterJenis').val());

                $('#emptyState').addClass('hidden');
                $('#panelData').removeClass('hidden');

                $('#tableData').html(`
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Memuat data inventaris...
                        </td>
                    </tr>
                `);

                $('#mobileContainer').html(`
                    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                        <i class="fas fa-spinner fa-spin text-2xl text-primary"></i>
                        <p class="mt-3 text-sm text-slate-500">Memuat data...</p>
                    </div>
                `);

                $('#pagination, #paginationMobile').empty();

                requestInventaris = $.ajax({
                    url: inventarisDataUrl,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#search').val(),
                        jenis: $('#filterJenis').val()
                    },

                    success: function(res) {
                        renderStats(res.stats);

                        if (res.total === 0 && !adaFilter) {
                            $('#panelData').addClass('hidden');
                            $('#emptyState').removeClass('hidden');
                            return;
                        }

                        $('#emptyState').addClass('hidden');
                        $('#panelData').removeClass('hidden');

                        let desktopHtml = '';
                        let mobileHtml = '';

                        if (!res.data || res.data.length === 0) {
                            desktopHtml = `
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                <i class="fas fa-magnifying-glass text-2xl"></i>
                                            </div>
                                            <p class="mt-4 font-semibold text-slate-700">Data tidak ditemukan</p>
                                            <p class="mt-1 text-sm text-slate-400">Coba gunakan kata kunci atau filter lainnya.</p>
                                        </div>
                                    </td>
                                </tr>
                            `;

                            mobileHtml = `
                                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="fas fa-magnifying-glass text-2xl"></i>
                                    </div>
                                    <p class="mt-4 font-semibold text-slate-700">Data tidak ditemukan</p>
                                    <p class="mt-1 text-sm text-slate-400">Coba gunakan kata kunci atau filter lainnya.</p>
                                </div>
                            `;
                        } else {
                            $.each(res.data, function(i, item) {
                                const nama = escapeHtml(item.nama);
                                const kode = escapeHtml(item.kode_inventaris);
                                const jenis = renderJenis(item.jenis);
                                const kondisi = renderKondisi(item.kondisi);
                                const harga = formatRupiah(item.harga);
                                const tanggal = escapeHtml(formatTanggal(item.tanggal_perolehan));
                                const gambar = escapeHtml(fotoUrl(item.foto));
                                const id = Number(item.id);

                                // Desktop
                                desktopHtml += `
                                    <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                                        <td class="px-5 py-4">
                                            <img src="${gambar}"
                                                alt="${nama}"
                                                onerror="this.onerror=null;this.src='${noImageUrl}'"
                                                class="h-12 w-12 rounded-xl border border-slate-200 object-cover transition group-hover:scale-105">
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="inline-flex items-center rounded-lg bg-primary/5 px-3 py-1.5 text-xs font-bold tracking-wide text-primary">
                                                ${kode}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="min-w-0 max-w-[320px]">
                                                <p class="break-words font-semibold leading-5 text-slate-800">
                                                    ${nama}
                                                </p>
                                                ${jenis}
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex max-w-[220px] flex-wrap gap-1.5">
                                                ${kondisi}
                                            </div>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="font-semibold text-slate-800">
                                                    Rp ${harga}
                                                </span>
                                                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                                    ${tanggal}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                    data-id="${id}"
                                                    title="Edit Inventaris"
                                                    class="btnEdit flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>

                                                <button type="button"
                                                    data-id="${id}"
                                                    title="Hapus Inventaris"
                                                    class="btnHapus flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                                                    <i class="fas fa-trash text-xs"></i>
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
                                                onerror="this.onerror=null;this.src='${noImageUrl}'"
                                                class="h-20 w-20 shrink-0 rounded-xl border border-slate-200 object-cover">

                                            <div class="min-w-0 flex-1">
                                                <h3 class="break-words font-semibold leading-5 text-slate-800">
                                                    ${nama}
                                                </h3>

                                                <span class="mt-1.5 inline-flex items-center rounded-lg bg-primary/5 px-2.5 py-1 text-[11px] font-bold tracking-wide text-primary">
                                                    ${kode}
                                                </span>

                                                <div class="mt-2">
                                                    ${jenis}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            ${kondisi}
                                        </div>

                                        <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                            <div>
                                                <p class="text-sm font-bold text-primary">
                                                    Rp ${harga}
                                                </p>
                                                <p class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500">
                                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                                    ${tanggal}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    data-id="${id}"
                                                    title="Edit Inventaris"
                                                    class="btnEdit flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>

                                                <button type="button"
                                                    data-id="${id}"
                                                    title="Hapus Inventaris"
                                                    class="btnHapus flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
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
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-exclamation-circle text-3xl text-red-400"></i>
                                        <p class="mt-3 font-semibold text-slate-700">Gagal memuat data inventaris.</p>
                                        <button type="button" data-reload
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                            Coba lagi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);

                        $('#mobileContainer').html(`
                            <div class="rounded-2xl border border-red-100 bg-white p-8 text-center shadow-sm">
                                <i class="fas fa-exclamation-circle text-3xl text-red-400"></i>
                                <p class="mt-3 font-semibold text-slate-700">Gagal memuat data.</p>
                                <button type="button" data-reload
                                    class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Coba lagi
                                </button>
                            </div>
                        `);
                    },

                    complete: function(xhr, status) {
                        if (requestInventaris && status !== 'abort') {
                            requestInventaris = null;
                        }
                    }
                });
            }

            // ==========================================
            // STATISTIK
            // ==========================================

            function renderStats(stats) {
                if (!stats) return;

                $('#totalInventaris').text(formatRupiah(stats.total_inventaris));
                $('#totalNilai').text('Rp ' + formatRupiah(stats.total_nilai));
                $('#totalBaik').text(formatRupiah(stats.kondisi?.Baik || 0));
                $('#totalRusak').text(
                    formatRupiah((stats.kondisi?.Rusak || 0) + (stats.kondisi?.Hilang || 0))
                );
            }

            // ==========================================
            // PAGINATION
            // ==========================================

            function renderPagination(res) {
                const totalData = Number(res.total || 0);
                const dari = totalData > 0 ? Number(res.from || 0) : 0;
                const sampai = totalData > 0 ? Number(res.to || 0) : 0;

                const infoHtml = `
                    <p class="text-sm text-slate-500">
                        Menampilkan
                        <span class="font-semibold text-slate-700">${dari}-${sampai}</span>
                        dari
                        <span class="font-semibold text-slate-700">${formatRupiah(totalData)}</span>
                        data
                    </p>
                `;

                if (!res.last_page || res.last_page <= 1) {
                    $('#pagination').html(`
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            ${infoHtml}
                        </div>
                    `);
                    $('#paginationMobile').html(`
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                            ${infoHtml}
                        </div>
                    `);
                    return;
                }

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

                let html = '<div class="flex flex-wrap items-center justify-between gap-3">';
                html += infoHtml;
                html += '<div class="flex flex-wrap items-center justify-end gap-1.5">';

                html += `
                    <button type="button"
                        data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}
                        aria-label="Halaman sebelumnya"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition
                        ${current <= 1
                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                            : 'border-slate-200 bg-white text-slate-500 hover:border-primary hover:text-primary'}">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                `;

                pages.forEach(function(item) {
                    if (item === '...') {
                        html += '<span class="px-1 text-xs text-slate-400">...</span>';
                        return;
                    }

                    html += `
                        <button type="button"
                            ${item === current ? 'aria-current="page"' : ''}
                            data-page="${item}"
                            class="flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold transition
                            ${item === current
                                ? 'bg-primary text-white shadow-sm shadow-primary/20'
                                : 'border border-slate-200 bg-white text-slate-600 hover:border-primary hover:text-primary'}">
                            ${item}
                        </button>
                    `;
                });

                html += `
                    <button type="button"
                        data-page="${current + 1}" ${current >= total ? 'disabled' : ''}
                        aria-label="Halaman berikutnya"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition
                        ${current >= total
                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                            : 'border-slate-200 bg-white text-slate-500 hover:border-primary hover:text-primary'}">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                `;

                html += '</div></div>';

                $('#pagination').html(html);
                $('#paginationMobile').html(
                    `<div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">${html}</div>`
                );
            }

            // ==========================================
            // FORM INVENTARIS
            // ==========================================

            // Menampilkan/menyembunyikan input kondisi. Input yang ditutup dikosongkan
            // agar nilainya tidak ikut tersimpan tanpa terlihat.
            function toggleKondisi(button, show) {
                const box = $('#' + button.attr('aria-controls'));

                box.toggleClass('hidden', !show);
                button.attr('aria-expanded', show);
                button.find('i').toggleClass('fa-plus', !show).toggleClass('fa-xmark', show);

                if (!show) box.find('input').val('');
            }

            $('.btnKondisi').on('click', function() {
                const button = $(this);
                const show = button.attr('aria-expanded') !== 'true';

                toggleKondisi(button, show);

                if (show) $('#' + button.attr('aria-controls')).find('input').trigger('focus');
            });

            function resetFormInventaris() {
                const form = $('#formInventaris');

                form[0].reset();
                form.removeData('id');

                $('.btnKondisi').each(function() {
                    toggleKondisi($(this), false);
                });

                $('#methodInventaris').val('POST');
                $('#modalTitle').text('Tambah Inventaris');
                $('#btnSimpanInventaris').html('<i class="fas fa-floppy-disk mr-2"></i> Simpan');

                $('#previewFoto').attr('src', noImageUrl).addClass('hidden');
                $('#placeholderFoto').removeClass('hidden');
            }

            $('#btnTambahInventaris, #btnTambahInventarisKosong').on('click', function() {
                resetFormInventaris();
                showModal('modalInventaris');
            });

            // Ambil data inventaris dan tampilkan pada modal yang sama untuk proses edit.
            $(document).on('click', '.btnEdit', function() {
                const button = $(this);
                const id = button.data('id');

                button.prop('disabled', true);

                $.ajax({
                    url: inventarisUrl + '/' + encodeURIComponent(id),
                    type: 'GET',
                    dataType: 'json',

                    success: function(res) {
                        resetFormInventaris();

                        $('#formInventaris').data('id', id);
                        $('#methodInventaris').val('PUT');
                        $('#modalTitle').text('Edit Inventaris');
                        $('#btnSimpanInventaris').html('<i class="fas fa-floppy-disk mr-2"></i> Simpan Perubahan');

                        $('#jenis').val(res.jenis);
                        $('#nama').val(res.nama);
                        $('#harga').val(formatRupiah(res.harga));
                        $('#tanggal_perolehan').val(
                            String(res.tanggal_perolehan || '').substring(0, 10)
                        );

                        if (res.foto) {
                            $('#previewFoto').attr('src', fotoUrl(res.foto)).removeClass('hidden');
                            $('#placeholderFoto').addClass('hidden');
                        }

                        (res.kondisi || []).forEach(function(item) {
                            $(`#formInventaris [name="kondisi[${item.kondisi}]"]`).val(item.jumlah);
                        });

                        // Kondisi yang sudah berisi langsung ditampilkan saat edit.
                        $('.btnKondisi').each(function() {
                            const button = $(this);

                            if ($('#' + button.attr('aria-controls')).find('input').val()) {
                                toggleKondisi(button, true);
                            }
                        });

                        showModal('modalInventaris');
                    },

                    error: function(xhr) {
                        swalError(xhr.responseJSON?.message || 'Data inventaris gagal dimuat.');
                    },

                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });

            // Foto bersifat opsional, file hanya diperiksa jika dipilih.
            $('#foto').on('change', function() {
                const file = this.files[0];

                if (!file) return;

                if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)) {
                    $(this).val('');
                    swalError('Format foto tidak didukung.');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    $(this).val('');
                    swalError('Ukuran foto maksimal 2 MB.');
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    $('#previewFoto').attr('src', e.target.result).removeClass('hidden');
                    $('#placeholderFoto').addClass('hidden');
                };

                reader.readAsDataURL(file);
            });

            $('#harga').on('input', function() {
                const value = $(this).val().replace(/\D/g, '');

                $(this).val(value ? formatRupiah(value) : '');
            });

            $('#formInventaris').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const button = $('#btnSimpanInventaris');
                const originalButton = button.html();
                const id = form.data('id');
                const isEdit = Boolean(id);

                // Laravel menerima PUT melalui method spoofing.
                $('#methodInventaris').val(isEdit ? 'PUT' : 'POST');

                const formData = new FormData(this);

                formData.set('harga', $('#harga').val().replace(/\D/g, ''));

                // Kondisi yang tidak diisi dikirim sebagai 0.
                form.find('[name^="kondisi["]').each(function() {
                    formData.set(this.name, this.value || 0);
                });

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...');

                $.ajax({
                    url: isEdit ? inventarisUrl + '/' + encodeURIComponent(id) : inventarisStoreUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',

                    success: function(res) {
                        if (!res.status) {
                            swalError(res.message || 'Inventaris gagal disimpan.');
                            return;
                        }

                        closeModal('modalInventaris');
                        loadData(1);
                        swalSuccess(res.message);
                    },

                    error: function(xhr) {
                        // swalError menampilkan teks biasa, jadi pesan tidak perlu di-escape.
                        const errors = xhr.responseJSON?.errors;

                        swalError(
                            errors ?
                            Object.values(errors).flat().join(' ') :
                            xhr.responseJSON?.message || 'Inventaris gagal disimpan.'
                        );
                    },

                    complete: function() {
                        button.prop('disabled', false).html(originalButton);
                    }
                });
            });

            // ==========================================
            // HAPUS INVENTARIS
            // ==========================================

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');

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
                                loadData(1);

                                swalSuccess(
                                    res.message || 'Inventaris berhasil dihapus.'
                                );
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
            });
        });
    </script>
@endpush
