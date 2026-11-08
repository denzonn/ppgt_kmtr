@extends('layouts.app')

@section('title', 'Bidang Jemaat')
@section('page', 'jemaat_bidang')
@section('page_title', 'Bidang Jemaat')

@section('content')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Bidang</h2>
                <p class="mt-1 text-sm text-slate-500">Daftar bidang di {{ auth()->user()->jemaat?->nama }}.</p>
            </div>
            <button type="button" onclick="tambahBidang()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Bidang
            </button>
        </div>

        <dl id="ringkasBidang" class="grid grid-cols-3 divide-x divide-slate-100 rounded-2xl border border-slate-200 bg-white py-4 shadow-sm">
            <div class="px-4 text-center sm:px-6">
                <dt class="text-xs text-slate-500">Bidang</dt>
                <dd id="jumlahBidang" class="mt-1 text-2xl font-bold text-slate-800">0</dd>
            </div>
            <div class="px-4 text-center sm:px-6">
                <dt class="text-xs text-slate-500">Pengurus terisi</dt>
                <dd id="jumlahTerisi" class="mt-1 text-2xl font-bold text-slate-800">0</dd>
            </div>
            <div class="px-4 text-center sm:px-6">
                <dt class="text-xs text-slate-500">Belum terisi</dt>
                <dd id="jumlahSisa" class="mt-1 text-2xl font-bold text-primary">0</dd>
            </div>
        </dl>

        <div id="daftarBidang" class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"></div>
    </div>

    <div id="modalBidang"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h3 id="modalBidangTitle" class="text-lg font-bold text-slate-800">Tambah Bidang</h3>
                <button type="button" onclick="closeModal('modalBidang')" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formBidang" class="space-y-5 p-6">
                <input type="hidden" id="bidang_id">

                <div>
                    <label for="bidang_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Bidang</label>
                    <input type="text" id="bidang_nama" required maxlength="150" placeholder="Contoh: Bidang Umum"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="bidang_jumlah" class="mb-2 block text-sm font-semibold text-slate-700">Jumlah Orang</label>
                    <input type="number" id="bidang_jumlah" required min="1" max="999" placeholder="Contoh: 5"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-500">Jumlah pengurus yang bisa diisi di bidang ini.</p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalBidang')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanBidang"
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
        const bidangUrl = @json(route('jemaat.bidang.index'));
        const bidangDataUrl = @json(route('jemaat.bidang.getData'));
        const csrfBidang = '{{ csrf_token() }}';

        let bidangItems = [];

        function escapeBidang(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function pesanGagalBidang(xhr, bawaan) {
            const errors = xhr.responseJSON?.errors;

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || bawaan)
            });
        }

        function barisBidang(item) {
            const nama = escapeBidang(item.nama);
            const jumlah = item.jumlah_anggota;
            const terisi = item.pengurus_count;
            const persen = jumlah ? Math.min(Math.round(terisi / jumlah * 100), 100) : 0;
            const penuh = jumlah !== null && terisi >= jumlah;
            const status = jumlah === null ? `${terisi} pengurus` : penuh ? 'Lengkap' : terisi === 0 ? 'Belum terisi' : `Sisa ${jumlah - terisi} orang`;

            return `
                <div class="flex flex-wrap items-center gap-4 p-4 transition hover:bg-slate-50 sm:px-6">
                    {{-- #1E3A8A adalah warna primary aplikasi. --}}
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full" style="background: conic-gradient(#1E3A8A ${persen}%, #e2e8f0 0)"
                        role="img" aria-label="${terisi} dari ${jumlah ?? terisi} pengurus terisi">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700">${jumlah === null ? terisi : `${terisi}/${jumlah}`}</span>
                    </div>

                    <div class="min-w-0 flex-1 basis-40">
                        <h4 class="break-words font-bold text-slate-800">${nama}</h4>
                        <span class="mt-1 inline-block rounded-md px-2 py-0.5 text-xs font-semibold ${penuh ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'}">${status}</span>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        @if (auth()->user()->bisaMenu('jemaat_pengurus'))
                            <a href="{{ route('jemaat.pengurus.index') }}" class="mr-2 text-sm font-semibold text-primary hover:underline">Atur pengurus</a>
                        @endif
                        <button onclick="editBidang(${item.id})" title="Edit" aria-label="Edit ${nama}"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-primary">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button onclick="hapusBidang(${item.id})" title="Hapus" aria-label="Hapus ${nama}"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-50 hover:text-red-600">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>`;
        }

        function loadBidang() {
            $.get(bidangDataUrl).done(function(res) {
                bidangItems = res.data;

                const terisi = bidangItems.reduce((n, b) => n + b.pengurus_count, 0);
                const sisa = bidangItems.reduce((n, b) => n + Math.max((b.jumlah_anggota ?? 0) - b.pengurus_count, 0), 0);

                $('#jumlahBidang').text(bidangItems.length);
                $('#jumlahTerisi').text(terisi);
                $('#jumlahSisa').text(sisa);

                $('#daftarBidang').html(bidangItems.length === 0 ? `
                    <div class="px-6 py-14 text-center">
                        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary"><i class="fa-solid fa-sitemap text-2xl"></i></span>
                        <h3 class="mt-4 font-bold text-slate-800">Belum ada bidang</h3>
                        <p class="mt-1 text-sm text-slate-500">Tambahkan bidang pertama untuk jemaat Anda.</p>
                    </div>` : bidangItems.map(barisBidang).join(''));
            }).fail(function() {
                $('#daftarBidang').html('<div class="py-12 text-center text-sm text-red-600">Gagal memuat data bidang.</div>');
            });
        }

        function tambahBidang() {
            $('#formBidang')[0].reset();
            $('#bidang_id').val('');
            $('#modalBidangTitle').text('Tambah Bidang');
            showModal('modalBidang');
        }

        function editBidang(id) {
            const item = bidangItems.find(b => b.id === id);

            $('#bidang_id').val(id);
            $('#bidang_nama').val(item.nama);
            $('#bidang_jumlah').val(item.jumlah_anggota ?? '');
            $('#modalBidangTitle').text('Edit Bidang');
            showModal('modalBidang');
        }

        $('#formBidang').on('submit', function(e) {
            e.preventDefault();

            const id = $('#bidang_id').val();
            const btn = $('#btnSimpanBidang').prop('disabled', true);

            $.ajax({
                url: id ? `${bidangUrl}/${id}` : bidangUrl,
                type: 'POST',
                data: {
                    _token: csrfBidang,
                    _method: id ? 'PUT' : 'POST',
                    nama: $('#bidang_nama').val(),
                    jumlah_anggota: $('#bidang_jumlah').val()
                }
            }).done(function(res) {
                closeModal('modalBidang');
                loadBidang();
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                pesanGagalBidang(xhr, 'Bidang gagal disimpan.');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        function hapusBidang(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus bidang ini?',
                text: bidangItems.find(b => b.id === id).nama,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${bidangUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfBidang,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadBidang();
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    pesanGagalBidang(xhr, 'Bidang gagal dihapus.');
                });
            });
        }

        loadBidang();
    </script>
@endpush
