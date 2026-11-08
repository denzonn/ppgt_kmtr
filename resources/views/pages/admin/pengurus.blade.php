@extends('layouts.app')

@section('title', 'Pengurus')
@section('page', 'pengurus')
@section('page_title', 'Pengurus')

@section('content')

    <div class="space-y-5">

        @php
            $totalTerisi = $bidangs->sum('pengurus_count');
            $totalKuota = $bidangs->sum('jumlah_anggota');
        @endphp

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Pengurus</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Periode {{ $periode->nama }}, {{ $totalTerisi }}{{ $totalKuota ? ' dari ' . $totalKuota : '' }} orang sudah terisi.
                </p>
            </div>
            <button type="button" onclick="tambahPengurus()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i> Tambah Pengurus
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="searchPengurus" placeholder="Cari nama anggota..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                </div>
                <select id="filterBidangPengurus" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 sm:w-80">
                    <option value="">Semua Bidang</option>
                    @foreach ($bidangs as $b)
                        <option value="{{ $b->id }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="daftarPengurus" class="divide-y divide-slate-100">
                <div class="py-12 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data pengurus...</div>
            </div>
        </div>

        <div id="paginationPengurus" class="flex flex-wrap justify-end gap-2 empty:hidden"></div>
    </div>

{{-- Modal Tambah/Edit --}}
    <div id="modalPengurus"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-user-tie text-lg"></i>
                    </div>
                    <h3 id="modalPengurusTitle" class="text-lg font-bold text-slate-800">Tambah Pengurus</h3>
                </div>

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
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                    <p id="pengurus_terpilih" class="mt-2 text-sm font-semibold text-primary"></p>
                    <ul id="pengurus_hasil" class="mt-2 hidden max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 text-sm"></ul>
                </div>

                <div>
                    <label for="pengurus_jabatan" class="mb-2 block text-sm font-semibold text-slate-700">Jabatan</label>
                    <select id="pengurus_jabatan" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3
                    text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih jabatan</option>
                        @foreach ($bidangs as $b)
                            @if ($b->jabatans->isNotEmpty())
                                <optgroup data-bidang="{{ $b->id }}" label="{{ $b->nama }} ({{ $b->pengurus_count }}{{ $b->jumlah_anggota ? ' dari ' . $b->jumlah_anggota : '' }} orang)">
                                    @foreach ($b->jabatans as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p id="pengurus_jabatan_info" class="mt-2 text-xs text-slate-500"></p>
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
                            <button type="button" id="btnHapusFotoPengurus" class="mt-1 hidden text-xs font-semibold text-red-600">Hapus foto</button>
                        </div>
                    </div>
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
        const bidangInfo = @json($bidangInfo);
        
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
            showModal('modalPengurus');
        }

        function tutupModalPengurus() {
            closeModal('modalPengurus');
        }

        function aturJabatanPengurus(bidangId = '') {
            const grup = $('#pengurus_jabatan optgroup');

            grup.each(function() {
                const cocok = !bidangId || String($(this).data('bidang')) === String(bidangId);
                $(this).prop('disabled', !cocok).prop('hidden', !cocok);
            });

            const pilihan = $('#pengurus_jabatan optgroup:not([disabled]) option');

            if (bidangId && pilihan.length === 1) {
                $('#pengurus_jabatan').val(pilihan.val());
            }

            $('#pengurus_jabatan_info').text(pilihan.length ?
                (bidangId ? 'Menampilkan jabatan di bidang ini.' : 'Jabatan dikelompokkan per bidang.') :
                'Belum ada jabatan di sini. Tambahkan dulu di menu Jabatan.');
        }

        let hapusFotoPengurus = false;

        function tampilkanFotoPengurus(url) {
            $('#pengurus_preview').attr('src', url || '').toggleClass('hidden', !url);
            $('#pengurus_preview_kosong').toggleClass('hidden', Boolean(url)).toggleClass('flex', !url);
            $('#btnHapusFotoPengurus').toggleClass('hidden', !url);
        }

        $('#pengurus_foto').on('change', function() {
            const file = this.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                $(this).val('');
                Swal.fire('Gagal', 'Foto harus berformat JPG, PNG, atau GIF dan maksimal 2 MB.', 'error');
                return;
            }

            hapusFotoPengurus = false;
            tampilkanFotoPengurus(URL.createObjectURL(file));
        });

        $('#btnHapusFotoPengurus').on('click', function() {
            $('#pengurus_foto').val('');
            hapusFotoPengurus = true;
            tampilkanFotoPengurus(null);
        });

        function tambahPengurus(bidangId = '') {
            $('#formPengurus')[0].reset();
            $('#pengurus_id').val('');
            hapusFotoPengurus = false;
            tampilkanFotoPengurus(null);
            aturJabatanPengurus(bidangId);
            pilihAnggotaPengurus('', '');
            $('#modalPengurusTitle').text('Tambah Pengurus');
            bukaModalPengurus();
        }

        function editPengurus(id) {
            $.get(urlPengurus(id)).done(function(item) {
                $('#formPengurus')[0].reset();
                $('#pengurus_id').val(item.id);
                hapusFotoPengurus = false;
                tampilkanFotoPengurus(item.foto);
                aturJabatanPengurus();
                pilihAnggotaPengurus(item.data_anggota_id, `${item.nama} (${item.no_anggota ?? '-'})`);
                $('#pengurus_jabatan').val(item.jabatan_id);
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
            const isian = new FormData();
            const foto = $('#pengurus_foto')[0].files[0];

            isian.append('_token', '{{ csrf_token() }}');
            isian.append('_method', id ? 'PUT' : 'POST');
            isian.append('data_anggota_id', $('#pengurus_anggota_id').val());
            isian.append('jabatan_id', $('#pengurus_jabatan').val());

            if (foto) isian.append('foto', foto);
            if (hapusFotoPengurus) isian.append('hapus_foto', '1');

            $.ajax({
                url: id ? urlPengurus(id) : pengurusBaseUrl,
                type: 'POST',
                data: isian,
                processData: false,
                contentType: false,
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    // Muat ulang supaya hitungan orang per bidang di daftar jabatan ikut berubah.
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
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
                search: $('#searchPengurus').val(),
                bidang_id: $('#filterBidangPengurus').val()
            }).done(function(res) {
                const data = res.data || [];

                const mencari = $('#searchPengurus').val().trim() !== '';
                const filter = $('#filterBidangPengurus').val();

                const grup = {};
                data.forEach(function(item) {
                    (grup[item.bidang_id] = grup[item.bidang_id] || []).push(item);
                });

                // Bidang yang belum punya pengurus tetap tampil, supaya mudah diisi.
                const semuaTampil = !mencari && res.last_page === 1;
                const bagian = bidangInfo.filter(b => filter ? String(b.id) === filter : (grup[b.id] || semuaTampil));

                if (bagian.length === 0) {
                    $('#daftarPengurus').html('<div class="py-12 text-center text-sm text-slate-500">Pengurus tidak ditemukan.</div>');
                    $('#paginationPengurus').html('');
                    return;
                }

                const kartu = function(item) {
                    const inisial = String(item.nama).trim().split(/\s+/).slice(0, 2).map(k => k[0]).join('').toUpperCase();

                    return `
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-primary/40 hover:shadow-sm">
                        ${item.foto ?
                            `<img src="${escapePengurus(item.foto)}" alt="Foto ${escapePengurus(item.nama)}" class="h-11 w-11 shrink-0 rounded-full object-cover" loading="lazy">` :
                            `<span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">${escapePengurus(inisial)}</span>`}
                        <div class="min-w-0 flex-1">
                            <div class="break-words text-sm font-semibold text-slate-800">${escapePengurus(item.nama)}</div>
                            <div class="text-xs text-primary">${escapePengurus(item.jabatan)}</div>
                            <div class="text-[11px] text-slate-400">${escapePengurus(item.no_anggota ?? 'Tanpa no. anggota')}</div>
                        </div>
                        <button onclick="editPengurus(${item.id})" title="Edit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-pen text-xs"></i></button>
                        <button onclick="hapusPengurus(${item.id})" title="Hapus" class="-ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </div>`;
                };

                $('#daftarPengurus').html(bagian.map(function(b) {
                    const anggota = grup[b.id] || [];
                    const terisi = Number(b.pengurus_count);
                    const kuota = b.jumlah_anggota === null ? null : Number(b.jumlah_anggota);
                    const persen = kuota ? Math.min(100, Math.round(terisi / kuota * 100)) : 0;
                    const sisa = kuota === null ? null : Math.max(0, kuota - terisi);

                    let ringkasan = `<span class="whitespace-nowrap text-xs text-slate-500">${terisi} orang</span>`;

                    if (sisa === 0) {
                        ringkasan = `<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary"><i class="fas fa-check text-[10px]"></i>Lengkap, ${terisi} orang</span>`;
                    } else if (kuota !== null) {
                        ringkasan = `
                        <div class="flex items-center gap-2.5">
                            <div class="hidden h-1.5 w-28 overflow-hidden rounded-full bg-slate-100 sm:block"><div class="h-full rounded-full bg-primary" style="width: ${persen}%"></div></div>
                            <span class="whitespace-nowrap text-xs font-medium text-slate-500">${terisi} dari ${kuota} orang</span>
                        </div>`;
                    }

                    const tambah = !mencari && sisa !== 0 ? `
                        <button onclick="tambahPengurus(${b.id})" title="Tambah pengurus di bidang ini"
                            class="flex min-h-[70px] items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 px-3 text-sm font-medium text-slate-500 transition hover:border-primary hover:bg-primary/5 hover:text-primary">
                            <i class="fas fa-plus text-xs"></i>
                            ${sisa === null ? 'Tambah Pengurus' : `Tambah, sisa ${sisa} orang`}
                        </button>` : '';

                    return `
                    <section class="p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="font-bold leading-snug text-slate-800">${escapePengurus(b.nama)}</h3>
                            ${ringkasan}
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">${anggota.map(kartu).join('')}${tambah}</div>
                    </section>`;
                }).join(''));

renderPaginationPengurus(res);
            }).fail(function() {
                $('#daftarPengurus').html('<div class="py-10 text-center text-sm text-red-600">Gagal memuat data pengurus.</div>');
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
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Pengurus gagal dihapus.', 'error');
                    }
                });
            });
        }

        $('#searchPengurus').on('input', function() {
            clearTimeout(pengurusTimer);
            pengurusTimer = setTimeout(() => loadPengurus(1), 400);
        });

        $('#filterBidangPengurus').on('change', () => loadPengurus(1));

        $(document).ready(() => loadPengurus());
    </script>
@endpush
