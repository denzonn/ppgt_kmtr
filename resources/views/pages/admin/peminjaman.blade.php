@extends('layouts.app')

@section('title', 'Peminjaman Barang')
@section('page', 'peminjaman')
@section('page_title', 'Peminjaman Barang')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Riwayat Peminjaman</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola peminjaman barang dan pengembalian inventaris organisasi.
                </p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Peminjaman</p>
                        <h3 id="totalPeminjaman" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-right-left text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Seluruh riwayat peminjaman</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Sedang Dipinjam</p>
                        <h3 id="totalDipinjam" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Belum dikembalikan</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Sudah Dikembalikan</p>
                        <h3 id="totalDikembalikan" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-circle-check text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Selesai dipinjam</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Barang</p>
                        <h3 id="totalBarang" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-boxes-stacked text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jumlah unit dipinjam</p>
            </div>
        </div>

        {{-- Panel Data --}}
        <div id="panelData" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Toolbar --}}
            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-right-left"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Peminjaman</h3>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Data peminjaman barang yang telah tercatat.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative sm:col-span-2 xl:col-span-1">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="search" placeholder="Cari peminjam atau barang..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 xl:w-72">
                        </div>

                        <select id="filterStatus"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                            <option value="">Semua Status</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dikembalikan">Dikembalikan</option>
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
                    <table class="w-full min-w-[900px] border-collapse text-left">

                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80">
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Barang Dipinjam</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Peminjam</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Tanggal Pinjam</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Tanggal Kembali</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Status</th>
                                <th
                                    class="w-28 px-5 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableData" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Memuat data peminjaman...
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
                        Belum Ada Data Peminjaman
                    </h3>

                    <p class="mt-2 max-w-md text-sm text-slate-500">
                        Data peminjaman akan muncul di sini ketika ada barang yang dipinjam.
                    </p>
                </div>
            </div>
        </div>

    </div>

    @include('modals.peminjaman')
@endsection

