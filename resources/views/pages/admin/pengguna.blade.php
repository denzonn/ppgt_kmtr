@extends('layouts.app')

@section('title', 'Master Pengguna')
@section('page', 'pengguna')
@section('page_title', 'Master Pengguna')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Master Pengguna</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola akun pengguna, role, dan periodenya.</p>
            </div>

            <button type="button" onclick="tambahPengguna()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Pengguna
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchPengguna" placeholder="Cari nama atau email..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Password</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tablePengguna" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="paginationPengguna" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalPengguna"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalPenggunaTitle" class="text-lg font-bold text-slate-800">Tambah Pengguna</h3>

                <button type="button" onclick="tutupModalPengguna()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formPengguna" class="space-y-5 p-6">
                <input type="hidden" id="pengguna_id">

                <div>
                    <label for="pengguna_name" class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                    <input type="text" id="pengguna_name" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="pengguna_email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" id="pengguna_email" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="pengguna_password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input type="text" id="pengguna_password" minlength="6" autocomplete="off"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                    <p id="hintPassword" class="mt-1 hidden text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                <div>
                    <label for="pengguna_role" class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                    <select id="pengguna_role" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="wrapPeriode">
                    <label for="pengguna_periode" class="mb-2 block text-sm font-semibold text-slate-700">Periode</label>
                    <select id="pengguna_periode"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih periode</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalPengguna()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanPengguna"
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
        const penggunaBaseUrl = @json(route('pengguna.index'));
        const penggunaDataUrl = @json(route('pengguna.getData'));
        // role id -> apakah memuat menu kepengurusan (periode wajib)
        const roleButuhPeriode = @json($butuhPeriode);

        let penggunaPage = 1;
        let penggunaTimer = null;

        function escapePengguna(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlPengguna(id) {
            return `${penggunaBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalPengguna() {
            $('#modalPengguna').removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModalPengguna() {
            $('#modalPengguna').addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function aturPeriode() {
            const butuh = roleButuhPeriode[$('#pengguna_role').val()] === true;
            $('#wrapPeriode').toggle(butuh);
            $('#pengguna_periode').prop('required', butuh);
            if (!butuh) $('#pengguna_periode').val('');
        }

        $('#pengguna_role').on('change', aturPeriode);

        function tambahPengguna() {
            $('#formPengguna')[0].reset();
            $('#pengguna_id').val('');
            $('#pengguna_password').prop('required', true);
            $('#hintPassword').addClass('hidden');
            $('#modalPenggunaTitle').text('Tambah Pengguna');
            aturPeriode();
            bukaModalPengguna();
        }

        function editPengguna(id) {
            $.get(urlPengguna(id)).done(function(item) {
                $('#formPengguna')[0].reset();
                $('#pengguna_id').val(item.id);
                $('#pengguna_name').val(item.name);
                $('#pengguna_email').val(item.email);
                $('#pengguna_password').val('').prop('required', false);
                $('#hintPassword').removeClass('hidden');
                $('#pengguna_role').val(item.role_id);
                $('#pengguna_periode').val(item.periode_id ?? '');
                $('#modalPenggunaTitle').text('Edit Pengguna');
                aturPeriode();
                bukaModalPengguna();
            }).fail(function() {
                Swal.fire('Gagal', 'Pengguna tidak ditemukan.', 'error');
            });
        }

        $('#formPengguna').on('submit', function(e) {
            e.preventDefault();

            const id = $('#pengguna_id').val();
            const btn = $('#btnSimpanPengguna');

            $.ajax({
                url: id ? urlPengguna(id) : penggunaBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    name: $('#pengguna_name').val(),
                    email: $('#pengguna_email').val(),
                    password: $('#pengguna_password').val(),
                    role_id: $('#pengguna_role').val(),
                    periode_id: $('#pengguna_periode').val()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalPengguna();
                    loadPengguna(penggunaPage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Pengguna gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapePengguna).join('<br>');
                    } else {
                        message = escapePengguna(message);
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

        function loadPengguna(page = 1) {
            penggunaPage = page;

            $.get(penggunaDataUrl, {
                page: page,
                search: $('#searchPengguna').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tablePengguna').html(
                        '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Pengguna tidak ditemukan.</td></tr>'
                    );
                    $('#paginationPengguna').html('');
                    return;
                }

                $('#tablePengguna').html(data.map(function(item) {
                    const periode = item.periode ?
                        `${escapePengguna(item.periode.tahun_mulai)} - ${escapePengguna(item.periode.tahun_selesai)}` :
                        '<span class="text-slate-400">-</span>';

                    return `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">${escapePengguna(item.name)}</td>
                        <td class="px-6 py-4">${escapePengguna(item.email)}</td>
                        <td class="px-6 py-4 font-mono">${escapePengguna(item.password_view)}</td>
                        <td class="px-6 py-4">${escapePengguna(item.role?.nama)}</td>
                        <td class="px-6 py-4">${periode}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="editPengguna(${Number(item.id)})" title="Edit"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button onclick="hapusPengguna(${Number(item.id)})" title="Hapus"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join(''));

                renderPaginationPengguna(res);
            }).fail(function() {
                $('#tablePengguna').html(
                    '<tr><td colspan="6" class="px-6 py-10 text-center text-red-600">Gagal memuat data pengguna.</td></tr>'
                );
            });
        }

        function renderPaginationPengguna(res) {
            if (res.last_page <= 1) {
                $('#paginationPengguna').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadPengguna(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationPengguna').html(html);
        }

        function hapusPengguna(id) {
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: 'Pengguna ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlPengguna(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadPengguna(penggunaPage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Pengguna gagal dihapus.', 'error');
                    }
                });
            });
        }

        document.getElementById('modalPengguna').addEventListener('click', function(e) {
            if (e.target === this) tutupModalPengguna();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') tutupModalPengguna();
        });

        $('#searchPengguna').on('input', function() {
            clearTimeout(penggunaTimer);
            penggunaTimer = setTimeout(() => loadPengguna(1), 400);
        });

        $(document).ready(() => loadPengguna());
    </script>
@endpush
