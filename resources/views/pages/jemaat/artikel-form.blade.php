@extends('layouts.app')

@section('title', $artikel ? 'Edit Artikel' : 'Tulis Artikel')
@section('page', 'artikel')
@section('page_title', $artikel ? 'Edit Artikel' : 'Tulis Artikel')

@push('addon-style')
    @vite(['resources/js/artikel-editor.js'])
@endpush

@section('content')

    @php($sampulWajib = ! $artikel?->foto)

    <form id="formArtikel" data-sampul-wajib="{{ $sampulWajib ? 1 : 0 }}" class="space-y-5">

        <div class="flex items-center gap-3">
            <a href="{{ route('jemaat.artikel.index') }}" title="Kembali ke daftar artikel" aria-label="Kembali ke daftar artikel"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-primary/30 hover:text-primary">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-800">{{ $artikel ? 'Edit Artikel' : 'Tulis Artikel' }}</h2>
                <p class="text-sm text-slate-500">Bagikan kabar dan cerita dari {{ auth()->user()->jemaat?->nama }}.</p>
            </div>
        </div>

        @if ($artikel?->status === 'ditolak')
            <div class="flex gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <p class="min-w-0 break-words"><span class="font-semibold">Artikel ini ditolak.</span> Alasan: {{ $artikel->alasan_tolak }}</p>
            </div>
        @elseif ($artikel?->status === 'disetujui')
            <div class="flex gap-3 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-700">
                <i class="fas fa-circle-info mt-0.5"></i>
                <p>Setelah disimpan, artikel kembali berstatus Menunggu dan perlu disetujui lagi.</p>
            </div>
        @endif

        <div class="artikel-kertas rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">

            {{-- Foto sampul --}}
            <div class="relative">
            {{-- Tetap berada di tengah banner supaya pesan "pilih file" dari browser muncul di sini. --}}
            <input type="file" id="artikel_foto" accept="image/jpeg,image/png,image/jpg,image/gif" @required($sampulWajib) class="peer absolute left-1/2 top-3/4 h-px w-px opacity-0">
            <label for="artikel_foto"
                class="group relative block h-44 cursor-pointer overflow-hidden rounded-t-2xl bg-gradient-to-br from-blue-50 via-slate-50 to-cyan-50 ring-inset ring-primary transition peer-focus-visible:ring-2 md:h-80">
                {{-- Foto tampil utuh. Sisi kosongnya diisi versi buram dari foto yang sama. --}}
                <img id="artikel_preview_latar" src="{{ $artikel?->foto }}" alt="" aria-hidden="true"
                    class="{{ $artikel?->foto ? '' : 'hidden' }} absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-xl">
                <img id="artikel_preview" src="{{ $artikel?->foto }}" alt="Preview foto sampul"
                    class="{{ $artikel?->foto ? '' : 'hidden' }} relative h-full w-full object-contain">

                <span id="artikel_sampul_kosong" class="{{ $artikel?->foto ? 'hidden' : 'flex' }} absolute inset-0 flex-col items-center justify-center text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-primary shadow-md transition group-hover:scale-105">
                        <i class="fa-solid fa-image text-xl"></i>
                    </span>
                    <span class="mt-3 text-sm font-semibold text-slate-700">Tambahkan foto sampul <span class="text-red-500">*</span></span>
                    <span class="mt-0.5 text-xs text-slate-500">JPG, PNG, atau GIF. Maksimal 2 MB.</span>
                </span>

                <span id="artikel_sampul_ganti" class="{{ $artikel?->foto ? 'inline-flex' : 'hidden' }} absolute bottom-3 right-3 items-center gap-2 rounded-xl bg-white/90 px-3 py-2 text-xs font-semibold text-slate-700 shadow-md backdrop-blur transition group-hover:bg-white">
                    <i class="fa-solid fa-camera"></i>
                    Ganti foto
                </span>
            </label>
            </div>

            {{-- Judul dan penulis --}}
            <div class="space-y-4 px-5 pt-6 md:px-8 md:pt-8">
                <div>
                    <label for="artikel_judul" class="sr-only">Judul</label>
                    <input type="text" id="artikel_judul" required maxlength="200" placeholder="Tulis judul artikel..." value="{{ $artikel?->judul }}"
                        class="w-full border-0 bg-transparent p-0 text-2xl font-bold text-slate-800 placeholder:text-slate-300 focus:ring-0 md:text-3xl">
                </div>

                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-5">
                    <label for="artikel_penulis" class="flex min-w-0 flex-1 basis-56 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 transition focus-within:border-primary focus-within:bg-white focus-within:ring-4 focus-within:ring-primary/10">
                        <i class="fa-solid fa-pen-nib text-xs text-primary"></i>
                        <span class="sr-only">Nama Penulis</span>
                        <input type="text" id="artikel_penulis" required maxlength="150" placeholder="Nama penulis" value="{{ $artikel?->penulis }}"
                            class="w-full min-w-0 border-0 bg-transparent px-0 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0">
                    </label>

                    @if (auth()->user()->jemaat)
                        <span class="inline-flex items-center gap-2 rounded-xl bg-primary/5 px-3 py-2.5 text-sm font-medium text-primary">
                            <i class="fa-solid fa-church text-xs"></i>
                            {{ auth()->user()->jemaat->nama }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Isi --}}
            <div class="min-w-0">
                <label for="artikel_isi" class="sr-only">Isi Artikel</label>
                <textarea id="artikel_isi" rows="16" placeholder="Tulis isi artikel di sini"
                    class="w-full border-0 px-5 py-4 text-sm focus:ring-0 md:px-8">{{ $artikel?->isi }}</textarea>
            </div>

            {{-- Tombol selalu terlihat saat menulis artikel panjang --}}
            <div class="sticky bottom-0 z-10 flex flex-col gap-3 rounded-b-2xl border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:items-center sm:justify-between md:px-8">
                <p class="flex items-center gap-2 text-xs text-slate-500">
                    <i class="fas fa-shield-halved text-primary"></i>
                    Diperiksa Pengurus Klasis sebelum disetujui.
                </p>
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('jemaat.artikel.index') }}"
                        class="flex-1 rounded-xl border border-slate-200 px-5 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50 sm:flex-none">
                        Batal
                    </a>
                    <button type="submit" id="btnSimpanArtikel"
                        class="inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary/20 transition hover:opacity-90 disabled:opacity-60 sm:flex-none">
                        <i class="fas fa-paper-plane text-xs"></i>
                        Kirim untuk Disetujui
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('addon-script')
    <script>
        const artikelDaftarUrl = @json(route('jemaat.artikel.index'));
        const artikelSimpanUrl = @json($artikel ? route('jemaat.artikel.update', $artikel->id) : route('jemaat.artikel.store'));
        const artikelMetode = @json($artikel ? 'PUT' : 'POST');
        const csrfArtikel = '{{ csrf_token() }}';

        let editorArtikel = null;
        let adaPerubahan = false;

        // Kalau editor gagal dimuat, kotak teks biasa tetap bisa dipakai.
        document.addEventListener('DOMContentLoaded', function() {
            window.buatEditorArtikel(document.getElementById('artikel_isi'), {
                uploadUrl: @json(route('jemaat.artikel.gambar')),
                csrf: csrfArtikel
            }).then(function(editor) {
                editorArtikel = editor;
                editor.model.document.on('change:data', () => adaPerubahan = true);
            }).catch(function(error) {
                console.error(error);
            });
        });

        $('#formArtikel input, #formArtikel textarea').on('input change', () => adaPerubahan = true);

        // Tulisan panjang jangan sampai hilang karena tidak sengaja keluar dari halaman.
        window.addEventListener('beforeunload', function(e) {
            if (adaPerubahan) e.preventDefault();
        });

        $('#artikel_foto').on('change', function() {
            const file = this.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                $(this).val('');
                Swal.fire('Gagal', 'Foto harus berformat JPG, PNG, atau GIF dan maksimal 2 MB.', 'error');
                return;
            }

            $('#artikel_preview, #artikel_preview_latar').attr('src', URL.createObjectURL(file)).removeClass('hidden');
            $('#artikel_sampul_kosong').addClass('hidden').removeClass('flex');
            $('#artikel_sampul_ganti').removeClass('hidden').addClass('inline-flex');
        });

        $('#formArtikel').on('submit', function(e) {
            e.preventDefault();

            if (editorArtikel && editorArtikel.plugins.get('PendingActions').hasAny) {
                Swal.fire('Tunggu sebentar', 'Gambar di dalam artikel masih diunggah.', 'info');
                return;
            }

            const btn = $('#btnSimpanArtikel').prop('disabled', true);
            const isian = new FormData();
            const foto = $('#artikel_foto')[0].files[0];

            isian.append('_token', csrfArtikel);
            isian.append('_method', artikelMetode);
            isian.append('judul', $('#artikel_judul').val());
            isian.append('penulis', $('#artikel_penulis').val());
            isian.append('isi', editorArtikel ? editorArtikel.getData() : $('#artikel_isi').val());

            if (foto) isian.append('foto', foto);

            $.ajax({
                url: artikelSimpanUrl,
                type: 'POST',
                data: isian,
                processData: false,
                contentType: false
            }).done(function(res) {
                adaPerubahan = false;
                Swal.fire('Berhasil', res.message, 'success').then(() => location.href = artikelDaftarUrl);
            }).fail(function(xhr) {
                const errors = xhr.responseJSON?.errors;

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Artikel gagal disimpan.')
                });
            }).always(function() {
                btn.prop('disabled', false);
            });
        });
    </script>
@endpush
