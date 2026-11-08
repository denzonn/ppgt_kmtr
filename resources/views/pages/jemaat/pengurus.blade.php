@extends('layouts.app')

@section('title', 'Pengurus Jemaat')
@section('page', 'jemaat_pengurus')
@section('page_title', 'Pengurus Jemaat')

@section('content')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Pengurus</h2>
                <p class="mt-1 text-sm text-slate-500">Daftar pengurus {{ auth()->user()->jemaat?->nama }}, diambil dari data anggota.</p>
            </div>
            <button type="button" id="btnTambahPengurus" onclick="tambahPengurus()"
                class="hidden items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Pengurus
            </button>
        </div>

        <div id="daftarPengurus" class="space-y-6"></div>
    </div>

    <div id="modalPengurus"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h3 id="modalPengurusTitle" class="text-lg font-bold text-slate-800">Tambah Pengurus</h3>
                <button type="button" onclick="closeModal('modalPengurus')" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formPengurus" class="space-y-5 p-6">
                <input type="hidden" id="pengurus_id">
                <input type="hidden" id="pengurus_anggota_id">

                <div>
                    <label for="pengurus_cari" class="mb-2 block text-sm font-semibold text-slate-700">Anggota</label>
                    <input type="text" id="pengurus_cari" autocomplete="off" placeholder="Cari nama atau no. anggota..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                    <p id="pengurus_terpilih" class="mt-2 text-sm font-semibold text-primary"></p>
                    <ul id="pengurus_hasil" class="mt-2 hidden max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 text-sm"></ul>
                </div>

                <div>
                    <label for="pengurus_bidang" class="mb-2 block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="pengurus_bidang" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary"></select>
                </div>

                <div>
                    <label for="pengurus_jabatan" class="mb-2 block text-sm font-semibold text-slate-700">Jabatan</label>
                    <input type="text" id="pengurus_jabatan" required maxlength="150" placeholder="Contoh: Ketua"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="pengurus_foto" class="mb-2 block text-sm font-semibold text-slate-700">
                        Foto <span class="text-xs text-slate-400">(opsional)</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <img id="pengurus_preview" alt="Preview foto pengurus" class="hidden h-16 w-16 shrink-0 rounded-full object-cover">
                        <span id="pengurus_preview_kosong" class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fa-solid fa-user text-xl"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <input type="file" id="pengurus_foto" accept="image/jpeg,image/png,image/jpg,image/gif" class="block w-full text-sm text-slate-600">
                            <p class="mt-1 text-xs text-slate-500">JPG, PNG, GIF. Maksimal 2 MB.</p>
                            <button type="button" id="btnHapusFoto" class="mt-1 hidden text-xs font-semibold text-red-600">Hapus foto</button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalPengurus')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanPengurus"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const pengurusUrl = @json(route('jemaat.pengurus.index'));
        const pengurusDataUrl = @json(route('jemaat.pengurus.getData'));
        const pengurusAnggotaUrl = @json(route('jemaat.pengurus.anggota'));
        const bidangHalamanUrl = @json(route('jemaat.bidang.index'));
        const csrfPengurus = '{{ csrf_token() }}';

        let pengurusItems = [];
        let bidangItems = [];
        let hapusFoto = false;
        let cariTimer = null;

        function escapePengurus(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function pesanGagalPengurus(xhr, bawaan) {
            const errors = xhr.responseJSON?.errors;

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || bawaan)
            });
        }

        function bidangPenuh(b) {
            return b.jumlah_anggota !== null && b.terisi >= b.jumlah_anggota;
        }

        function kartuPengurus(item) {
            const inisial = String(item.nama).trim().split(/\s+/).slice(0, 2).map(k => k[0]).join('').toUpperCase();

            return `
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-primary/40 hover:shadow-sm">
                    ${item.foto ?
                        `<img src="${escapePengurus(item.foto)}" alt="Foto ${escapePengurus(item.nama)}" class="h-11 w-11 shrink-0 rounded-full object-cover" loading="lazy">` :
                        `<span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">${escapePengurus(inisial)}</span>`}
                    <div class="min-w-0 flex-1">
                        <div class="break-words text-sm font-semibold text-slate-800">${escapePengurus(item.nama)}</div>
                        <div class="break-words text-xs text-primary">${escapePengurus(item.jabatan)}</div>
                        <div class="text-[11px] text-slate-400">${escapePengurus(item.no_anggota ?? 'Tanpa no. anggota')}</div>
                    </div>
                    <button onclick="editPengurus(${item.id})" title="Edit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-pen text-xs"></i></button>
                    <button onclick="hapusPengurus(${item.id})" title="Hapus" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </div>`;
        }

        function bagianBidang(b) {
            const isi = pengurusItems.filter(p => p.jemaat_bidang_id === b.id);
            const ringkasan = b.jumlah_anggota === null ? `${b.terisi} orang` : `${b.terisi} dari ${b.jumlah_anggota} orang`;
            const tambah = bidangPenuh(b) ? '' : `
                <button onclick="tambahPengurus(${b.id})" title="Tambah pengurus di bidang ini"
                    class="flex min-h-[70px] items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 px-3 text-sm font-medium text-slate-500 transition hover:border-primary hover:bg-primary/5 hover:text-primary">
                    <i class="fas fa-plus text-xs"></i> Tambah Pengurus
                </button>`;

            return `
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="break-words font-bold text-slate-800">${escapePengurus(b.nama)}</h3>
                        <span class="whitespace-nowrap text-xs font-medium ${bidangPenuh(b) ? 'text-primary' : 'text-slate-500'}">${escapePengurus(ringkasan)}</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">${isi.map(kartuPengurus).join('')}${tambah}</div>
                </section>`;
        }

        function loadPengurus() {
            $.get(pengurusDataUrl).done(function(res) {
                pengurusItems = res.data;
                bidangItems = res.bidang;

                $('#btnTambahPengurus').toggleClass('hidden', bidangItems.length === 0).toggleClass('inline-flex', bidangItems.length > 0);

                $('#daftarPengurus').html(bidangItems.length === 0 ?
                    `<div class="rounded-2xl border border-slate-200 bg-white py-12 text-center text-sm text-slate-500">
                        Belum ada bidang. <a href="${escapePengurus(bidangHalamanUrl)}" class="font-semibold text-primary">Tambahkan bidang dulu</a> sebelum mengisi pengurus.
                    </div>` :
                    bidangItems.map(bagianBidang).join(''));
            }).fail(function() {
                $('#daftarPengurus').html('<div class="py-12 text-center text-sm text-red-600">Gagal memuat data pengurus.</div>');
            });
        }

        // Bidang penuh tidak bisa dipilih, kecuali bidang pengurus yang sedang diubah.
        function isiPilihanBidang(terpilih = '') {
            $('#pengurus_bidang').html('<option value="">Pilih bidang</option>' + bidangItems.map(function(b) {
                const penuh = bidangPenuh(b) && String(b.id) !== String(terpilih);
                const ringkasan = b.jumlah_anggota === null ? `${b.terisi} orang` : `${b.terisi} dari ${b.jumlah_anggota} orang`;

                return `<option value="${b.id}" ${penuh ? 'disabled' : ''}>${escapePengurus(b.nama)} (${escapePengurus(ringkasan)})</option>`;
            }).join('')).val(terpilih);
        }

        function pilihAnggota(id, label) {
            $('#pengurus_anggota_id').val(id);
            $('#pengurus_terpilih').text(label);
            $('#pengurus_cari').val('');
            $('#pengurus_hasil').addClass('hidden').empty();
        }

        $('#pengurus_cari').on('input', function() {
            clearTimeout(cariTimer);
            cariTimer = setTimeout(function() {
                $.get(pengurusAnggotaUrl, { search: $('#pengurus_cari').val() }).done(function(rows) {
                    const ul = $('#pengurus_hasil').empty().removeClass('hidden');

                    if (!rows.length) {
                        ul.append('<li class="px-4 py-3 text-slate-500">Anggota tidak ditemukan.</li>');
                    }

                    rows.forEach(function(r) {
                        const label = `${r.nama} (${r.no_anggota ?? '-'})`;

                        $('<li class="cursor-pointer px-4 py-3 hover:bg-slate-50"></li>')
                            .text(label)
                            .on('click', () => pilihAnggota(r.id, label))
                            .appendTo(ul);
                    });
                });
            }, 300);
        });

        $('#pengurus_cari').on('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        function tampilkanFoto(url) {
            $('#pengurus_preview').attr('src', url || '').toggleClass('hidden', !url);
            $('#pengurus_preview_kosong').toggleClass('hidden', Boolean(url)).toggleClass('flex', !url);
            $('#btnHapusFoto').toggleClass('hidden', !url);
        }

        $('#pengurus_foto').on('change', function() {
            const file = this.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                $(this).val('');
                Swal.fire('Gagal', 'Foto harus berformat JPG, PNG, atau GIF dan maksimal 2 MB.', 'error');
                return;
            }

            hapusFoto = false;
            tampilkanFoto(URL.createObjectURL(file));
        });

        $('#btnHapusFoto').on('click', function() {
            $('#pengurus_foto').val('');
            hapusFoto = true;
            tampilkanFoto(null);
        });

        function tambahPengurus(bidangId = '') {
            $('#formPengurus')[0].reset();
            $('#pengurus_id').val('');
            pilihAnggota('', '');
            hapusFoto = false;
            tampilkanFoto(null);
            isiPilihanBidang(bidangId);
            $('#modalPengurusTitle').text('Tambah Pengurus');
            showModal('modalPengurus');
        }

        function editPengurus(id) {
            const item = pengurusItems.find(p => p.id === id);

            $('#formPengurus')[0].reset();
            $('#pengurus_id').val(id);
            pilihAnggota(item.data_anggota_id, `${item.nama} (${item.no_anggota ?? '-'})`);
            isiPilihanBidang(item.jemaat_bidang_id);
            $('#pengurus_jabatan').val(item.jabatan);
            hapusFoto = false;
            tampilkanFoto(item.foto);
            $('#modalPengurusTitle').text('Edit Pengurus');
            showModal('modalPengurus');
        }

        $('#formPengurus').on('submit', function(e) {
            e.preventDefault();

            if (!$('#pengurus_anggota_id').val()) {
                Swal.fire('Gagal', 'Pilih anggota dari hasil pencarian.', 'error');
                return;
            }

            const id = $('#pengurus_id').val();
            const btn = $('#btnSimpanPengurus').prop('disabled', true);
            const isian = new FormData();
            const foto = $('#pengurus_foto')[0].files[0];

            isian.append('_token', csrfPengurus);
            isian.append('_method', id ? 'PUT' : 'POST');
            isian.append('jemaat_bidang_id', $('#pengurus_bidang').val());
            isian.append('data_anggota_id', $('#pengurus_anggota_id').val());
            isian.append('jabatan', $('#pengurus_jabatan').val());

            if (foto) isian.append('foto', foto);
            if (hapusFoto) isian.append('hapus_foto', '1');

            $.ajax({
                url: id ? `${pengurusUrl}/${id}` : pengurusUrl,
                type: 'POST',
                data: isian,
                processData: false,
                contentType: false
            }).done(function(res) {
                closeModal('modalPengurus');
                loadPengurus();
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                pesanGagalPengurus(xhr, 'Pengurus gagal disimpan.');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        function hapusPengurus(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus pengurus ini?',
                text: pengurusItems.find(p => p.id === id).nama,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${pengurusUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfPengurus,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadPengurus();
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    pesanGagalPengurus(xhr, 'Pengurus gagal dihapus.');
                });
            });
        }

        loadPengurus();
    </script>
@endpush
