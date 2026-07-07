@extends('layouts.auth')

@section('title', 'Peminjaman Inventaris')

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="min-h-screen bg-slate-100 py-8">
        <div class="max-w-lg mx-auto px-4">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-primary/10 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-box-open text-primary text-3xl"></i>
                </div>

                <h1 class="mt-5 text-3xl font-bold text-slate-800">
                    Peminjaman Inventaris
                </h1>

                <p class="mt-2 text-slate-500 leading-relaxed">
                    Silakan pilih inventaris yang ingin dipinjam kemudian unggah foto
                    pengambilan sebagai bukti.
                </p>
            </div>

            {{-- Card --}}
            <div class="bg-white rounded-[28px] shadow-xl border border-slate-200 overflow-hidden">

                <form id="formPeminjaman">
                    @csrf

                    <div class="p-6 space-y-7">

                        {{-- Nama Peminjam --}}
                        <div>

                            <label class="block font-semibold text-slate-800 mb-3">
                                Nama Peminjam
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="nama_peminjam" id="nama_peminjam"
                                class="bg-white border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-400 text-slate-800 placeholder:text-sm w-full rounded-2xl py-3 px-4"
                                placeholder="Masukkan nama peminjam">

                        </div>

                        {{-- Inventaris --}}
                        <div>

                            <label class="block font-semibold text-slate-800 mb-3">
                                Inventaris
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="flex gap-2">

                                <div class="flex-1">

                                    <select id="inventaris_select" class="p-0">

                                        <option value=""></option>

                                        @foreach ($inventaris as $item)
                                            <option value="{{ $item->id }}" data-nama="{{ $item->nama }}">
                                                {{ $item->nama }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <button type="button" id="btnTambahInventaris"
                                    class=" px-6 rounded-2xl bg-primary hover:bg-primary_hover text-white font-semibold shadow transition">

                                    <i class="fa fa-plus mr-1"></i>

                                </button>

                            </div>

                        </div>

                        {{-- List Inventaris --}}
                        <div>

                            <div class="flex items-center justify-between mb-3">

                                <h4 class="font-semibold text-slate-800">
                                    Inventaris Dipilih
                                </h4>

                                <span class="text-xs text-slate-500">
                                    Bisa memilih lebih dari satu
                                </span>

                            </div>

                            <div id="listInventaris" class="space-y-3">

                                <div id="emptyInventaris"
                                    class="border-2 border-dashed border-slate-300 rounded-2xl py-10 px-5 text-center bg-slate-50">

                                    <div
                                        class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center mx-auto">

                                        <i class="fa-solid fa-box-open text-slate-500 text-2xl"></i>

                                    </div>

                                    <h5 class="font-semibold text-slate-700 mt-4">
                                        Belum ada inventaris dipilih
                                    </h5>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Tekan tombol <b>Tambah</b> untuk memasukkan inventaris.
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- Foto --}}
                        <div>

                            <label class="block font-semibold mb-3">
                                Foto Pengambilan
                                <span class="text-red-500">*</span>
                            </label>

                            <label for="foto_pengambilan" class="block cursor-pointer">

                                <div
                                    class="h-72 rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 hover:border-primary hover:bg-blue-50 transition overflow-hidden flex flex-col items-center justify-center">

                                    <img id="previewFoto" class="hidden w-full h-full object-cover">

                                    <div id="placeholderFoto" class="text-center">

                                        <div
                                            class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto">

                                            <i class="fa-solid fa-camera text-primary text-3xl"></i>

                                        </div>

                                        <p class="mt-5 font-semibold text-lg">
                                            Tap untuk mengambil foto
                                        </p>

                                        <p class="text-slate-500 text-sm">
                                            Kamera akan langsung dibuka
                                        </p>

                                    </div>

                                </div>

                            </label>

                            <input id="foto_pengambilan" name="foto_pengambilan" type="file" accept="image/*"
                                capture="environment" class="hidden">

                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-slate-200 p-6 bg-slate-50">

                        <button id="btnSubmit"
                            class="w-full h-14 rounded-2xl bg-primary hover:bg-primary_hover text-white font-semibold text-lg shadow-md transition">

                            <i class="fa-solid fa-paper-plane mr-2"></i>

                            Ajukan Peminjaman

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let inventarisIndex = 0;

        $('#inventaris_select').select2({
            placeholder: 'Cari inventaris...',
            width: '100%'
        });

        // Preview Foto
        $('#foto_pengambilan').on('change', function() {

            const file = this.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                $('#previewFoto')
                    .attr('src', e.target.result)
                    .removeClass('hidden');

                $('#placeholderFoto').addClass('hidden');
            }

            reader.readAsDataURL(file);

        });

        $(document).ready(function() {
            // Tambah Inventaris
            $('#btnTambahInventaris').click(function() {
                let option = $('#inventaris_select option:selected');

                let id = option.val();

                if (!id) {

                    Swal.fire({
                        icon: 'warning',
                        text: 'Silakan pilih inventaris terlebih dahulu.'
                    });

                    return;

                }

                if ($('#inventaris_' + id).length) {

                    Swal.fire({
                        icon: 'warning',
                        text: 'Inventaris sudah dipilih.'
                    });

                    return;

                }

                let nama = option.data('nama');

                $('#emptyInventaris').hide();

                let index = inventarisIndex++;

                let html = `
<div id="inventaris_${id}"
    class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">

    <input
        type="hidden"
        name="inventaris[${index}][id]"
        value="${id}">

    <div class="flex justify-between items-start">

        <div class="flex-1 pr-3">

            <div class="font-semibold text-slate-800">
                ${nama}
            </div>

            <div class="text-sm text-slate-500 mt-1">
                Jumlah yang dipinjam
            </div>

        </div>

        <button
            type="button"
            class="hapusInventaris w-10 h-10 rounded-xl bg-red-50 hover:bg-red-100 text-red-600">

            <i class="fa fa-trash"></i>

        </button>

    </div>

    <div class="mt-4">

        <input
            type="number"
            min="1"
            value="1"
            name="inventaris[${index}][jumlah]"
            class="w-full h-12 rounded-xl border border-slate-300 px-4"
            required>

    </div>

</div>
`;

                $('#listInventaris').append(html);

                $('#inventaris_select')
                    .val('')
                    .trigger('change');

            });

        });

        // Hapus Inventaris
        $(document).on('click', '.hapusInventaris', function() {

            $(this)
                .closest('[id^="inventaris_"]')
                .remove();

            if ($('#listInventaris > [id^="inventaris_"]').length == 0) {

                $('#emptyInventaris').show();

            }

        });

        // Submit
        $('#formPeminjaman').submit(function(e) {

            e.preventDefault();

            if ($('#listInventaris > [id^="inventaris_"]').length == 0) {

                Swal.fire({
                    icon: 'warning',
                    text: 'Silakan pilih minimal satu inventaris.'
                });

                return;

            }

            if ($('#foto_pengambilan')[0].files.length == 0) {

                Swal.fire({
                    icon: 'warning',
                    text: 'Foto pengambilan wajib diunggah.'
                });

                return;

            }

            let btn = $('#btnSubmit');

            btn
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin mr-2"></i>Mengirim...');

            let formData = new FormData(this);

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
                        text: 'Pengajuan peminjaman berhasil.'
                    });

                    // Reset Form
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

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message ??
                            'Terjadi kesalahan.'
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
