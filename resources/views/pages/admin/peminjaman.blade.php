@extends('layouts.app')

@section('title', 'Peminjaman Barang')

@section('page', 'peminjaman')

@section('page_title', 'Peminjaman Barang')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h2 class="text-lg md:text-2xl font-bold">
                    Riwayat Peminjaman
                </h2>

                <p class="text-slate-500 md:mt-1 text-xs md:text-base">
                    Kelola seluruh peminjaman barang organisasi.
                </p>

            </div>
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

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hidden md:block">

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-slate-50">
                            <tr class="text-left text-sm text-slate-600">
                                <th class="px-6 py-4 w-[30%]">Nama Barang</th>
                                <th class="px-6 py-4 ">Nama Peminjam</th>
                                <th class="px-6 py-4">Tanggal Pinjam</th>
                                <th class="px-6 py-4">Tanggal Kembali</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="tableData">

                        </tbody>

                    </table>

                </div>

            </div>

            <div id="mobileContainer" class="space-y-4 md:hidden">
                {{-- Diisi Javascript --}}
            </div>

            <div id="pagination" class="mt-5 hidden md:block"></div>

            <div id="paginationMobile" class="mt-5 md:hidden"></div>

        </div>


        <div id="emptyState" class="hidden">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">

                <div class="py-14 md:py-20 px-4 flex flex-col items-center text-center">

                    <div class="w-16 h-16 md:w-24 md:h-24 rounded-full bg-slate-100 flex items-center justify-center">

                        <i class="fa-solid fa-box-open text-2xl md:text-4xl text-slate-400"></i>

                    </div>

                    <h3 class="mt-4 text-base md:text-lg font-semibold text-slate-700">

                        Belum ada data peminjaman

                    </h3>

                    <p class="mt-2 text-xs md:text-sm text-slate-500">

                        Data peminjaman akan muncul di sini ketika ada peminjaman yang dilakukan.

                    </p>

                </div>

            </div>

        </div>

    </div>

    @include('modals.peminjaman')

@endsection

