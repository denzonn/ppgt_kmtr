@extends('layouts.app')

@section('title', 'Generator QR Code')
@section('page', 'generator_qr')
@section('page_title', 'Generator QR Code')

@section('content')

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">
                    Generator QR Code
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Buat QR Code dari link dan unduh dalam format PNG.
                </p>
            </div>

            <button type="button" onclick="tambahQR()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Buat QR Code
            </button>
        </div>

        {{-- Search --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="relative">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-4 top-1/2
                -translate-y-1/2 text-slate-400"></i>

                <input type="text" id="searchQR" placeholder="Cari nama QR Code atau link..."
                    class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4
                text-sm focus:border-primary focus:ring-primary">
            </div>
        </div>

        {{-- Tabel Desktop --}}
        <div id="qrTableContainer"
            class="hidden overflow-hidden rounded-2xl border border-slate-200
        bg-white shadow-sm md:block">

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-sm text-slate-600">
                            <th class="px-6 py-4">QR Code</th>
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">Link Tujuan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="tableQR" class="divide-y divide-slate-100 text-sm">
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div id="mobileQR" class="space-y-4 md:hidden"></div>

        {{-- Pagination --}}
        <div id="paginationQR" class="flex flex-wrap justify-end gap-2"></div>

        {{-- Empty State --}}
        <div id="emptyQR"
            class="hidden rounded-2xl border border-slate-200 bg-white px-5 py-14
        text-center shadow-sm">

            <div class="mx-auto flex h-20 w-20 items-center justify-center
            rounded-2xl bg-blue-50 text-primary">
                <i class="fa-solid fa-qrcode text-4xl"></i>
            </div>

            <h3 class="mt-5 text-lg font-bold text-slate-800">
                Belum Ada QR Code
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Masukkan nama dan link tujuan untuk membuat QR Code pertamamu.
            </p>

            <button type="button" onclick="tambahQR()"
                class="mt-6 rounded-xl bg-primary px-5 py-3
            text-sm font-semibold text-white transition hover:bg-primary_hover">
                <i class="fa-solid fa-plus mr-2"></i>
                Buat QR Code
            </button>
        </div>

    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalQR"
        class="fixed inset-0 z-[100] hidden items-center justify-center
    overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">

        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl
        bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h3 id="modalQRTitle" class="text-lg font-bold text-slate-800">
                        Buat QR Code
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Masukkan nama dan link tujuan.
                    </p>
                </div>

                <button type="button" onclick="tutupModalQR()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formQR" class="space-y-5 p-6">
                @csrf

                <input type="hidden" id="qr_id">
                <input type="hidden" id="qr_method" value="POST">

                <div>
                    <label for="qr_nama" class="mb-2 block text-sm font-semibold text-slate-700">
                        Nama QR Code
                    </label>

                    <input type="text" id="qr_nama" name="nama" placeholder="Contoh: Website PPGT KMTr" required
                        maxlength="150"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">

                    <p class="mt-1 text-xs text-slate-500">
                        Gunakan nama yang mudah dikenali.
                    </p>
                </div>

                <div>
                    <label for="qr_link" class="mb-2 block text-sm font-semibold text-slate-700">
                        Link Tujuan
                    </label>

                    <div class="relative">
                        <i
                            class="fa-solid fa-link absolute left-4 top-1/2
                        -translate-y-1/2 text-slate-400"></i>

                        <input type="url" id="qr_link" name="link" placeholder="https://contoh.com" required
                            maxlength="2048"
                            class="w-full rounded-xl border border-slate-300
                        py-3 pl-11 pr-4 text-sm
                        focus:border-primary focus:ring-primary">
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        Pastikan link dapat dibuka dan diawali https:// atau http://.
                    </p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalQR()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanQR"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold
                    text-white transition hover:bg-primary_hover disabled:opacity-60">
                        <i class="fa-solid fa-qrcode mr-2"></i>
                        Buat QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const qrBaseUrl = @json(route('generate_qr.index'));
        const qrDataUrl = @json(route('generate_qr.getData'));
        const qrStoreUrl = @json(route('generate_qr.store'));
        const qrDownloadUrl = @json(url('/'));

        let qrPage = 1;
        let qrSearchTimer = null;
        let qrRequest = null;

        function escapeQR(value) {
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

        function urlQR(id) {
            return `${qrBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function gambarQR(item, ukuran = 'h-24 w-24') {
            if (!item.qr_code) {
                return `
            <div class="${ukuran} flex items-center justify-center
                rounded-xl bg-slate-100 text-slate-400">
                <i class="fa-solid fa-qrcode text-3xl"></i>
            </div>
        `;
            }

            // qr_code sudah berisi URL lengkap dari database.
            const src = escapeQR(item.qr_code);

            return `
        <img src="${src}" alt="QR Code ${escapeQR(item.nama)}"
            class="${ukuran} rounded-xl border border-slate-200 bg-white
            object-contain p-1" loading="lazy">
    `;
        }

        function tambahQR() {
            document.getElementById('formQR').reset();
            document.getElementById('qr_id').value = '';
            document.getElementById('qr_method').value = 'POST';
            document.getElementById('modalQRTitle').textContent = 'Buat QR Code';

            document.getElementById('btnSimpanQR').innerHTML =
                '<i class="fa-solid fa-qrcode mr-2"></i>Buat QR Code';

            bukaModalQR();
        }

        function bukaModalQR() {
            const modal = document.getElementById('modalQR');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            setTimeout(() => document.getElementById('qr_nama').focus(), 100);
        }

        function tutupModalQR() {
            const modal = document.getElementById('modalQR');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        document.getElementById('modalQR').addEventListener('click', function(e) {
            if (e.target === this) tutupModalQR();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModalQR();
        });

        // Simpan dan edit
        $('#formQR').on('submit', function(e) {
            e.preventDefault();

            const id = $('#qr_id').val();
            const method = $('#qr_method').val();
            const url = method === 'POST' ? qrStoreUrl : urlQR(id);

            const btn = $('#btnSimpanQR');
            const originalText = method === 'POST' ? 'Buat QR Code' : 'Simpan Perubahan';

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: method === 'PUT' ? 'PUT' : 'POST',
                    nama: $('#qr_nama').val(),
                    link: $('#qr_link').val()
                },
                beforeSend: function() {
                    btn.prop('disabled', true).html(
                        '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...'
                    );
                },
                success: function(res) {
                    if (res.status) {
                        tutupModalQR();
                        loadQR(qrPage);

                        if (typeof swalSuccess === 'function') {
                            swalSuccess(res.message);
                        } else {
                            Swal.fire('Berhasil', res.message, 'success');
                        }
                    }
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'QR Code gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors)
                            .flat()
                            .join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: message
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(
                        `<i class="fa-solid fa-floppy-disk mr-2"></i>${originalText}`
                    );
                }
            });
        });

        // Ambil data dan render tabel
        function loadQR(page = 1) {
            qrPage = page;

            if (qrRequest) qrRequest.abort();

            $('#tableQR').html(`
            <tr>
                <td colspan="4" class="py-12 text-center text-slate-500">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Memuat data...
                </td>
            </tr>
        `);

            qrRequest = $.get(qrDataUrl, {
                page: page,
                search: $('#searchQR').val()
            }).done(function(res) {
                const data = res.data || [];
                const searching = $('#searchQR').val().trim() !== '';

                if (res.total === 0 && !searching) {
                    $('#qrTableContainer, #mobileQR, #paginationQR').addClass('hidden');
                    $('#emptyQR').removeClass('hidden');
                    return;
                }

                $('#emptyQR').addClass('hidden');
                $('#qrTableContainer, #mobileQR, #paginationQR').removeClass('hidden');

                if (data.length === 0) {
                    $('#tableQR').html(`
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-magnifying-glass mb-3 text-2xl"></i>
                            <p>QR Code tidak ditemukan.</p>
                        </td>
                    </tr>
                `);

                    $('#mobileQR').html(`
                    <div class="rounded-2xl border border-slate-200 bg-white
                        p-8 text-center text-sm text-slate-500">
                        QR Code tidak ditemukan.
                    </div>
                `);

                    renderPaginationQR(res);
                    return;
                }

                let desktop = '';
                let mobile = '';

                data.forEach(function(item) {
                    const nama = escapeQR(item.nama);
                    const link = escapeQR(item.link);

                    desktop += `
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4">
                            ${gambarQR(item, 'h-24 w-24')}
                        </td>

                        <td class="px-6 py-4 font-semibold text-slate-800">
                            ${nama}
                        </td>

                        <td class="max-w-xs px-6 py-4">
                            <a href="${link}" target="_blank" rel="noopener noreferrer"
                                class="block truncate text-primary hover:underline"
                                title="${link}">
                                ${link}
                            </a>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <button onclick="downloadQR(${item.id})"
                                    title="Download PNG"
                                    class="flex h-9 w-9 items-center justify-center
                                    rounded-lg bg-emerald-50 text-emerald-700
                                    transition hover:bg-emerald-100">
                                    <i class="fa-solid fa-download"></i>
                                </button>

                                <button onclick="editQR(${item.id})"
                                    title="Edit"
                                    class="flex h-9 w-9 items-center justify-center
                                    rounded-lg bg-blue-50 text-blue-600
                                    transition hover:bg-blue-100">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button onclick="hapusQR(${item.id})"
                                    title="Hapus"
                                    class="flex h-9 w-9 items-center justify-center
                                    rounded-lg bg-red-50 text-red-600
                                    transition hover:bg-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;

                    mobile += `
                    <div class="rounded-2xl border border-slate-200 bg-white
                        p-4 shadow-sm">
                        <div class="flex items-start gap-4">
                            ${gambarQR(item, 'h-24 w-24 shrink-0')}

                            <div class="min-w-0 flex-1">
                                <h3 class="break-words font-bold text-slate-800">
                                    ${nama}
                                </h3>

                                <a href="${link}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 block break-all text-xs text-primary">
                                    ${link}
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2 border-t border-slate-100 pt-3">
                            <button onclick="downloadQR(${item.id})"
                                class="flex flex-1 items-center justify-center gap-2
                                rounded-lg bg-emerald-50 px-3 py-2.5
                                text-sm font-semibold text-emerald-700
                                transition hover:bg-emerald-100">
                                <i class="fa-solid fa-download"></i>
                                Download PNG
                            </button>

                            <button onclick="editQR(${item.id})"
                                class="flex h-10 w-10 items-center justify-center
                                rounded-lg bg-blue-50 text-blue-600">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button onclick="hapusQR(${item.id})"
                                class="flex h-10 w-10 items-center justify-center
                                rounded-lg bg-red-50 text-red-600">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                });

                $('#tableQR').html(desktop);
                $('#mobileQR').html(mobile);
                renderPaginationQR(res);
            }).fail(function(xhr, status) {
                if (status === 'abort') return;

                $('#tableQR').html(`
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-red-600">
                        Gagal memuat data QR Code.
                        <button onclick="loadQR(qrPage)" class="ml-1 underline">
                            Coba lagi
                        </button>
                    </td>
                </tr>
            `);
            });
        }

        // Pagination
        function renderPaginationQR(res) {
            if (res.last_page <= 1) {
                $('#paginationQR').html('');
                return;
            }

            let html = `
            <button ${res.current_page <= 1 ? 'disabled' : ''}
                onclick="loadQR(${res.current_page - 1})"
                class="rounded-lg border px-3 py-2 text-sm
                disabled:cursor-not-allowed disabled:opacity-40 hover:bg-slate-50">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        `;

            for (let i = 1; i <= res.last_page; i++) {
                if (
                    res.last_page > 7 &&
                    i !== 1 &&
                    i !== res.last_page &&
                    Math.abs(i - res.current_page) > 1
                ) {
                    if (i === 2 || i === res.last_page - 1) {
                        html += `<span class="px-1 py-2 text-slate-400">...</span>`;
                    }
                    continue;
                }

                html += `
                <button onclick="loadQR(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition
                    ${i === res.current_page
                        ? 'border-primary bg-primary text-white'
                        : 'hover:bg-slate-50'}">
                    ${i}
                </button>
            `;
            }

            html += `
            <button ${res.current_page >= res.last_page ? 'disabled' : ''}
                onclick="loadQR(${res.current_page + 1})"
                class="rounded-lg border px-3 py-2 text-sm
                disabled:cursor-not-allowed disabled:opacity-40 hover:bg-slate-50">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        `;

            $('#paginationQR').html(html);
        }

        // Edit
        function editQR(id) {
            $.get(urlQR(id))
                .done(function(item) {
                    $('#qr_id').val(item.id);
                    $('#qr_method').val('PUT');
                    $('#qr_nama').val(item.nama);
                    $('#qr_link').val(item.link);
                    $('#modalQRTitle').text('Edit QR Code');

                    $('#btnSimpanQR').html(
                        '<i class="fa-solid fa-floppy-disk mr-2"></i>Simpan Perubahan'
                    );

                    bukaModalQR();
                })
                .fail(function() {
                    Swal.fire('Gagal', 'Data QR Code tidak ditemukan.', 'error');
                });
        }


        async function downloadQR(id) {
            try {
                const res = await $.get(`${urlQR(id)}/download`);

                const img = new Image();
                img.crossOrigin = 'anonymous';

                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                    img.src = res.url;
                });

                // Canvas langsung sebesar card, tanpa background luar.
                const canvas = document.createElement('canvas');
                canvas.width = 1000;
                canvas.height = 1080;

                const ctx = canvas.getContext('2d');

                // Warna utama.
                const navy = '#1E3A8A';
                const dark = '#0F172A';
                const muted = '#64748B';
                const border = '#DCE5F0';

                // Background putih langsung memenuhi gambar.
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Border card langsung di tepi gambar.
                ctx.strokeStyle = border;
                ctx.lineWidth = 3;
                ctx.strokeRect(2, 2, 996, 1076);

                // Aksen biru bagian atas.
                ctx.fillStyle = navy;
                ctx.fillRect(0, 0, 1000, 14);

                // Nama QR Code.
                let nama = String(res.nama || 'QR Code');
                const maxNamaWidth = 830;

                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = 'bold 38px Arial, sans-serif';

                while (
                    ctx.measureText(nama).width > maxNamaWidth &&
                    nama.length > 5
                ) {
                    nama = nama.slice(0, -2) + '…';
                }

                ctx.fillStyle = dark;
                ctx.fillText(nama, 500, 65);

                // Keterangan singkat saja.
                ctx.fillStyle = muted;
                ctx.font = '20px Arial, sans-serif';
                ctx.fillText('SCAN UNTUK MEMBUKA LINK', 500, 105);

                // Garis tipis di bawah header.
                ctx.beginPath();
                ctx.moveTo(55, 140);
                ctx.lineTo(945, 140);
                ctx.strokeStyle = border;
                ctx.lineWidth = 2;
                ctx.stroke();

                // QR Code dibuat dominan.
                const qrSize = 860;
                const qrX = (canvas.width - qrSize) / 2;
                const qrY = 155;

                // Bingkai tipis yang rapat dengan QR.
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);

                ctx.strokeStyle = border;
                ctx.lineWidth = 2;
                ctx.strokeRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);

                // Sisakan QR dalam rasio aslinya.
                const ratio = Math.min(
                    qrSize / img.naturalWidth,
                    qrSize / img.naturalHeight
                );

                const width = img.naturalWidth * ratio;
                const height = img.naturalHeight * ratio;

                ctx.drawImage(
                    img,
                    qrX + (qrSize - width) / 2,
                    qrY + (qrSize - height) / 2,
                    width,
                    height
                );

                // Footer ringkas.
                ctx.beginPath();
                ctx.moveTo(55, 1030);
                ctx.lineTo(945, 1030);
                ctx.strokeStyle = border;
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.fillStyle = navy;
                ctx.font = 'bold 25px Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('PPGT Klasis Makassar Timur', 500, 1055);

                // Ekspor PNG.
                const blob = await new Promise(resolve => {
                    canvas.toBlob(resolve, 'image/png');
                });

                if (!blob) {
                    throw new Error('Konversi PNG gagal.');
                }

                const blobUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');

                a.href = blobUrl;
                a.download = `${String(res.nama || 'qr-code')
            .replace(/[\\/:*?"<>|]/g, '-')
            .trim() || 'qr-code'}.png`;

                document.body.appendChild(a);
                a.click();
                a.remove();

                setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);

            } catch (error) {
                console.error('Download QR Code:', error);

                Swal.fire(
                    'Gagal',
                    'QR Code gagal diunduh. Silakan coba kembali.',
                    'error'
                );
            }
        }

        // Hapus
        function hapusQR(id) {
            Swal.fire({
                title: 'Hapus QR Code?',
                text: 'Data dan file QR Code ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlQR(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');

                        if (qrPage > 1) {
                            loadQR(qrPage);
                        } else {
                            loadQR(1);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'QR Code gagal dihapus.', 'error');
                    }
                });
            });
        }

        // Pencarian dengan debounce
        $('#searchQR').on('input', function() {
            clearTimeout(qrSearchTimer);

            qrSearchTimer = setTimeout(function() {
                loadQR(1);
            }, 400);
        });

        $(document).ready(function() {
            loadQR();
        });
    </script>
@endpush