@push('addon-script')
    <script>
        const peminjamanDataUrl = @json(route('peminjaman-admin.getData'));
        const storageUrl = @json(asset('storage'));

        let page = 1;
        let searchTimer = null;
        let requestPeminjaman = null;
        const peminjamanData = {};

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

        function formatAngka(value) {
            return Number(value || 0).toLocaleString('id-ID');
        }

        // Foto lama disimpan di folder publik, foto baru di storage publik.
        function fotoUrl(path, folder) {
            if (!path) return '';

            const value = String(path);

            if (!value.includes('/')) {
                return `/images/${folder}/${value}`;
            }

            return storageUrl + '/' + value;
        }

        function statusPeminjaman(status) {
            return String(status || '').toLowerCase() === 'dipinjam';
        }

        function badgeStatus(status) {
            return statusPeminjaman(status)
                ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                        <i class="fas fa-clock text-[10px]"></i> Dipinjam
                   </span>`
                : `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        <i class="fas fa-circle-check text-[10px]"></i> Dikembalikan
                   </span>`;
        }

        function renderBarang(details) {
            if (!details || details.length === 0) {
                return '<span class="text-xs text-slate-400">-</span>';
            }

            return details.map(function(detail) {
                const nama = escapeHtml(detail.inventaris?.nama || '-');
                const kode = escapeHtml(detail.inventaris?.kode_inventaris || '-');

                return `
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 last:border-b-0">
                        <div class="min-w-0">
                            <p class="break-words text-sm font-medium text-slate-700">${nama}</p>
                            <p class="mt-0.5 break-all text-[11px] text-slate-500">${kode}</p>
                        </div>

                        <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                            ${formatAngka(detail.jumlah)} Unit
                        </span>
                    </div>
                `;
            }).join('');
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

            $('#filterStatus').on('change', function() {
                loadData(1);
            });

            $('#btnReset').on('click', function() {
                $('#search').val('');
                $('#filterStatus').val('');

                loadData(1);
            });
        });

        function loadData(currentPage = 1) {
            page = currentPage;

            if (requestPeminjaman) {
                requestPeminjaman.abort();
            }

            const adaFilter = Boolean($('#search').val().trim() || $('#filterStatus').val());

            $('#emptyState').addClass('hidden');
            $('#panelData').removeClass('hidden');

            $('#tableData').html(`
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Memuat data peminjaman...
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

            requestPeminjaman = $.ajax({
                url: peminjamanDataUrl,
                method: 'GET',
                data: {
                    page: page,
                    search: $('#search').val(),
                    status: $('#filterStatus').val()
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

                    let html = '';
                    let mobileHtml = '';

                    if (!res.data || res.data.length === 0) {
                        html = `
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
                            peminjamanData[item.id] = item;

                            const peminjam = escapeHtml(item.nama_peminjam);
                            const barang = renderBarang(item.details);
                            const tanggalPinjam = escapeHtml(formatTanggal(item.tanggal_peminjaman));
                            const tanggalKembali = item.tanggal_pengembalian ?
                                escapeHtml(formatTanggal(item.tanggal_pengembalian)) :
                                '<span class="text-slate-400">-</span>';
                            const badge = badgeStatus(item.status);

                            // Desktop
                            html += `
                                <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                                    <td class="px-5 py-4">
                                        <div class="min-w-[220px] max-w-[300px]">
                                            ${barang}
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                                ${escapeHtml(String(item.nama_peminjam || '?')
                                                    .trim()
                                                    .charAt(0)
                                                    .toUpperCase())}
                                            </span>
                                            <span class="font-semibold text-slate-800">${peminjam}</span>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        ${tanggalPinjam}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        ${tanggalKembali}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        ${badge}
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center">
                                            <button type="button"
                                                data-id="${item.id}"
                                                title="Detail Peminjaman"
                                                class="btnDetail flex h-9 w-9 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition hover:border-blue-200 hover:bg-blue-600 hover:text-white">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;

                            // Mobile
                            mobileHtml += `
                                <div class="w-full min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="break-words font-semibold text-slate-800">
                                                ${peminjam}
                                            </h3>
                                            <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                                <i class="fa-regular fa-calendar text-[10px]"></i>
                                                ${tanggalPinjam}
                                            </p>
                                        </div>

                                        ${badge}
                                    </div>

                                    <div class="mt-3 border-t border-slate-100 pt-2">
                                        ${barang}
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                        <div>
                                            <p class="text-xs text-slate-500">Tgl Kembali</p>
                                            <p class="mt-0.5 text-sm font-medium text-slate-700">
                                                ${tanggalKembali}
                                            </p>
                                        </div>

                                        <button type="button"
                                            data-id="${item.id}"
                                            class="btnDetail inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-semibold text-white transition hover:opacity-90">
                                            <i class="fas fa-eye"></i>
                                            Detail
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    $('#tableData').html(html);
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
                                    <p class="mt-3 font-semibold text-slate-700">Gagal memuat data peminjaman.</p>
                                    <button type="button" onclick="loadData(page)"
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
                            <button type="button" onclick="loadData(page)"
                                class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                Coba lagi
                            </button>
                        </div>
                    `);
                },

                complete: function(xhr, status) {
                    if (requestPeminjaman && status !== 'abort') {
                        requestPeminjaman = null;
                    }
                }
            });
        }

        // ==========================================
        // STATISTIK
        // ==========================================

        function renderStats(stats) {
            if (!stats) return;

            $('#totalPeminjaman').text(formatAngka(stats.total_peminjaman));
            $('#totalDipinjam').text(formatAngka(stats.dipinjam));
            $('#totalDikembalikan').text(formatAngka(stats.dikembalikan));
            $('#totalBarang').text(formatAngka(stats.total_barang));
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
                    <span class="font-semibold text-slate-700">${formatAngka(totalData)}</span>
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
                    ${current <= 1 ? 'disabled' : `onclick="loadData(${current - 1})"`}
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
                        onclick="loadData(${item})"
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
                    ${current >= total ? 'disabled' : `onclick="loadData(${current + 1})"`}
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
        // DETAIL PEMINJAMAN
        // ==========================================

        $(document).on('click', '.btnDetail', function() {
            const id = $(this).data('id');
            const data = peminjamanData[id];

            if (!data) return;

            const peminjam = escapeHtml(data.nama_peminjam);
            const tanggalPinjam = escapeHtml(formatTanggal(data.tanggal_peminjaman));
            const tanggalKembali = data.tanggal_pengembalian ?
                escapeHtml(formatTanggal(data.tanggal_pengembalian)) :
                '<span class="text-slate-400">-</span>';

            const fotoPengambilan = data.foto_pengambilan ?
                `<img src="${escapeHtml(fotoUrl(data.foto_pengambilan, 'peminjaman'))}"
                        alt="Foto pengambilan"
                        class="h-32 w-full rounded-xl border border-slate-200 object-cover md:h-48">`
                : `<div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-400 md:h-48">
                        Tidak ada foto
                   </div>`;

            const fotoPengembalian = data.foto_pengembalian ?
                `<img src="${escapeHtml(fotoUrl(data.foto_pengembalian, 'pengembalian'))}"
                        alt="Foto pengembalian"
                        class="h-32 w-full rounded-xl border border-slate-200 object-cover md:h-48">`
                : `<div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-400 md:h-48">
                        Belum dikembalikan
                   </div>`;

            $('#modalBody').html(`
                <div class="space-y-6">

                    <div class="grid grid-cols-2 gap-4 text-xs md:text-base">
                        <div>
                            <p class="text-slate-500">Nama Peminjam</p>
                            <p class="mt-1 font-semibold text-slate-800">${peminjam}</p>
                        </div>

                        <div>
                            <p class="text-slate-500">Status</p>
                            <div class="mt-1">${badgeStatus(data.status)}</div>
                        </div>

                        <div>
                            <p class="text-slate-500">Tanggal Pinjam</p>
                            <p class="mt-1 font-medium text-slate-700">${tanggalPinjam}</p>
                        </div>

                        <div>
                            <p class="text-slate-500">Tanggal Kembali</p>
                            <p class="mt-1 font-medium text-slate-700">${tanggalKembali}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <div>
                        <div class="mb-3 flex items-center gap-2">
                            <i class="fas fa-boxes-stacked text-primary"></i>
                            <h4 class="font-bold text-slate-800">Daftar Inventaris</h4>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            ${renderBarang(data.details)}
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <div class="grid grid-cols-1 gap-4 text-xs md:grid-cols-2 md:text-base">
                        <div>
                            <p class="mb-2 text-slate-500">Foto Pengambilan</p>
                            ${fotoPengambilan}
                        </div>

                        <div>
                            <p class="mb-2 text-slate-500">Foto Pengembalian</p>
                            ${fotoPengembalian}
                        </div>
                    </div>

                    ${statusPeminjaman(data.status) ? `
                        <div class="border-t border-slate-100 pt-6 text-xs md:text-sm">
                            <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-800 md:text-lg">
                                <i class="fas fa-rotate-left text-primary"></i>
                                Konfirmasi Pengembalian
                            </h3>

                            <div class="space-y-4">
                                <label for="fotoPengembalian" class="block cursor-pointer">
                                    <div class="flex h-40 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-primary hover:bg-primary/5 md:h-56">
                                        <img id="previewPengembalian" class="hidden h-full w-full object-cover">

                                        <div id="placeholderPengembalian" class="text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm">
                                                <i class="fa-solid fa-camera text-xl"></i>
                                            </div>
                                            <p class="mt-3 text-slate-500">Tap untuk mengambil foto pengembalian</p>
                                        </div>
                                    </div>
                                </label>

                                <input type="file" id="fotoPengembalian" accept="image/*" capture="environment" class="hidden">

                                <button type="button" data-id="${data.id}"
                                    class="btnKonfirmasiPengembalian flex h-12 w-full items-center justify-center rounded-xl bg-emerald-600 font-semibold text-white transition hover:bg-emerald-700">
                                    <i class="fas fa-check mr-2"></i>
                                    Selesaikan Pengembalian
                                </button>
                            </div>
                        </div>
                    ` : ''}

                </div>
            `);

            showModal('modalPeminjaman');
        });
    </script>
@endpush
