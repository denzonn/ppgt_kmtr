@extends('layouts.app')

@section('title', 'Master Bidang')
@section('page', 'bidang')
@section('page_title', 'Master Bidang')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Master Bidang</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola bidang periode {{ $periode->nama }}.</p>
            </div>

            <button type="button" onclick="tambahBidang()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Bidang
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchBidang" placeholder="Cari nama bidang..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">Nama Bidang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBidang" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="paginationBidang" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalBidang"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalBidangTitle" class="text-lg font-bold text-slate-800">Tambah Bidang</h3>

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
                    <input type="text" id="bidang_nama" required maxlength="100"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
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
            $('#modalBidang').removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModalBidang() {
            $('#modalBidang').addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
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
                    nama: $('#bidang_nama').val()
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
                        '<tr><td colspan="2" class="px-6 py-12 text-center text-slate-500">Bidang tidak ditemukan.</td></tr>'
                    );
                    $('#paginationBidang').html('');
                    return;
                }

                $('#tableBidang').html(data.map(function(item) {
                    const aksi = `
                        <div class="flex justify-center gap-2">
                            <button onclick="editBidang(${item.id})" title="Edit"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="hapusBidang(${item.id})" title="Hapus"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>`;

                    return `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">${escapeBidang(item.nama)}</td>
                        <td class="px-6 py-4 text-center">${aksi}</td>
                    </tr>`;
                }).join(''));

                renderPaginationBidang(res);
            }).fail(function() {
                $('#tableBidang').html(
                    '<tr><td colspan="2" class="px-6 py-10 text-center text-red-600">Gagal memuat data bidang.</td></tr>'
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

        document.getElementById('modalBidang').addEventListener('click', function(e) {
            if (e.target === this) tutupModalBidang();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') tutupModalBidang();
        });

        $('#searchBidang').on('input', function() {
            clearTimeout(bidangTimer);
            bidangTimer = setTimeout(() => loadBidang(1), 400);
        });

        $(document).ready(() => loadBidang());
    </script>
@endpush
