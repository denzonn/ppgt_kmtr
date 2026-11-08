@extends('layouts.app')

@section('title', 'Master Bidang')
@section('page', 'bidang')
@section('page_title', 'Master Bidang')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Master Bidang</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola bidang dan jumlah orang pada periode {{ $periode->nama }}.</p>
            </div>
            <button type="button" onclick="tambahBidang()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Bidang
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Bidang</p>
                        <h3 id="statBidang" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-sitemap text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Bidang pada periode ini</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Jumlah Orang</p>
                        <h3 id="statKuota" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Total orang yang direncanakan</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pengurus Terisi</p>
                        <h3 id="statTerisi" class="mt-2 text-2xl font-bold text-slate-800">0</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Sudah dipilih dari data anggota</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Bidang</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Bidang yang telah terdaftar.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="searchBidang" placeholder="Cari nama bidang..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80">
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Nama Bidang</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Jabatan</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Program Kerja</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Jumlah Orang</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                    </thead>
                    <tbody id="tableBidang" class="divide-y divide-slate-100">
                        <tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data bidang...</td></tr>
                    </tbody>
                </table>
            </div>

            <div id="paginationBidang" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-white px-5 py-4 empty:hidden"></div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalBidang"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-sitemap text-lg"></i>
                    </div>
                    <h3 id="modalBidangTitle" class="text-lg font-bold text-slate-800">Tambah Bidang</h3>
                </div>

                <button type="button" onclick="tutupModalBidang()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formBidang" class="space-y-5 p-6">
                <input type="hidden" id="bidang_id">

                <div>
                    <label for="bidang_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Bidang</label>
                    <input type="text" id="bidang_nama" placeholder="Contoh: Spiritualitas" required maxlength="100"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="bidang_jumlah" class="mb-2 block text-sm font-semibold text-slate-700">Jumlah Orang dalam Bidang</label>
                    <input type="number" id="bidang_jumlah" placeholder="Contoh: 4" required min="1" max="999"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalBidang()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanBidang"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold
                    text-white transition hover:bg-primary_hover disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const bidangBaseUrl = @json(route('kepengurusan.bidang.index', $periode));
        const bidangDataUrl = @json(route('kepengurusan.bidang.getData', $periode));
        
        let bidangPage = 1;
        let bidangTimer = null;

        function escapeBidang(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlBidang(id) {
            return `${bidangBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalBidang() {
            showModal('modalBidang');
        }

        function tutupModalBidang() {
            closeModal('modalBidang');
        }

        function tambahBidang() {
            $('#formBidang')[0].reset();
            $('#bidang_id').val('');
            $('#modalBidangTitle').text('Tambah Bidang');
            bukaModalBidang();
        }

        function editBidang(id) {
            $.get(urlBidang(id)).done(function(item) {
                $('#formBidang')[0].reset();
                $('#bidang_id').val(item.id);
                $('#bidang_nama').val(item.nama);
                $('#bidang_jumlah').val(item.jumlah_anggota);
                $('#modalBidangTitle').text('Edit Bidang');
                bukaModalBidang();
            }).fail(function() {
                Swal.fire('Gagal', 'Bidang tidak ditemukan.', 'error');
            });
        }

        $('#formBidang').on('submit', function(e) {
            e.preventDefault();

            const id = $('#bidang_id').val();
            const btn = $('#btnSimpanBidang');

            $.ajax({
                url: id ? urlBidang(id) : bidangBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#bidang_nama').val(),
                    jumlah_anggota: $('#bidang_jumlah').val()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalBidang();
                    loadBidang(bidangPage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Bidang gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapeBidang).join('<br>');
                    } else {
                        message = escapeBidang(message);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: message
                    });
                },
                complete: () => btn.prop('disabled', false)
            });
        });

        function loadBidang(page = 1) {
            bidangPage = page;

            $.get(bidangDataUrl, {
                page: page,
                search: $('#searchBidang').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tableBidang').html(
                        '<tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-500">Bidang tidak ditemukan.</td></tr>'
                    );
                    $('#paginationBidang').html('');
                    return;
                }

                $('#statBidang').text(res.total);
                $('#statKuota').text(data.reduce((n, b) => n + Number(b.jumlah_anggota || 0), 0));
                $('#statTerisi').text(data.reduce((n, b) => n + Number(b.pengurus_count), 0));

                $('#tableBidang').html(data.map(function(item) {
                    const terisi = Number(item.pengurus_count);
                    const kuota = item.jumlah_anggota === null ? null : Number(item.jumlah_anggota);
                    const persen = kuota ? Math.min(100, Math.round(terisi / kuota * 100)) : 0;
                    const isi = kuota === null ?
                        `<button onclick="editBidang(${item.id})" class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100">Tentukan jumlah orang</button>` :
                        `<div class="flex items-center gap-3">
                            <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-primary" style="width: ${persen}%"></div></div>
                            <span class="whitespace-nowrap text-sm font-medium text-slate-700">${terisi} / ${kuota} orang</span>
                        </div>`;

                    return `
                    <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                        <td class="px-5 py-4 font-semibold text-slate-800">${escapeBidang(item.nama)}</td>
                        <td class="px-5 py-4 text-center text-sm text-slate-600">${Number(item.jabatans_count)}</td>
                        <td class="px-5 py-4 text-center text-sm text-slate-600">${Number(item.program_kerjas_count)}</td>
                        <td class="px-5 py-4">${isi}</td>
                        <td class="px-5 py-4"><div class="flex items-center justify-center gap-2">
                                <button onclick="editBidang(${item.id})" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white"><i class="fas fa-pen text-xs"></i></button>
                                <button onclick="hapusBidang(${item.id})" title="Hapus" class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white"><i class="fas fa-trash text-xs"></i></button>
                            </div></td>
                    </tr>`;
                }).join(''));

                renderPaginationBidang(res);
            }).fail(function() {
                $('#tableBidang').html(
                    '<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-red-600">Gagal memuat data bidang.</td></tr>'
                );
            });
        }

        function renderPaginationBidang(res) {
            if (res.last_page <= 1) {
                $('#paginationBidang').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadBidang(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationBidang').html(html);
        }

        function hapusBidang(id) {
            Swal.fire({
                title: 'Hapus Bidang?',
                text: 'Bidang ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlBidang(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadBidang(bidangPage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Bidang gagal dihapus.', 'error');
                    }
                });
            });
        }

        $('#searchBidang').on('input', function() {
            clearTimeout(bidangTimer);
            bidangTimer = setTimeout(() => loadBidang(1), 400);
        });

        $(document).ready(() => loadBidang());
    </script>
@endpush
