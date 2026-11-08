@extends('layouts.app')

@section('title', 'Master Pengguna')
@section('page', 'pengguna')
@section('page_title', 'Master Pengguna')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Master Pengguna</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola akun pengguna, role, dan periodenya.</p>
            </div>
            <button type="button" onclick="tambahPengguna()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Pengguna
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Pengguna</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Akun yang dapat masuk ke sistem.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="searchPengguna" placeholder="Cari nama atau email..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80">
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pengguna</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Password</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Role</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Periode / Jemaat</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                    </thead>
                    <tbody id="tablePengguna" class="divide-y divide-slate-100">
                        <tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data pengguna...</td></tr>
                    </tbody>
                </table>
            </div>

            <div id="paginationPengguna" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-white px-5 py-4 empty:hidden"></div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalPengguna"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-users-gear text-lg"></i>
                    </div>
                    <h3 id="modalPenggunaTitle" class="text-lg font-bold text-slate-800">Tambah Pengguna</h3>
                </div>

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
                    <input type="text" id="pengguna_name" placeholder="Masukkan nama pengguna" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="pengguna_email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" id="pengguna_email" placeholder="Contoh: pengurus@gmail.com" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="pengguna_password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input type="text" id="pengguna_password" placeholder="Minimal 6 karakter" minlength="6" autocomplete="off"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                    <p id="hintPassword" class="mt-1 hidden text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                <div>
                    <label for="pengguna_role" class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                    <select id="pengguna_role" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
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
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih periode</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="wrapJemaat">
                    <label for="pengguna_jemaat" class="mb-2 block text-sm font-semibold text-slate-700">Jemaat</label>
                    <select id="pengguna_jemaat"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih jemaat</option>
                        @foreach ($jemaats as $jemaat)
                            <option value="{{ $jemaat->id }}">{{ $jemaat->nama }}</option>
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
        const roleButuhJemaat = @json($butuhJemaat);

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
            showModal('modalPengguna');
        }

        function tutupModalPengguna() {
            closeModal('modalPengguna');
        }

        function aturPeriode() {
            const butuh = roleButuhPeriode[$('#pengguna_role').val()] === true;
            $('#wrapPeriode').toggle(butuh);
            $('#pengguna_periode').prop('required', butuh);
            if (!butuh) $('#pengguna_periode').val('');

            const butuhJemaat = roleButuhJemaat[$('#pengguna_role').val()] === true;
            $('#wrapJemaat').toggle(butuhJemaat);
            $('#pengguna_jemaat').prop('required', butuhJemaat);
            if (!butuhJemaat) $('#pengguna_jemaat').val('');
        }

        $('#pengguna_role').on('change', aturPeriode);

        function tambahPengguna() {
            $('#formPengguna')[0].reset();
            $('#pengguna_id').val('');
            $('#pengguna_password').prop('required', true).attr('placeholder', 'Minimal 6 karakter');
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
                $('#pengguna_password').val('').prop('required', false).attr('placeholder', 'Kosongkan jika tidak diubah');
                $('#hintPassword').removeClass('hidden');
                $('#pengguna_role').val(item.role_id);
                $('#pengguna_periode').val(item.periode_id ?? '');
                $('#pengguna_jemaat').val(item.jemaat_id ?? '');
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
                    periode_id: $('#pengguna_periode').val(),
                    jemaat_id: $('#pengguna_jemaat').val()
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
                        '<tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-500">Pengguna tidak ditemukan.</td></tr>'
                    );
                    $('#paginationPengguna').html('');
                    return;
                }

                $('#tablePengguna').html(data.map(function(item) {
                    const periode = item.periode ?
                        `${escapePengguna(item.periode.tahun_mulai)} - ${escapePengguna(item.periode.tahun_selesai)}` :
                        '<span class="text-slate-400">-</span>';
                    const inisial = String(item.name).trim().charAt(0).toUpperCase();

                    return `
                    <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">${escapePengguna(inisial)}</span>
                                <div>
                                    <div class="font-semibold text-slate-800">${escapePengguna(item.name)}</div>
                                    <div class="text-xs text-slate-500">${escapePengguna(item.email)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4"><span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs text-slate-700">${escapePengguna(item.password_view)}</span></td>
                        <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex items-center rounded-lg bg-primary/5 px-3 py-1.5 text-xs font-bold text-primary">${escapePengguna(item.role?.nama)}</span></td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">${item.jemaat ? escapePengguna(item.jemaat.nama) : periode}</td>
                        <td class="px-5 py-4"><div class="flex items-center justify-center gap-2">
                                <button onclick="editPengguna(${item.id})" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white"><i class="fas fa-pen text-xs"></i></button>
                                <button onclick="hapusPengguna(${item.id})" title="Hapus" class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white"><i class="fas fa-trash text-xs"></i></button>
                            </div></td>
                    </tr>`;
                }).join(''));

                renderPaginationPengguna(res);
            }).fail(function() {
                $('#tablePengguna').html(
                    '<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-red-600">Gagal memuat data pengguna.</td></tr>'
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

        $('#searchPengguna').on('input', function() {
            clearTimeout(penggunaTimer);
            penggunaTimer = setTimeout(() => loadPengguna(1), 400);
        });

        $(document).ready(() => loadPengguna());
    </script>
@endpush