@push('addon-script')
    <script>
        let page = 1;
        let searchTimer;
        let peminjamanData = {};

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

            $.get("{{ route('peminjaman-admin.getData') }}", {
                page: page,
                search: $('#search').val()
            }, function(res) {

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

                } else {

                    $.each(res.data, function(i, item) {
                        peminjamanData[item.id] = item;
                        let inventarisHtml = '';

                        item.details.forEach(function(detail) {

                            inventarisHtml += `
                                <div class="flex items-center justify-between py-2 border-b last:border-b-0">

                                    <div>

                                        <div class="font-medium text-slate-700 text-sm md:text-base">
                                            ${detail.inventaris.nama}
                                        </div>

                                        <div class="text-[10px] md:text-xs text-slate-500">
                                            ${detail.inventaris.kode_inventaris}
                                        </div>

                                    </div>

                                    <span class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] md:text-xs font-semibold">

                                        ${detail.jumlah} Unit

                                    </span>

                                </div>
                                `;

                        });

                        html += `
                            <tr class="border-t hover:bg-slate-50 transition">

                                <td class="px-6 py-4">
                                    ${inventarisHtml}
                                </td>

                                <td class="px-6 py-4">
                                ${item.nama_peminjam}
                                </td>

                                <td class="px-6 py-4">

                                    ${formatTanggal(item.tanggal_peminjaman)}

                                </td>

                                <td class="px-6 py-4">

                                    ${
                                        item.tanggal_pengembalian
                                        ? formatTanggal(item.tanggal_pengembalian)
                                        : '-'
                                    }

                                </td>

                                <td class="px-6 py-4">

                                    ${
                                        item.status == 'dipinjam'

                                        ? `<span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">

                                                                                                                                                                                                                                                                                                    Dipinjam

                                                                                                                                                                                                                                                                                            </span>`

                                        : `<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">

                                                                                                                                                                                                                                                                                                    Dikembalikan

                                                                                                                                                                                                                                                                                            </span>`
                                    }

                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex justify-center">

                                        <button
                                            data-id="${item.id}"
                                            class="btnDetail w-9 h-9 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600">

                                            <i class="fa-solid fa-eye"></i>

                                        </button>

                                    </div>

                                </td>

                            </tr>
                            `;

                        mobileHtml += `
                                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">

                                        <div class="flex items-start justify-between">

                                            <div>

                                                <h3 class="font-semibold text-sm text-slate-800">

                                                    ${item.nama_peminjam}

                                                </h3>

                                                <p class="text-[10px] text-slate-500">

                                                    ${formatTanggal(item.tanggal_peminjaman)}

                                                </p>

                                            </div>

                                            ${
                                                item.status == 'dipinjam'
                                                    ? `<span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-semibold">
                                                                                                                                                                                                                        Dipinjam
                                                                                                                                                                                                                </span>`
                                                    : `<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-semibold">
                                                                                                                                                                                                                        Dikembalikan
                                                                                                                                                                                                                </span>`
                                            }

                                        </div>

                                        <div class="mt-4 space-y-2">

                                            ${inventarisHtml}

                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mt-4 text-[10px] md:text-sm">

                                            <div>

                                                <p class="text-slate-500">
                                                    Tgl Pinjam
                                                </p>

                                                <p class="font-medium">

                                                    ${formatTanggal(item.tanggal_peminjaman)}

                                                </p>

                                            </div>

                                            <div>

                                                <p class="text-slate-500">
                                                    Tgl Kembali
                                                </p>

                                                <p class="font-medium">

                                                    ${
                                                        item.tanggal_pengembalian
                                                            ? formatTanggal(item.tanggal_pengembalian)
                                                            : '-'
                                                    }

                                                </p>

                                            </div>

                                        </div>

                                        <div class="flex justify-center">
                                            <button
                                                data-id="${item.id}"
                                                class="btnDetail mt-4 w-fit px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs ">

                                                <i class="fa-solid fa-eye mr-2"></i>

                                                Lihat Detail

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

        $(document).on('click', '.btnDetail', function() {

            let id = $(this).data('id');

            let data = peminjamanData[id];

            if (!data) return;

            let detailHtml = '';

            data.details.forEach(function(item) {

                detailHtml += `
            <tr class="border-b">

                <td class="py-3 px-3">

                    <div class="font-medium">
                        ${item.inventaris.nama}
                    </div>

                    <div class="text-xs text-slate-500">
                        ${item.inventaris.kode_inventaris}
                    </div>

                </td>

                <td class="text-center">

                    ${item.jumlah}

                </td>

            </tr>
        `;

            });

            $('#modalBody').html(`

        <div class="space-y-6">

            <div class="grid grid-cols-2 gap-4 text-xs md:text-base">

                <div>

                    <div class="text-slate-500">
                        Nama Peminjam
                    </div>

                    <div class="font-semibold">
                        ${data.nama_peminjam}
                    </div>

                </div>

                <div>

                    <div class="text-slate-500">
                        Status
                    </div>

                    <span class="inline-flex mt-1 px-3 py-1 rounded-full text-[10px] md:text-xs font-semibold
                        ${
                            data.status == 'dipinjam'
                            ? 'bg-amber-100 text-amber-700'
                            : 'bg-green-100 text-green-700'
                        }">

                        ${data.status == 'dipinjam' ? 'Dipinjam' : 'Dikembalikan'}

                    </span>

                </div>

                <div>

                    <div class="text-slate-500">
                        Tanggal Pinjam
                    </div>

                    <div class="font-medium">
                        ${formatTanggal(data.tanggal_peminjaman)}
                    </div>

                </div>

                <div>

                    <div class="text-slate-500">
                        Tanggal Kembali
                    </div>

                    <div class="font-medium">
                        ${
                            data.tanggal_pengembalian
                            ? formatTanggal(data.tanggal_pengembalian)
                            : '-'
                        }
                    </div>

                </div>

            </div>

            <div class="text-xs md:text-base">

                <div class="font-semibold mb-3">

                    Daftar Inventaris

                </div>

                <div class="overflow-hidden rounded-xl border">

                    <table class="w-full">

                        <thead class="bg-slate-50">

                            <tr>

                                <th class="text-left px-4 py-3">
                                    Inventaris
                                </th>

                                <th class="text-center w-24">
                                    Jumlah
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            ${detailHtml}

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 text-xs md:text-base">

                <div>

                    <div class="text-slate-500 mb-2">
                        Foto Pengambilan
                    </div>

                    ${
                        data.foto_pengambilan

                        ? `<img src="/images/peminjaman/${data.foto_pengambilan}"
                                                                            class="rounded-xl border h-32 md:h-48 w-full object-cover">`

                        : '-'
                    }

                </div>

                <div>

                    <div class="text-slate-500 mb-2">
                        Foto Pengembalian
                    </div>

                    ${
                        data.foto_pengembalian

                        ? `<img src="/images/pengembalian/${data.foto_pengembalian}"
                                                                                                                                                                                                                                        class="rounded-xl border h-32 md:h-48 w-full object-cover">`

                        : `<div class="border rounded-xl h-32 md:h-48 flex items-center justify-center text-slate-400 text-xs md:text-base">

                                                                                                                                                                                                                                        Belum dikembalikan

                                                                                                                                                                                                                                    </div>`
                    }

                </div>

            </div>

            ${
    data.status == 'dipinjam'
    ? `
                                                                                                                                                                                                <div class="border-t pt-6 text-xs md:text-sm">

                                                                                                                                                                                                    <h3 class="font-semibold text-base md:text-lg mb-4">

                                                                                                                                                                                                        Konfirmasi Pengembalian

                                                                                                                                                                                                    </h3>

                                                                                                                                                                                                    <div class="space-y-4">

                                                                                                                                                                                                        <label
                                                                                                                                                                                                            for="fotoPengembalian"
                                                                                                                                                                                                            class="cursor-pointer block">

                                                                                                                                                                                                            <div
                                                                                                                                                                                                                class="border-2 border-dashed border-slate-300 rounded-2xl h-40 md:h-56 bg-slate-50 hover:bg-slate-100 flex items-center justify-center overflow-hidden">

                                                                                                                                                                                                                <img
                                                                                                                                                                                                                    id="previewPengembalian"
                                                                                                                                                                                                                    class="hidden w-full h-full object-cover">

                                                                                                                                                                                                                <div id="placeholderPengembalian" class="text-center">

                                                                                                                                                                                                                    <i class="fa-solid fa-camera text-4xl text-slate-400"></i>

                                                                                                                                                                                                                    <p class="mt-3 text-slate-500">

                                                                                                                                                                                                                        Tap untuk mengambil foto pengembalian

                                                                                                                                                                                                                    </p>

                                                                                                                                                                                                                </div>

                                                                                                                                                                                                            </div>

                                                                                                                                                                                                        </label>

                                                                                                                                                                                                        <input
                                                                                                                                                                                                            type="file"
                                                                                                                                                                                                            id="fotoPengembalian"
                                                                                                                                                                                                            accept="image/*"
                                                                                                                                                                                                            capture="environment"
                                                                                                                                                                                                            class="hidden">

                                                                                                                                                                                                        <button
                                                                                                                                                                                                            data-id="${data.id}"
                                                                                                                                                                                                            class="btnKonfirmasiPengembalian w-full h-12 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold">

                                                                                                                                                                                                            <i class="fa-solid fa-check mr-2"></i>

                                                                                                                                                                                                            Selesaikan Pengembalian

                                                                                                                                                                                                        </button>

                                                                                                                                                                                                    </div>

                                                                                                                                                                                                </div>
                                                                                                                                                                                                `
    : ''
}

        </div>

    `);

            showModal('modalPeminjaman');

        });
    </script>
@endpush
