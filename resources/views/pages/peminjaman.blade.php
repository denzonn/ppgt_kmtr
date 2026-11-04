@extends('layouts.auth')

@section('title', 'Peminjaman Inventaris')

@section('content')
    <div class="min-h-screen bg-slate-100 pb-10">
        <div class="mx-auto w-full max-w-lg px-4 py-6 sm:py-10">

            {{-- Brand / Kembali --}}
            <div class="mb-6 flex items-center justify-between gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT"
                        class="h-9 w-9 rounded-xl object-contain shadow-sm">

                    <span class="text-sm font-bold text-slate-800 sm:text-base">
                        PPGT KMTr
                    </span>
                </a>

                <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-primary/30 hover:text-primary sm:text-sm">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Beranda
                </a>
            </div>

            {{-- Header --}}
            <div class="mb-6 text-center sm:mb-8">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary/10 shadow-sm sm:h-20 sm:w-20">
                    <i class="fa-solid fa-box-open text-primary text-2xl sm:text-3xl"></i>
                </div>

                <h1 class="mt-4 text-2xl font-bold text-slate-800 sm:mt-5 sm:text-3xl">
                    Peminjaman Inventaris
                </h1>

                <p class="mt-2 text-sm leading-relaxed text-slate-500 sm:text-base">
                    Pilih inventaris yang ingin dipinjam, tentukan jumlahnya, lalu
                    unggah foto pengambilan sebagai bukti.
                </p>
            </div>

            {{-- Card --}}
            <div class="rounded-[28px] border border-slate-200 bg-white shadow-xl">

                <form id="formPeminjaman" novalidate>
                    @csrf

                    <div class="space-y-7 p-5 sm:p-6">

                        {{-- Nama Peminjam --}}
                        <div>
                            <label for="nama_peminjam" class="mb-3 block font-semibold text-slate-800">
                                Nama Peminjam
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="nama_peminjam" id="nama_peminjam" maxlength="255"
                                autocomplete="name"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder:text-sm placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/30">

                            <p id="errorNama" class="mt-2 hidden text-xs font-medium text-red-500">
                                Nama peminjam wajib diisi.
                            </p>
                        </div>

                        {{-- Inventaris --}}
                        <div>
                            <label for="inventaris_select" class="mb-3 block font-semibold text-slate-800">
                                Inventaris
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="flex gap-2">

                                <div class="min-w-0 flex-1">
                                    <select id="inventaris_select" class="p-0">
                                        <option value=""></option>

                                        @foreach ($inventaris as $item)
                                            @php
                                                $sisa = $tersedia[$item->id] ?? null;
                                                $label = $item->nama . ($sisa !== null ? ' — sisa ' . $sisa . ' unit' : '');
                                            @endphp

                                            <option value="{{ $item->id }}"
                                                data-nama="{{ $item->nama }}"
                                                data-kode="{{ $item->kode_inventaris }}"
                                                data-tersedia="{{ $sisa === null ? '' : $sisa }}"
                                                @if ($sisa === 0) disabled @endif>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="button" id="btnTambahInventaris" title="Tambah inventaris"
                                    class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-2xl bg-primary text-white shadow transition hover:bg-primary_hover active:scale-95">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Inventaris yang sedang dipinjam tidak dapat dipilih lagi.
                            </p>
                        </div>

                        {{-- List Inventaris --}}
                        <div>
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <h4 class="font-semibold text-slate-800">
                                    Inventaris Dipilih
                                    <span id="jumlahDipilih"
                                        class="ml-1 rounded-lg bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">0</span>
                                </h4>

                                <span class="text-xs text-slate-500">
                                    Bisa memilih lebih dari satu
                                </span>
                            </div>

                            <div id="listInventaris" class="space-y-3">

                                <div id="emptyInventaris"
                                    class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">

                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-200">
                                        <i class="fa-solid fa-box-open text-2xl text-slate-500"></i>
                                    </div>

                                    <h5 class="mt-4 font-semibold text-slate-700">
                                        Belum ada inventaris dipilih
                                    </h5>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tekan tombol <b>+</b> untuk menambahkan inventaris.
                                    </p>

                                </div>

                            </div>
                        </div>

                        {{-- Foto --}}
                        <div>
                            <label for="foto_pengambilan" class="mb-3 block font-semibold text-slate-800">
                                Foto Pengambilan
                                <span class="text-red-500">*</span>
                            </label>

                            <label for="foto_pengambilan" class="block cursor-pointer">

                                <div
                                    class="relative flex h-64 flex-col items-center justify-center overflow-hidden rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-primary hover:bg-primary/5 sm:h-72">

                                    <img id="previewFoto" alt="Preview foto pengambilan"
                                        class="hidden h-full w-full object-cover">

                                    <div id="placeholderFoto" class="text-center">

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 sm:h-20 sm:w-20">
                                            <i class="fa-solid fa-camera text-2xl text-primary sm:text-3xl"></i>
                                        </div>

                                        <p class="mt-4 text-base font-semibold sm:mt-5 sm:text-lg">
                                            Tap untuk mengambil foto
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Kamera akan langsung dibuka
                                        </p>
                                    </div>

                                    {{-- Tombol ganti / hapus foto --}}
                                    <button type="button" id="btnHapusFoto"
                                        class="absolute right-3 top-3 hidden h-9 w-9 items-center justify-center rounded-xl bg-white/90 text-slate-600 shadow-sm backdrop-blur transition hover:bg-white hover:text-red-600">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>

                                </div>

                            </label>

                            <input id="foto_pengambilan" name="foto_pengambilan" type="file"
                                accept="image/*" capture="environment" class="hidden">

                            <p id="errorFoto" class="mt-2 hidden text-xs font-medium text-red-500">
                                Foto pengambilan wajib diunggah.
                            </p>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="sticky bottom-0 rounded-b-[28px] border-t border-slate-200 bg-white/95 p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] backdrop-blur sm:p-6 sm:pb-6">

                        <button type="submit" id="btnSubmit"
                            class="flex h-14 w-full items-center justify-center rounded-2xl bg-primary text-lg font-semibold text-white shadow-md transition hover:bg-primary_hover active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70">

                            <i class="fa-solid fa-paper-plane mr-2"></i>

                            Ajukan Peminjaman

                        </button>

                        <p class="mt-3 text-center text-xs text-slate-400">
                            Pengajuan akan diverifikasi oleh admin.
                        </p>

                    </div>

                </form>

            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                PPGT KMTr &middot; Formulir Peminjaman Inventaris
            </p>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        const maksUkuranFoto = 4 * 1024 * 1024;

        let inventarisIndex = 0;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [char];
            });
        }

        function tampilkanPesan(text, icon = 'warning') {
            Swal.fire({
                icon: icon,
                text: text
            });
        }

        // ==========================================
        // SELECT2
        // ==========================================

        $('#inventaris_select').select2({
            placeholder: 'Cari inventaris...',
            width: '100%',
            templateResult: function(item) {
                if (!item.id) return item.text;

                const kode = $(item.element).data('kode');
                const sisa = $(item.element).data('tersedia');

                let detail = '';

                if (kode) {
                    detail += '<span class="block text-[11px] text-slate-400">' + escapeHtml(kode) + '</span>';
                }

                if (sisa !== '' && sisa !== undefined) {
                    detail += '<span class="block text-[11px] font-semibold ' +
                        (Number(sisa) > 0 ? 'text-emerald-600' : 'text-red-500') + '">Sisa ' +
                        escapeHtml(sisa) + ' unit</span>';
                }

                return $('<div>').html(
                    '<span class="block truncate text-sm font-medium text-slate-700">' +
                    escapeHtml(item.text.split(' — ')[0]) + '</span>' + detail
                );
            }
        });

        // ==========================================
        // FOTO
        // ==========================================

        $('#foto_pengambilan').on('change', function() {

            const file = this.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                $(this).val('');
                tampilkanPesan('Format foto tidak didukung.');
                return;
            }

            if (file.size > maksUkuranFoto) {
                $(this).val('');
                tampilkanPesan('Ukuran foto maksimal 4 MB.');
                return;
            }

            const reader = new FileReader();

            reader.onload = function(e) {
                $('#previewFoto')
                    .attr('src', e.target.result)
                    .removeClass('hidden');

                $('#placeholderFoto').addClass('hidden');
                $('#btnHapusFoto').removeClass('hidden').addClass('flex');
                $('#errorFoto').addClass('hidden');
            };

            reader.readAsDataURL(file);

        });

        $('#btnHapusFoto').on('click', function(e) {

            e.preventDefault();
            e.stopPropagation();

            $('#foto_pengambilan').val('');
            $('#previewFoto').attr('src', '').addClass('hidden');
            $('#placeholderFoto').removeClass('hidden');
            $('#btnHapusFoto').addClass('hidden').removeClass('flex');

        });

        // ==========================================
        // TAMBAH / HAPUS INVENTARIS
        // ==========================================

        $(document).ready(function() {

            $('#btnTambahInventaris').click(function() {

                const option = $('#inventaris_select option:selected');
                const id = option.val();

                if (!id) {
                    tampilkanPesan('Silakan pilih inventaris terlebih dahulu.');
                    return;
                }

                if ($('#inventaris_' + id).length) {
                    tampilkanPesan('Inventaris sudah dipilih.');
                    return;
                }

                const nama = option.data('nama') || '-';
                const kode = option.data('kode') || '';
                const sisa = option.data('tersedia');
                const maks = (sisa === '' || sisa === undefined || sisa === null) ? '' : Number(sisa);

                $('#emptyInventaris').hide();

                const index = inventarisIndex++;

                const html = `
                    <div id="inventaris_${id}" data-id="${id}"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

                        <input type="hidden" name="inventaris[${index}][id]" value="${escapeHtml(id)}">

                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0 flex-1">
                                <p class="break-words font-semibold text-slate-800">
                                    ${escapeHtml(nama)}
                                </p>

                                <p class="mt-0.5 break-all text-xs text-slate-500">
                                    ${escapeHtml(kode)}
                                </p>
                            </div>

                            <button type="button" title="Hapus dari daftar"
                                class="hapusInventaris flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Jumlah yang dipinjam
                            </label>

                            <div class="flex items-center gap-2">

                                <button type="button" class="kurangiInventaris flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 active:scale-95">
                                    <i class="fa-solid fa-minus text-xs"></i>
                                </button>

                                <input type="number" inputmode="numeric" min="1"
                                    ${maks > 0 ? `max="${maks}"` : ''}
                                    value="1"
                                    name="inventaris[${index}][jumlah]"
                                    class="h-12 w-full min-w-0 rounded-xl border border-slate-300 px-4 text-center font-semibold"
                                    required>

                                <button type="button" class="tambahJumlahInventaris flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 active:scale-95">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>
                            </div>

                            ${maks > 0 ? `
                                <p class="mt-2 text-xs text-slate-400">
                                    Maksimal <span class="font-semibold text-slate-500">${maks}</span> unit.
                                </p>
                            ` : ''}
                        </div>

                    </div>
                `;

                $('#listInventaris').append(html);

                $('#inventaris_select')
                    .val('')
                    .trigger('change');

                reindexInventaris();
                perbaruiJumlahDipilih();

            });

            // Hapus inventaris
            $(document).on('click', '.hapusInventaris', function() {

                $(this)
                    .closest('[id^="inventaris_"]')
                    .remove();

                if ($('#listInventaris > [id^="inventaris_"]').length === 0) {
                    $('#emptyInventaris').show();
                }

                reindexInventaris();
                perbaruiJumlahDipilih();

            });

            // Jumlah: naik / turun
            $(document).on('click', '.kurangiInventaris', function() {

                const input = $(this).siblings('input');
                const nilai = Math.max(1, (parseInt(input.val(), 10) || 1) - 1);

                input.val(nilai).trigger('input');

            });

            $(document).on('click', '.tambahJumlahInventaris', function() {

                const input = $(this).siblings('input');
                const maks = parseInt(input.attr('max'), 10);
                let nilai = (parseInt(input.val(), 10) || 0) + 1;

                if (!isNaN(maks) && nilai > maks) {
                    nilai = maks;
                    tampilkanPesan('Jumlah melebihi unit yang tersedia.');
                }

                input.val(nilai).trigger('input');

            });

        });

        function perbaruiJumlahDipilih() {
            $('#jumlahDipilih').text($('#listInventaris > [id^="inventaris_"]').length);
        }

        // Jaga indeks input tetap berurutan (0,1,2...) agar data terkirim
        // sebagai array list utuh ke server.
        function reindexInventaris() {
            $('#listInventaris > [id^="inventaris_"]').each(function(index) {

                $(this).find('input[name$="[id]"]')
                    .attr('name', 'inventaris[' + index + '][id]');

                $(this).find('input[name$="[jumlah]"]')
                    .attr('name', 'inventaris[' + index + '][jumlah]');

            });
        }

        // ==========================================
        // SUBMIT
        // ==========================================

        $('#formPeminjaman').submit(function(e) {

            e.preventDefault();

            let valid = true;

            // Nama peminjam
            if (!$('#nama_peminjam').val().trim()) {
                $('#errorNama').removeClass('hidden');
                $('#nama_peminjam').addClass('border-red-400 focus:border-red-400 focus:ring-red-200');
                valid = false;
            } else {
                $('#errorNama').addClass('hidden');
                $('#nama_peminjam').removeClass('border-red-400 focus:border-red-400 focus:ring-red-200');
            }

            // Minimal satu inventaris
            if ($('#listInventaris > [id^="inventaris_"]').length === 0) {
                tampilkanPesan('Silakan pilih minimal satu inventaris.');
                valid = false;
            }

            // Jumlah minimal 1 unit
            let jumlahTidakValid = false;

            $('#listInventaris input[name$="[jumlah]"]').each(function() {

                const nilai = parseInt($(this).val(), 10);

                if (!nilai || nilai < 1) {
                    jumlahTidakValid = true;
                    $(this).addClass('border-red-400');
                } else {
                    $(this).removeClass('border-red-400');
                }

            });

            if (jumlahTidakValid) {
                tampilkanPesan('Jumlah peminjaman minimal 1 unit.');
                valid = false;
            }

            // Foto
            if ($('#foto_pengambilan')[0].files.length === 0) {
                $('#errorFoto').removeClass('hidden');
                valid = false;
            } else {
                $('#errorFoto').addClass('hidden');
            }

            if (!valid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    text: 'Periksa kembali isian yang masih kosong.'
                });
                return;
            }

            const btn = $('#btnSubmit');

            btn
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin mr-2"></i>Mengirim...');

            const formData = new FormData(this);

            $.ajax({

                url: "{{ route('peminjaman.store') }}",

                type: "POST",

                data: formData,

                processData: false,

                contentType: false,

                success: function(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Pengajuan peminjaman berhasil dikirim.'
                    });

                    // Reset form
                    $('#formPeminjaman')[0].reset();

                    $('#inventaris_select')
                        .val(null)
                        .trigger('change');

                    $('#listInventaris')
                        .find('[id^="inventaris_"]')
                        .remove();

                    $('#emptyInventaris').show();

                    $('#previewFoto')
                        .attr('src', '')
                        .addClass('hidden');

                    $('#placeholderFoto')
                        .removeClass('hidden');

                    $('#btnHapusFoto')
                        .addClass('hidden')
                        .removeClass('flex');

                    $('#errorNama, #errorFoto').addClass('hidden');

                    inventarisIndex = 0;

                    perbaruiJumlahDipilih();

                    window.scrollTo({ top: 0, behavior: 'smooth' });

                },

                error: function(xhr) {

                    const errors = xhr.responseJSON?.errors || {};
                    let message = xhr.responseJSON?.message || 'Terjadi kesalahan.';

                    if (xhr.status === 422 && Object.keys(errors).length > 0) {
                        message = Object.values(errors)
                            .flat()
                            .map(function(item) {
                                return item;
                            })
                            .join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: message
                    });

                },

                complete: function() {

                    btn
                        .prop('disabled', false)
                        .html('<i class="fa-solid fa-paper-plane mr-2"></i>Ajukan Peminjaman');

                }

            });

        });
    </script>
@endpush
