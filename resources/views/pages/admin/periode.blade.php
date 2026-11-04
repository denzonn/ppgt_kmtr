@extends('layouts.app')

@section('title', 'Master Periode')
@section('page', 'periode')
@section('page_title', 'Master Periode')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Master Periode</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola periode kepengurusan.</p>
            </div>

            <button type="button" onclick="tambahPeriode()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Periode
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchPeriode" placeholder="Cari tahun..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4 text-center">Pengguna</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tablePeriode" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="paginationPeriode" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalPeriode"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalPeriodeTitle" class="text-lg font-bold text-slate-800">Tambah Periode</h3>

                <button type="button" onclick="tutupModalPeriode()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formPeriode" class="space-y-5 p-6">
                <input type="hidden" id="periode_id">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tahun_mulai" class="mb-2 block text-sm font-semibold text-slate-700">Tahun Mulai</label>
                        <input type="number" id="tahun_mulai" required min="1900" max="2100"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3
                        text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label for="tahun_selesai" class="mb-2 block text-sm font-semibold text-slate-700">Tahun Selesai</label>
                        <input type="number" id="tahun_selesai" required min="1900" max="2100"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3
                        text-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalPeriode()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanPeriode"
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
        const periodeBaseUrl = @json(route('periode.index'));
        const periodeDataUrl = @json(route('periode.getData'));
        const kepengurusanBaseUrl = @json(url('/kepengurusan'));

        let periodePage = 1;
        let periodeTimer = null;

        function escapePeriode(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlPeriode(id) {
            return `${periodeBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalPeriode() {
            $('#modalPeriode').removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModalPeriode() {
            $('#modalPeriode').addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function tambahPeriode() {
            $('#formPeriode')[0].reset();
            $('#periode_id').val('');
            $('#modalPeriodeTitle').text('Tambah Periode');
            bukaModalPeriode();
        }

        function editPeriode(id) {
            $.get(urlPeriode(id)).done(function(item) {
                $('#formPeriode')[0].reset();
                $('#periode_id').val(item.id);
                $('#tahun_mulai').val(item.tahun_mulai);
                $('#tahun_selesai').val(item.tahun_selesai);
                $('#modalPeriodeTitle').text('Edit Periode');
                bukaModalPeriode();
            }).fail(function() {
                Swal.fire('Gagal', 'Periode tidak ditemukan.', 'error');
            });
        }

        $('#formPeriode').on('submit', function(e) {
            e.preventDefault();

            const id = $('#periode_id').val();
            const btn = $('#btnSimpanPeriode');

            $.ajax({
                url: id ? urlPeriode(id) : periodeBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    tahun_mulai: $('#tahun_mulai').val(),
                    tahun_selesai: $('#tahun_selesai').val()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalPeriode();
                    loadPeriode(periodePage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Periode gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapePeriode).join('<br>');
                    } else {
                        message = escapePeriode(message);
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

        function loadPeriode(page = 1) {
            periodePage = page;

            $.get(periodeDataUrl, {
                page: page,
                search: $('#searchPeriode').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tablePeriode').html(
                        '<tr><td colspan="3" class="px-6 py-12 text-center text-slate-500">Periode tidak ditemukan.</td></tr>'
                    );
                    $('#paginationPeriode').html('');
                    return;
                }

                $('#tablePeriode').html(data.map(function(item) {
                    const kelola = `${kepengurusanBaseUrl}/${encodeURIComponent(item.id)}/program-kerja`;

                    return `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">${escapePeriode(item.tahun_mulai)} - ${escapePeriode(item.tahun_selesai)}</td>
                        <td class="px-6 py-4 text-center">${Number(item.users_count)}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="${kelola}" title="Kelola"
                                    class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    Kelola
                                </a>
                                <button onclick="editPeriode(${Number(item.id)})" title="Edit"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button onclick="hapusPeriode(${Number(item.id)})" title="Hapus"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join(''));

                renderPaginationPeriode(res);
            }).fail(function() {
                $('#tablePeriode').html(
                    '<tr><td colspan="3" class="px-6 py-10 text-center text-red-600">Gagal memuat data periode.</td></tr>'
                );
            });
        }

        function renderPaginationPeriode(res) {
            if (res.last_page <= 1) {
                $('#paginationPeriode').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadPeriode(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationPeriode').html(html);
        }

        function hapusPeriode(id) {
            Swal.fire({
                title: 'Hapus Periode?',
                text: 'Periode ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlPeriode(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadPeriode(periodePage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Periode gagal dihapus.', 'error');
                    }
                });
            });
        }

        document.getElementById('modalPeriode').addEventListener('click', function(e) {
            if (e.target === this) tutupModalPeriode();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') tutupModalPeriode();
        });

        $('#searchPeriode').on('input', function() {
            clearTimeout(periodeTimer);
            periodeTimer = setTimeout(() => loadPeriode(1), 400);
        });

        $(document).ready(() => loadPeriode());
    </script>
@endpush
