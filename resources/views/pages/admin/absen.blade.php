@extends('layouts.app')

@section('title', 'Absen')
@section('page', 'absen')
@section('page_title', 'Absen')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Absen Kegiatan</h2>
                <p class="mt-1 text-sm text-slate-500">Buat absen, bagikan QR Code, lalu lihat daftar yang sudah mengisi.</p>
            </div>
            <button type="button" onclick="tambahAbsen()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Buat Absen
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-white p-5">
                <div class="relative md:w-80">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="searchAbsen" placeholder="Cari nama kegiatan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                </div>
            </div>

            <div id="gridAbsen" class="grid gap-4 bg-slate-50/60 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>

            <div id="paginationAbsen" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-white px-5 py-4 empty:hidden"></div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalAbsen"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h3 id="modalAbsenTitle" class="text-lg font-bold text-slate-800">Buat Absen</h3>
                <button type="button" onclick="closeModal('modalAbsen')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formAbsen" class="space-y-5 p-6">
                <input type="hidden" id="absen_id">

                <div>
                    <label for="absen_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Kegiatan</label>
                    <input type="text" id="absen_nama" required maxlength="150" placeholder="Contoh: Ibadah Pemuda"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="absen_jabatan" class="mb-2 block text-sm font-semibold text-slate-700">Pilihan Jabatan</label>
                    <input type="text" id="absen_jabatan" required maxlength="1000" placeholder="Contoh: Ketua, Sekretaris, Anggota"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-500">Pisahkan dengan koma.</p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalAbsen')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanAbsen"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Daftar Peserta --}}
    <div id="modalPeserta"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-3xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h3 id="modalPesertaTitle" class="break-words text-lg font-bold text-slate-800"></h3>
                <button type="button" onclick="closeModal('modalPeserta')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Jenis Kelamin</th>
                            <th class="px-4 py-3 font-semibold">Asal Jemaat</th>
                            <th class="px-4 py-3 font-semibold">Alamat</th>
                            <th class="px-4 py-3 font-semibold">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody id="tabelPeserta"></tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const absenUrl = @json(route('absen.index'));
        const absenDataUrl = @json(route('absen.getData'));
        const csrfAbsen = '{{ csrf_token() }}';

        let absenPage = 1;
        let absenSearchTimer = null;
        let absenItems = {};

        function escapeAbsen(value) {
            return $('<div>').text(value ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function pesanAbsen(isi) {
            return `<div class="col-span-full py-12 text-center text-sm text-slate-500">${isi}</div>`;
        }

        function pesanGagal(xhr, bawaan) {
            const errors = xhr.responseJSON?.errors;

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || bawaan)
            });
        }

        function loadAbsen(page = 1) {
            absenPage = page;

            $.get(absenDataUrl, {
                page: page,
                search: $('#searchAbsen').val()
            }).done(function(res) {
                // Halaman terakhir bisa kosong setelah isinya dihapus.
                if (res.data.length === 0 && res.total > 0) {
                    loadAbsen(res.last_page);
                    return;
                }

                absenItems = {};
                res.data.forEach(item => absenItems[item.id] = item);

                $('#gridAbsen').html(res.data.length === 0 ? pesanAbsen('Belum ada absen.') : res.data.map(function(item) {
                    const nama = escapeAbsen(item.nama);
                    const link = escapeAbsen(item.link);

                    return `
                    <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="flex justify-center rounded-xl border border-slate-100 p-2">
                            <img src="${escapeAbsen(item.qr_url)}" alt="QR Code ${nama}" class="h-40 w-40 rounded-xl bg-white object-contain" loading="lazy">
                        </div>
                        <h4 class="mt-3 break-words font-bold text-slate-800">${nama}</h4>
                        <a href="${link}" target="_blank" rel="noopener noreferrer" class="mt-0.5 mb-3 block truncate text-xs text-slate-500 hover:text-primary">
                            <i class="fas fa-link mr-1 text-[10px]"></i>${link}
                        </a>
                        <button onclick="lihatPeserta(${item.id})"
                            class="mb-2 inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            <i class="fas fa-users text-xs"></i> ${item.peserta_count} sudah absen
                        </button>
                        <div class="mt-auto flex items-center gap-2">
                            <button onclick="unduhQrAbsen(${item.id})"
                                class="inline-flex h-9 flex-1 items-center justify-center gap-2 rounded-xl bg-primary/10 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">
                                <i class="fas fa-download text-xs"></i> Unduh QR
                            </button>
                            <button onclick="editAbsen(${item.id})" title="Edit"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button onclick="hapusAbsen(${item.id})" title="Hapus"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>`;
                }).join(''));

                let tombol = '';
                for (let i = 1; res.last_page > 1 && i <= res.last_page; i++) {
                    tombol += `<button onclick="loadAbsen(${i})" class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
                }
                $('#paginationAbsen').html(tombol);
            }).fail(function() {
                $('#gridAbsen').html(pesanAbsen('Gagal memuat data absen.'));
            });
        }

        function tambahAbsen() {
            $('#formAbsen')[0].reset();
            $('#absen_id').val('');
            $('#modalAbsenTitle').text('Buat Absen');
            showModal('modalAbsen');
        }

        function editAbsen(id) {
            const item = absenItems[id];

            $('#absen_id').val(id);
            $('#absen_nama').val(item.nama);
            $('#absen_jabatan').val(item.jabatan.join(', '));
            $('#modalAbsenTitle').text('Edit Absen');
            showModal('modalAbsen');
        }

        $('#formAbsen').on('submit', function(e) {
            e.preventDefault();

            const id = $('#absen_id').val();
            const btn = $('#btnSimpanAbsen').prop('disabled', true);

            $.ajax({
                url: id ? `${absenUrl}/${id}` : absenUrl,
                type: 'POST',
                data: {
                    _token: csrfAbsen,
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#absen_nama').val(),
                    jabatan: $('#absen_jabatan').val()
                }
            }).done(function(res) {
                closeModal('modalAbsen');
                loadAbsen(absenPage);
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                pesanGagal(xhr, 'Absen gagal disimpan.');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        function hapusAbsen(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus absen ini?',
                text: 'Daftar yang sudah absen ikut terhapus dan QR Code tidak bisa dipakai lagi.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${absenUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfAbsen,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadAbsen(absenPage);
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    pesanGagal(xhr, 'Absen gagal dihapus.');
                });
            });
        }

        function lihatPeserta(id) {
            $('#modalPesertaTitle').text(absenItems[id].nama);
            $('#tabelPeserta').html('<tr><td colspan="5" class="py-12 text-center text-slate-400">Memuat data...</td></tr>');
            showModal('modalPeserta');

            $.get(`${absenUrl}/${id}/peserta`).done(function(res) {
                $('#tabelPeserta').html(res.data.length === 0 ?
                    '<tr><td colspan="5" class="py-12 text-center text-slate-500">Belum ada yang absen.</td></tr>' :
                    res.data.map(p => `
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-3">${escapeAbsen(p.nama)}</td>
                            <td class="px-4 py-3">${escapeAbsen(p.jenis_kelamin || '-')}</td>
                            <td class="px-4 py-3">${escapeAbsen(p.asal_jemaat)}</td>
                            <td class="px-4 py-3">${escapeAbsen(p.alamat)}</td>
                            <td class="px-4 py-3">${escapeAbsen(p.jabatan)}</td>
                        </tr>`).join(''));
            }).fail(function() {
                $('#tabelPeserta').html('<tr><td colspan="5" class="py-12 text-center text-red-600">Gagal memuat data.</td></tr>');
            });
        }

        async function unduhQrAbsen(id) {
            const item = absenItems[id];

            try {
                const img = new Image();

                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                    img.src = item.qr_url;
                });

                const canvas = document.createElement('canvas');
                canvas.width = 1000;
                canvas.height = 1100;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.fillStyle = '#0F172A';
                ctx.textAlign = 'center';
                ctx.font = 'bold 38px Arial, sans-serif';
                ctx.fillText(item.nama, 500, 70, 900);

                ctx.fillStyle = '#64748B';
                ctx.font = '22px Arial, sans-serif';
                ctx.fillText('SCAN UNTUK MENGISI ABSEN', 500, 110);

                ctx.drawImage(img, 60, 160, 880, 880);

                const a = document.createElement('a');
                a.href = canvas.toDataURL('image/png');
                a.download = `QR Absen ${item.nama.replace(/[\\/:*?"<>|]/g, '-')}.png`;
                a.click();
            } catch (e) {
                Swal.fire('Gagal', 'QR Code gagal diunduh.', 'error');
            }
        }

        $('#searchAbsen').on('input', function() {
            clearTimeout(absenSearchTimer);
            absenSearchTimer = setTimeout(() => loadAbsen(1), 300);
        });

        loadAbsen();
    </script>
@endpush
