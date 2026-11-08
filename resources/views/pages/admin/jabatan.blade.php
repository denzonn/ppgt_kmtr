@extends('layouts.app')

@section('title', 'Master Jabatan')
@section('page', 'jabatan')
@section('page_title', 'Master Jabatan')

@section('content')

    <div class="space-y-5">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Jabatan</h2>
                <p class="mt-1 text-sm text-slate-500">Susunan jabatan tiap bidang, periode {{ $periode->nama }}.</p>
            </div>
            <button type="button" onclick="tambahJabatan()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i> Tambah Jabatan
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="searchJabatan" placeholder="Cari nama jabatan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                </div>
                <select id="filterBidangJabatan" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 sm:w-80">
                    <option value="">Semua Bidang</option>
                    @foreach ($bidangs as $b)
                        <option value="{{ $b->id }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="daftarJabatan" class="divide-y divide-slate-100">
                <div class="py-12 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data jabatan...</div>
            </div>
        </div>

        <div id="paginationJabatan" class="flex flex-wrap justify-end gap-2 empty:hidden"></div>
    </div>

{{-- Modal Tambah/Edit --}}
    <div id="modalJabatan"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-id-badge text-lg"></i>
                    </div>
                    <h3 id="modalJabatanTitle" class="text-lg font-bold text-slate-800">Tambah Jabatan</h3>
                </div>

                <button type="button" onclick="tutupModalJabatan()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formJabatan" class="space-y-5 p-6">
                <input type="hidden" id="jabatan_id">

                <div>
                    <label for="jabatan_bidang" class="mb-2 block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="jabatan_bidang" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih bidang</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jabatan_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Jabatan</label>
                    <input type="text" id="jabatan_nama" placeholder="Contoh: Ketua" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
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
        const daftarBidang = @json($bidangs);
        
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
            showModal('modalJabatan');
        }

        function tutupModalJabatan() {
            closeModal('modalJabatan');
        }

        function tambahJabatan(bidangId = '') {
            $('#formJabatan')[0].reset();
            $('#jabatan_id').val('');
            $('#jabatan_bidang').val(bidangId);
            $('#modalJabatanTitle').text('Tambah Jabatan');
            bukaModalJabatan();
        }

        function editJabatan(id) {
            $.get(urlJabatan(id)).done(function(item) {
                $('#formJabatan')[0].reset();
                $('#jabatan_id').val(item.id);
                $('#jabatan_nama').val(item.nama);
                $('#jabatan_bidang').val(item.bidang_id ?? '');
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
                    bidang_id: $('#jabatan_bidang').val()
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
                search: $('#searchJabatan').val(),
                bidang_id: $('#filterBidangJabatan').val()
            }).done(function(res) {
                const data = res.data || [];

                const mencari = $('#searchJabatan').val().trim() !== '';
                const filter = $('#filterBidangJabatan').val();

                const grup = {};
                data.forEach(function(item) {
                    (grup[item.bidang_id] = grup[item.bidang_id] || []).push(item);
                });

                // Bidang tanpa jabatan tetap tampil supaya mudah diisi.
                const semuaTampil = !mencari && res.last_page === 1;
                const baris = daftarBidang.filter(b => filter ? String(b.id) === filter : (grup[b.id] || semuaTampil));
                const tanpaBidang = filter ? [] : (grup[null] || []);

                if (baris.length === 0 && tanpaBidang.length === 0) {
                    $('#daftarJabatan').html('<div class="py-12 text-center text-sm text-slate-500">Jabatan tidak ditemukan.</div>');
                    $('#paginationJabatan').html('');
                    return;
                }

                const tegel = function(item) {
                    const orang = Number(item.pengurus_count);
                    return `
                    <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-1.5 transition hover:border-primary/40 hover:shadow-sm">
                        <span class="h-2 w-2 shrink-0 rounded-full ${orang > 0 ? 'bg-primary' : 'bg-slate-300'}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="break-words text-sm font-semibold text-slate-700">${escapeJabatan(item.nama)}</div>
                            <div class="text-xs text-slate-500">${orang > 0 ? orang + ' orang' : 'Belum diisi'}</div>
                        </div>
                        <button onclick="editJabatan(${item.id})" title="Edit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-pen text-xs"></i></button>
                        <button onclick="hapusJabatan(${item.id})" title="Hapus" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </div>`;
                };

                const tombolTambah = id => `
                    <button onclick="tambahJabatan(${id})" title="Tambah jabatan di bidang ini"
                        class="flex min-h-[60px] items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 px-3 text-sm font-medium text-slate-500 transition hover:border-primary hover:bg-primary/5 hover:text-primary">
                        <i class="fas fa-plus text-xs"></i> Tambah Jabatan
                    </button>`;

                const kisi = (items, tambahan = '') => `<div class="grid flex-1 content-start gap-3 sm:grid-cols-2 xl:grid-cols-3">${items.map(tegel).join('')}${tambahan}</div>`;

                const barisTanpaBidang = tanpaBidang.length ? `
                    <section class="flex flex-col gap-3 bg-amber-50/50 p-5 md:flex-row md:gap-6">
                        <div class="md:w-56 md:shrink-0">
                            <h3 class="font-bold text-slate-800">Belum punya bidang</h3>
                            <p class="mt-1 text-xs text-slate-500">Klik edit lalu pilih bidangnya.</p>
                        </div>
                        ${kisi(tanpaBidang)}
                    </section>` : '';

                $('#daftarJabatan').html(barisTanpaBidang + baris.map(function(b) {
                    const items = grup[b.id] || [];
                    const totalOrang = items.reduce((n, x) => n + Number(x.pengurus_count), 0);
                    const isi = mencari && !items.length ?
                        '<p class="flex-1 self-center text-sm text-slate-400">Tidak ada yang cocok.</p>' :
                        kisi(items, mencari ? '' : tombolTambah(b.id));

                    return `
                    <section class="flex flex-col gap-3 p-5 md:flex-row md:gap-6">
                        <div class="md:w-56 md:shrink-0">
                            <h3 class="font-bold leading-snug text-slate-800">${escapeJabatan(b.nama)}</h3>
                            <p class="mt-1 text-xs text-slate-500">${items.length} jabatan &middot; ${totalOrang} orang</p>
                        </div>
                        ${isi}
                    </section>`;
                }).join(''));

renderPaginationJabatan(res);
            }).fail(function() {
                $('#daftarJabatan').html('<div class="py-10 text-center text-sm text-red-600">Gagal memuat data jabatan.</div>');
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

        $('#searchJabatan').on('input', function() {
            clearTimeout(jabatanTimer);
            jabatanTimer = setTimeout(() => loadJabatan(1), 400);
        });

        $('#filterBidangJabatan').on('change', () => loadJabatan(1));

        $(document).ready(() => loadJabatan());
    </script>
@endpush
