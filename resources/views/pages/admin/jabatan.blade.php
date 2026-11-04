@extends('layouts.app')

@section('title', 'Master Jabatan')
@section('page', 'jabatan')
@section('page_title', 'Master Jabatan')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Master Jabatan</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola jabatan periode {{ $periode->nama }}.</p>
            </div>

            <button type="button" onclick="tambahJabatan()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Jabatan
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchJabatan" placeholder="Cari nama jabatan..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">Nama Jabatan</th>
                            <th class="px-6 py-4 text-center">Terkait Bidang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableJabatan" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="paginationJabatan" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalJabatan"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalJabatanTitle" class="text-lg font-bold text-slate-800">Tambah Jabatan</h3>

                <button type="button" onclick="tutupModalJabatan()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formJabatan" class="space-y-5 p-6">
                <input type="hidden" id="jabatan_id">

                <div>
                    <label for="jabatan_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Jabatan</label>
                    <input type="text" id="jabatan_nama" required maxlength="100"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="jabatan_terkait" class="mb-2 block text-sm font-semibold text-slate-700">Terkait Bidang</label>
                    <select id="jabatan_terkait"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalJabatan()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanJabatan"
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
        const jabatanBaseUrl = @json(route('kepengurusan.jabatan.index', $periode));
        const jabatanDataUrl = @json(route('kepengurusan.jabatan.getData', $periode));
        
        let jabatanPage = 1;
        let jabatanTimer = null;

        function escapeJabatan(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlJabatan(id) {
            return `${jabatanBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalJabatan() {
            $('#modalJabatan').removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModalJabatan() {
            $('#modalJabatan').addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function tambahJabatan() {
            $('#formJabatan')[0].reset();
            $('#jabatan_id').val('');
            $('#modalJabatanTitle').text('Tambah Jabatan');
            bukaModalJabatan();
        }

        function editJabatan(id) {
            $.get(urlJabatan(id)).done(function(item) {
                $('#formJabatan')[0].reset();
                $('#jabatan_id').val(item.id);
                $('#jabatan_nama').val(item.nama);
                $('#jabatan_terkait').val(item.terkait_bidang ? '1' : '0');
                $('#modalJabatanTitle').text('Edit Jabatan');
                bukaModalJabatan();
            }).fail(function() {
                Swal.fire('Gagal', 'Jabatan tidak ditemukan.', 'error');
            });
        }

        $('#formJabatan').on('submit', function(e) {
            e.preventDefault();

            const id = $('#jabatan_id').val();
            const btn = $('#btnSimpanJabatan');

            $.ajax({
                url: id ? urlJabatan(id) : jabatanBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#jabatan_nama').val(),
                    terkait_bidang: $('#jabatan_terkait').val()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalJabatan();
                    loadJabatan(jabatanPage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Jabatan gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapeJabatan).join('<br>');
                    } else {
                        message = escapeJabatan(message);
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

        function loadJabatan(page = 1) {
            jabatanPage = page;

            $.get(jabatanDataUrl, {
                page: page,
                search: $('#searchJabatan').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tableJabatan').html(
                        '<tr><td colspan="3" class="px-6 py-12 text-center text-slate-500">Jabatan tidak ditemukan.</td></tr>'
                    );
                    $('#paginationJabatan').html('');
                    return;
                }

                $('#tableJabatan').html(data.map(function(item) {
                    const aksi = `
                        <div class="flex justify-center gap-2">
                            <button onclick="editJabatan(${item.id})" title="Edit"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="hapusJabatan(${item.id})" title="Hapus"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>`;

                    return `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">${escapeJabatan(item.nama)}</td>
                        <td class="px-6 py-4 text-center">${item.terkait_bidang ? 'Ya' : 'Tidak'}</td>
                        <td class="px-6 py-4 text-center">${aksi}</td>
                    </tr>`;
                }).join(''));

                renderPaginationJabatan(res);
            }).fail(function() {
                $('#tableJabatan').html(
                    '<tr><td colspan="3" class="px-6 py-10 text-center text-red-600">Gagal memuat data jabatan.</td></tr>'
                );
            });
        }

        function renderPaginationJabatan(res) {
            if (res.last_page <= 1) {
                $('#paginationJabatan').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadJabatan(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationJabatan').html(html);
        }

        function hapusJabatan(id) {
            Swal.fire({
                title: 'Hapus Jabatan?',
                text: 'Jabatan ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlJabatan(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadJabatan(jabatanPage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Jabatan gagal dihapus.', 'error');
                    }
                });
            });
        }

        document.getElementById('modalJabatan').addEventListener('click', function(e) {
            if (e.target === this) tutupModalJabatan();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') tutupModalJabatan();
        });

        $('#searchJabatan').on('input', function() {
            clearTimeout(jabatanTimer);
            jabatanTimer = setTimeout(() => loadJabatan(1), 400);
        });

        $(document).ready(() => loadJabatan());
    </script>
@endpush
