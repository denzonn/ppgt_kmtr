@extends('layouts.app')

@section('title', 'Manajemen Role')
@section('page', 'role')
@section('page_title', 'Manajemen Role')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Manajemen Role</h2>
                <p class="mt-1 text-sm text-slate-500">Atur menu yang dapat diakses setiap role.</p>
            </div>
            <button type="button" onclick="tambahRole()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Role
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Role</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Role beserta menu yang diizinkan.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="searchRole" placeholder="Cari nama role..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80">
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Nama Role</th>
                            <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Menu</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pengguna</th>
                            <th class="text-center px-5 py-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                    </thead>
                    <tbody id="tableRole" class="divide-y divide-slate-100">
                        <tr><td colspan="4" class="px-6 py-16 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data role...</td></tr>
                    </tbody>
                </table>
            </div>

            <div id="paginationRole" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-white px-5 py-4 empty:hidden"></div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalRole"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-user-shield text-lg"></i>
                    </div>
                    <h3 id="modalRoleTitle" class="text-lg font-bold text-slate-800">Tambah Role</h3>
                </div>

                <button type="button" onclick="tutupModalRole()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formRole" class="space-y-5 p-6">
                <input type="hidden" id="role_id">

                <div>
                    <label for="role_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Role</label>
                    <input type="text" id="role_nama" placeholder="Contoh: Bendahara" required maxlength="100"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                </div>

                @foreach (['Umum' => $menuUmum, 'Kepengurusan' => $menuKepengurusan, 'Jemaat' => $menuJemaat] as $kelompok => $menus)
                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-700">Menu {{ $kelompok }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($menus as $kunci => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="menus[]" value="{{ $kunci }}"
                                        class="rounded border-slate-300 text-primary focus:ring-primary">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalRole()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanRole"
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
        const roleBaseUrl = @json(route('role.index'));
        const roleDataUrl = @json(route('role.getData'));
        const roleLabels = @json($menuUmum + $menuKepengurusan + $menuJemaat);

        let rolePage = 1;
        let roleTimer = null;

        function escapeRole(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlRole(id) {
            return `${roleBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalRole() {
            showModal('modalRole');
        }

        function tutupModalRole() {
            closeModal('modalRole');
        }

        function tambahRole() {
            $('#formRole')[0].reset();
            $('#role_id').val('');
            $('#modalRoleTitle').text('Tambah Role');
            bukaModalRole();
        }

        function editRole(id) {
            $.get(urlRole(id)).done(function(item) {
                $('#formRole')[0].reset();
                $('#role_id').val(item.id);
                $('#role_nama').val(item.nama);
                $('input[name="menus[]"]').each(function() {
                    this.checked = (item.menus || []).includes(this.value);
                });
                $('#modalRoleTitle').text('Edit Role');
                bukaModalRole();
            }).fail(function() {
                Swal.fire('Gagal', 'Role tidak ditemukan.', 'error');
            });
        }

        $('#formRole').on('submit', function(e) {
            e.preventDefault();

            const id = $('#role_id').val();
            const btn = $('#btnSimpanRole');

            $.ajax({
                url: id ? urlRole(id) : roleBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#role_nama').val(),
                    menus: $('input[name="menus[]"]:checked').map(function() {
                        return this.value;
                    }).get()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalRole();
                    loadRole(rolePage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Role gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapeRole).join('<br>');
                    } else {
                        message = escapeRole(message);
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

        function loadRole(page = 1) {
            rolePage = page;

            $.get(roleDataUrl, {
                page: page,
                search: $('#searchRole').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tableRole').html(
                        '<tr><td colspan="4" class="px-6 py-16 text-center text-sm text-slate-500">Role tidak ditemukan.</td></tr>'
                    );
                    $('#paginationRole').html('');
                    return;
                }

                $('#tableRole').html(data.map(function(item) {
                    const menus = item.is_admin ?
                        '<span class="inline-flex items-center rounded-lg bg-primary/5 px-3 py-1.5 text-xs font-bold text-primary">Semua menu</span>' :
                        (item.menus || []).map(k =>
                            `<span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">${escapeRole(roleLabels[k] ?? k)}</span>`
                        ).join('') || '<span class="text-sm text-slate-400">-</span>';
                    const aksi = item.is_admin ?
                        '<div class="text-center text-xs text-slate-400"><i class="fas fa-lock mr-1"></i>Terkunci</div>' :
                        `<div class="flex items-center justify-center gap-2">
                                <button onclick="editRole(${item.id})" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white"><i class="fas fa-pen text-xs"></i></button>
                                <button onclick="hapusRole(${item.id})" title="Hapus" class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white"><i class="fas fa-trash text-xs"></i></button>
                            </div>`;

                    return `
                    <tr class="group transition duration-150 hover:bg-primary/[0.035]">
                        <td class="px-5 py-4 font-semibold text-slate-800">${escapeRole(item.nama)}</td>
                        <td class="px-5 py-4"><div class="flex max-w-xl flex-wrap gap-1.5">${menus}</div></td>
                        <td class="px-5 py-4 text-center text-sm text-slate-600">${Number(item.users_count)}</td>
                        <td class="px-5 py-4">${aksi}</td>
                    </tr>`;
                }).join(''));

                renderPaginationRole(res);
            }).fail(function() {
                $('#tableRole').html(
                    '<tr><td colspan="4" class="px-6 py-10 text-center text-sm text-red-600">Gagal memuat data role.</td></tr>'
                );
            });
        }

        function renderPaginationRole(res) {
            if (res.last_page <= 1) {
                $('#paginationRole').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadRole(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationRole').html(html);
        }

        function hapusRole(id) {
            Swal.fire({
                title: 'Hapus Role?',
                text: 'Role ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlRole(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadRole(rolePage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Role gagal dihapus.', 'error');
                    }
                });
            });
        }

        $('#searchRole').on('input', function() {
            clearTimeout(roleTimer);
            roleTimer = setTimeout(() => loadRole(1), 400);
        });

        $(document).ready(() => loadRole());
    </script>
@endpush
