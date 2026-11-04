@extends('layouts.app')

@section('title', 'Pengurus')
@section('page', 'pengurus')
@section('page_title', 'Pengurus')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Pengurus</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola pengurus periode {{ $periode->nama }}.</p>
            </div>

            <button type="button" onclick="tambahPengurus()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Pengurus
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchPengurus" placeholder="Cari nama anggota..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">No. Anggota</th>
                            <th class="px-6 py-4">Jabatan</th>
                            <th class="px-6 py-4">Bidang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tablePengurus" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="paginationPengurus" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalPengurus"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalPengurusTitle" class="text-lg font-bold text-slate-800">Tambah Pengurus</h3>

                <button type="button" onclick="tutupModalPengurus()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formPengurus" class="space-y-5 p-6">
                <input type="hidden" id="pengurus_id">

                <input type="hidden" id="pengurus_anggota_id">

                <div>
                    <label for="pengurus_cari" class="mb-2 block text-sm font-semibold text-slate-700">Anggota</label>
                    <input type="text" id="pengurus_cari" autocomplete="off" placeholder="Cari nama atau no. anggota..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                    <p id="pengurus_terpilih" class="mt-2 text-sm font-semibold text-primary"></p>
                    <ul id="pengurus_hasil" class="mt-2 hidden max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 text-sm"></ul>
                </div>

                <div>
                    <label for="pengurus_jabatan" class="mb-2 block text-sm font-semibold text-slate-700">Jabatan</label>
                    <select id="pengurus_jabatan" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih jabatan</option>
                        @foreach ($jabatans as $j)
                            <option value="{{ $j->id }}" data-bidang="{{ $j->terkait_bidang ? 1 : 0 }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="pengurus_bidang_wrap" class="hidden">
                    <label for="pengurus_bidang" class="mb-2 block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="pengurus_bidang"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih bidang</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalPengurus()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanPengurus"
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
        const pengurusBaseUrl = @json(route('kepengurusan.pengurus.index', $periode));
        const pengurusAnggotaUrl = @json(route('kepengurusan.pengurus.anggota', $periode));
        const pengurusDataUrl = @json(route('kepengurusan.pengurus.getData', $periode));
        
        let pengurusPage = 1;
        let pengurusTimer = null;

        function escapePengurus(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlPengurus(id) {
            return `${pengurusBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function pilihAnggotaPengurus(id, label) {
            $('#pengurus_anggota_id').val(id);
            $('#pengurus_terpilih').text(label);
            $('#pengurus_cari').val('');
            $('#pengurus_hasil').addClass('hidden').empty();
        }

        function aturBidangPengurus() {
            const terkait = $('#pengurus_jabatan option:selected').data('bidang') == 1;
            $('#pengurus_bidang_wrap').toggleClass('hidden', !terkait);
            if (!terkait) $('#pengurus_bidang').val('');
        }

        $('#pengurus_jabatan').on('change', aturBidangPengurus);

        let cariAnggotaTimer = null;
        $('#pengurus_cari').on('input', function() {
            clearTimeout(cariAnggotaTimer);
            cariAnggotaTimer = setTimeout(() => {
                $.get(pengurusAnggotaUrl, { search: $('#pengurus_cari').val() }).done(function(rows) {
                    const ul = $('#pengurus_hasil').empty().removeClass('hidden');
                    if (!rows.length) {
                        ul.append('<li class="px-4 py-3 text-slate-500">Anggota tidak ditemukan.</li>');
                    }
                    rows.forEach(function(r) {
                        const label = `${r.nama} (${r.no_anggota ?? '-'})`;
                        $('<li class="cursor-pointer px-4 py-3 hover:bg-slate-50"></li>')
                            .text(`${label} - ${r.jemaat ?? '-'}`)
                            .on('click', () => pilihAnggotaPengurus(r.id, label))
                            .appendTo(ul);
                    });
                });
            }, 300);
        });

        function bukaModalPengurus() {
            $('#modalPengurus').removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModalPengurus() {
            $('#modalPengurus').addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function tambahPengurus() {
            $('#formPengurus')[0].reset();
            $('#pengurus_id').val('');
            pilihAnggotaPengurus('', '');
            aturBidangPengurus();
            $('#modalPengurusTitle').text('Tambah Pengurus');
            bukaModalPengurus();
        }

        function editPengurus(id) {
            $.get(urlPengurus(id)).done(function(item) {
                $('#formPengurus')[0].reset();
                $('#pengurus_id').val(item.id);
                pilihAnggotaPengurus(item.data_anggota_id, `${item.nama} (${item.no_anggota ?? '-'})`);
                $('#pengurus_jabatan').val(item.jabatan_id);
                aturBidangPengurus();
                $('#pengurus_bidang').val(item.bidang_id ?? '');
                $('#modalPengurusTitle').text('Edit Pengurus');
                bukaModalPengurus();
            }).fail(function() {
                Swal.fire('Gagal', 'Pengurus tidak ditemukan.', 'error');
            });
        }

        $('#pengurus_cari').on('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        $('#formPengurus').on('submit', function(e) {
            e.preventDefault();

            const id = $('#pengurus_id').val();
            const btn = $('#btnSimpanPengurus');

            $.ajax({
                url: id ? urlPengurus(id) : pengurusBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    data_anggota_id: $('#pengurus_anggota_id').val(),
                    jabatan_id: $('#pengurus_jabatan').val(),
                    bidang_id: $('#pengurus_bidang_wrap').hasClass('hidden') ? '' : $('#pengurus_bidang').val()
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalPengurus();
                    loadPengurus(pengurusPage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Pengurus gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapePengurus).join('<br>');
                    } else {
                        message = escapePengurus(message);
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

        function loadPengurus(page = 1) {
            pengurusPage = page;

            $.get(pengurusDataUrl, {
                page: page,
                search: $('#searchPengurus').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#tablePengurus').html(
                        '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Pengurus tidak ditemukan.</td></tr>'
                    );
                    $('#paginationPengurus').html('');
                    return;
                }

                $('#tablePengurus').html(data.map(function(item) {
                    const aksi = `
                        <div class="flex justify-center gap-2">
                            <button onclick="editPengurus(${item.id})" title="Edit"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="hapusPengurus(${item.id})" title="Hapus"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>`;

                    return `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">${escapePengurus(item.nama)}</td>
                        <td class="px-6 py-4">${escapePengurus(item.no_anggota ?? '-')}</td>
                        <td class="px-6 py-4">${escapePengurus(item.jabatan)}</td>
                        <td class="px-6 py-4">${escapePengurus(item.bidang ?? '-')}</td>
                        <td class="px-6 py-4 text-center">${aksi}</td>
                    </tr>`;
                }).join(''));

                renderPaginationPengurus(res);
            }).fail(function() {
                $('#tablePengurus').html(
                    '<tr><td colspan="5" class="px-6 py-10 text-center text-red-600">Gagal memuat data pengurus.</td></tr>'
                );
            });
        }

        function renderPaginationPengurus(res) {
            if (res.last_page <= 1) {
                $('#paginationPengurus').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadPengurus(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationPengurus').html(html);
        }

        function hapusPengurus(id) {
            Swal.fire({
                title: 'Hapus Pengurus?',
                text: 'Pengurus ini akan dihapus dari periode.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlPengurus(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadPengurus(pengurusPage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Pengurus gagal dihapus.', 'error');
                    }
                });
            });
        }

        document.getElementById('modalPengurus').addEventListener('click', function(e) {
            if (e.target === this) tutupModalPengurus();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') tutupModalPengurus();
        });

        $('#searchPengurus').on('input', function() {
            clearTimeout(pengurusTimer);
            pengurusTimer = setTimeout(() => loadPengurus(1), 400);
        });

        $(document).ready(() => loadPengurus());
    </script>
@endpush
