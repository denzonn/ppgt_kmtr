@extends('layouts.app')

@section('title', 'Program Kerja')
@section('page', 'program_kerja')
@section('page_title', 'Program Kerja')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 md:text-2xl">Program Kerja</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola program kerja dan kegiatan periode {{ $periode->nama }}.</p>
            </div>

            <button type="button" onclick="tambahProgram()"
                class="inline-flex items-center justify-center gap-2 rounded-xl
            bg-primary px-5 py-3 text-sm font-semibold text-white
            transition hover:bg-primary_hover active:scale-[0.98]">
                <i class="fa-solid fa-plus"></i>
                Tambah Program
            </button>
        </div>

        <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2">
            <select id="filterTahun" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                <option value="">Semua tahun</option>
                @foreach ($tahuns as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
            <select id="filterBidang" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                <option value="">Semua bidang</option>
                @foreach ($bidangs as $b)
                    <option value="{{ $b->id }}">{{ $b->nama }}</option>
                @endforeach
            </select>
        </div>

        <div id="daftarProgram" class="space-y-4"></div>

        <div id="paginationProgram" class="flex flex-wrap justify-end gap-2"></div>
    </div>

    {{-- Modal Program --}}
    <div id="modalProgram" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalProgramTitle" class="text-lg font-bold text-slate-800">Tambah Program</h3>
                <button type="button" onclick="tutupModal('modalProgram')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formProgram" class="space-y-5 p-6">
                <input type="hidden" id="program_id">

                <div>
                    <label for="program_tahun" class="mb-2 block text-sm font-semibold text-slate-700">Tahun</label>
                    <select id="program_tahun" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        @foreach ($tahuns as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_bidang" class="mb-2 block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="program_bidang" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih bidang</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Program</label>
                    <input type="text" id="program_nama" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModal('modalProgram')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" id="btnSimpanProgram"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary_hover disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Kegiatan --}}
    <div id="modalKegiatan" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 id="modalKegiatanTitle" class="text-lg font-bold text-slate-800">Tambah Kegiatan</h3>
                <button type="button" onclick="tutupModal('modalKegiatan')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formKegiatan" class="space-y-5 p-6">
                <input type="hidden" id="kegiatan_id">
                <input type="hidden" id="kegiatan_program_id">

                <div>
                    <label for="kegiatan_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Kegiatan</label>
                    <input type="text" id="kegiatan_nama" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="kegiatan_deskripsi" class="mb-2 block text-sm font-semibold text-slate-700">Deskripsi</label>
                    <textarea id="kegiatan_deskripsi" rows="3" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary"></textarea>
                </div>

                <div>
                    <label for="kegiatan_anggaran" class="mb-2 block text-sm font-semibold text-slate-700">Anggaran (Rp)</label>
                    <input type="number" id="kegiatan_anggaran" min="0" step="1" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Bulan Pelaksanaan</span>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="kegiatan_bulan" value="{{ $i + 1 }}"
                                    class="rounded border-slate-300 text-primary focus:ring-primary">
                                {{ $bulan }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModal('modalKegiatan')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" id="btnSimpanKegiatan"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary_hover disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Dokumentasi --}}
    <div id="modalDokumentasi" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="my-auto w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-800">Dokumentasi Kegiatan</h3>
                <button type="button" onclick="tutupModal('modalDokumentasi')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formDokumentasi" class="space-y-5 p-6">
                <input type="hidden" id="dok_id">

                <div>
                    <label for="dok_foto" class="mb-2 block text-sm font-semibold text-slate-700">Foto (JPG/PNG, maks. 2 MB)</label>
                    <img id="dok_preview" alt="Foto kegiatan" class="mb-3 hidden max-h-48 rounded-xl border border-slate-200">
                    <input type="file" id="dok_foto" accept="image/jpeg,image/png"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <label id="dok_hapus_wrap" class="mt-2 hidden items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="dok_hapus" class="rounded border-slate-300 text-primary focus:ring-primary">
                        Hapus foto saat ini
                    </label>
                </div>

                <div>
                    <label for="dok_link" class="mb-2 block text-sm font-semibold text-slate-700">Link Dokumentasi</label>
                    <input type="url" id="dok_link" maxlength="2048" placeholder="https://..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModal('modalDokumentasi')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" id="btnSimpanDok"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary_hover disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const pkBase = @json(route('kepengurusan.program_kerja.index', $periode)).replace(/\/$/, '');
        const kegiatanBase = pkBase.replace(/\/program-kerja$/, '/kegiatan');
        const csrf = @json(csrf_token());
        const namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        let pkPage = 1;
        let pkRows = {};

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            } [c]));
        }

        function rupiah(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        }

        function bukaModal(id) {
            $('#' + id).removeClass('hidden').addClass('flex');
            document.body.classList.add('overflow-hidden');
        }

        function tutupModal(id) {
            $('#' + id).addClass('hidden').removeClass('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function tampilError(xhr, fallback) {
            let message = esc(xhr.responseJSON?.message || fallback);
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                message = Object.values(xhr.responseJSON.errors).flat().map(esc).join('<br>');
            }
            Swal.fire({ icon: 'error', title: 'Gagal', html: message });
        }

        // Kirim POST (dengan _method spoofing); multipart tidak dipakai lewat PUT.
        function kirim(url, method, data, btn, onSuccess) {
            const isForm = data instanceof FormData;
            if (isForm) {
                data.append('_token', csrf);
                if (method !== 'POST') data.append('_method', method);
            } else {
                data._token = csrf;
                if (method !== 'POST') data._method = method;
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                processData: !isForm,
                contentType: isForm ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
                beforeSend: () => btn && btn.prop('disabled', true),
                success: onSuccess,
                error: xhr => tampilError(xhr, 'Data gagal disimpan.'),
                complete: () => btn && btn.prop('disabled', false)
            });
        }

        function konfirmasiHapus(judul, url, pesanGagal) {
            Swal.fire({
                title: judul,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;
                kirim(url, 'DELETE', {}, null, function(res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    loadProgram(pkPage);
                });
            });
        }

        // ---------- Program ----------
        function tambahProgram() {
            $('#formProgram')[0].reset();
            $('#program_id').val('');
            $('#modalProgramTitle').text('Tambah Program');
            bukaModal('modalProgram');
        }

        function editProgram(id) {
            const p = pkRows[id];
            $('#program_id').val(p.id);
            $('#program_tahun').val(p.tahun);
            $('#program_bidang').val(p.bidang_id);
            $('#program_nama').val(p.nama);
            $('#modalProgramTitle').text('Edit Program');
            bukaModal('modalProgram');
        }

        $('#formProgram').on('submit', function(e) {
            e.preventDefault();
            const id = $('#program_id').val();
            kirim(id ? `${pkBase}/${id}` : pkBase, id ? 'PUT' : 'POST', {
                tahun: $('#program_tahun').val(),
                bidang_id: $('#program_bidang').val(),
                nama: $('#program_nama').val()
            }, $('#btnSimpanProgram'), function(res) {
                tutupModal('modalProgram');
                loadProgram(pkPage);
                Swal.fire('Berhasil', res.message, 'success');
            });
        });

        function hapusProgram(id) {
            konfirmasiHapus('Hapus Program?', `${pkBase}/${id}`);
        }

        // ---------- Kegiatan ----------
        function cariKegiatan(id) {
            for (const p of Object.values(pkRows)) {
                const k = p.kegiatans.find(x => x.id === id);
                if (k) return k;
            }
        }

        function tambahKegiatan(programId) {
            $('#formKegiatan')[0].reset();
            $('#kegiatan_id').val('');
            $('#kegiatan_program_id').val(programId);
            $('#modalKegiatanTitle').text('Tambah Kegiatan');
            bukaModal('modalKegiatan');
        }

        function editKegiatan(id) {
            const k = cariKegiatan(id);
            $('#formKegiatan')[0].reset();
            $('#kegiatan_id').val(k.id);
            $('#kegiatan_nama').val(k.nama);
            $('#kegiatan_deskripsi').val(k.deskripsi);
            $('#kegiatan_anggaran').val(k.anggaran);
            $('input[name=kegiatan_bulan]').each(function() {
                this.checked = k.bulan.includes(Number(this.value));
            });
            $('#modalKegiatanTitle').text('Edit Kegiatan');
            bukaModal('modalKegiatan');
        }

        $('#formKegiatan').on('submit', function(e) {
            e.preventDefault();
            const id = $('#kegiatan_id').val();
            const bulan = $('input[name=kegiatan_bulan]:checked').map((_, el) => el.value).get();
            kirim(id ? `${kegiatanBase}/${id}` : `${pkBase}/${$('#kegiatan_program_id').val()}/kegiatan`, id ? 'PUT' : 'POST', {
                nama: $('#kegiatan_nama').val(),
                deskripsi: $('#kegiatan_deskripsi').val(),
                anggaran: $('#kegiatan_anggaran').val(),
                bulan: bulan
            }, $('#btnSimpanKegiatan'), function(res) {
                tutupModal('modalKegiatan');
                loadProgram(pkPage);
                Swal.fire('Berhasil', res.message, 'success');
            });
        });

        function hapusKegiatan(id) {
            konfirmasiHapus('Hapus Kegiatan?', `${kegiatanBase}/${id}`);
        }

        // ---------- Dokumentasi ----------
        function dokumentasi(id) {
            const k = cariKegiatan(id);
            $('#formDokumentasi')[0].reset();
            $('#dok_id').val(k.id);
            $('#dok_link').val(k.link ?? '');
            $('#dok_preview').toggleClass('hidden', !k.foto).attr('src', k.foto ? '/storage/' + k.foto : '');
            $('#dok_hapus_wrap').toggleClass('hidden', !k.foto).toggleClass('flex', !!k.foto);
            bukaModal('modalDokumentasi');
        }

        $('#formDokumentasi').on('submit', function(e) {
            e.preventDefault();
            const fd = new FormData();
            const file = $('#dok_foto')[0].files[0];
            if (file) fd.append('foto', file);
            if ($('#dok_hapus').is(':checked')) fd.append('hapus_foto', 1);
            fd.append('link', $('#dok_link').val());

            kirim(`${kegiatanBase}/${$('#dok_id').val()}/dokumentasi`, 'POST', fd, $('#btnSimpanDok'), function(res) {
                tutupModal('modalDokumentasi');
                loadProgram(pkPage);
                Swal.fire('Berhasil', res.message, 'success');
            });
        });

        // ---------- Daftar ----------
        function renderKegiatan(k) {
            const bulan = k.bulan.map(b => namaBulan[b - 1]).join(', ');
            const link = k.link ?
                `<a href="${esc(k.link)}" target="_blank" rel="noopener" class="text-primary underline">Link</a>` : '';
            const foto = k.foto ?
                `<a href="/storage/${esc(k.foto)}" target="_blank" rel="noopener" class="text-primary underline">Foto</a>` : '';
            const dok = [foto, link].filter(Boolean).join(' | ') || '-';

            return `
            <tr>
                <td class="px-4 py-3 font-semibold text-slate-800">${esc(k.nama)}<div class="font-normal text-slate-500">${esc(k.deskripsi)}</div></td>
                <td class="px-4 py-3 whitespace-nowrap">${esc(bulan)}</td>
                <td class="px-4 py-3 whitespace-nowrap">${esc(rupiah(k.anggaran))}</td>
                <td class="px-4 py-3">${dok}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-center gap-2">
                        <button onclick="dokumentasi(${k.id})" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200">Dokumentasi</button>
                        <button onclick="editKegiatan(${k.id})" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100"><i class="fa-solid fa-pen"></i></button>
                        <button onclick="hapusKegiatan(${k.id})" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }

        function renderProgram(p) {
            const rows = p.kegiatans.length ?
                p.kegiatans.map(renderKegiatan).join('') :
                '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada kegiatan.</td></tr>';

            return `
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="font-bold text-slate-800">${esc(p.nama)}</div>
                        <div class="text-sm text-slate-500">${esc(p.bidang?.nama ?? '-')} - ${esc(p.tahun)}</div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="tambahKegiatan(${p.id})" class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-primary_hover">+ Kegiatan</button>
                        <button onclick="editProgram(${p.id})" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100"><i class="fa-solid fa-pen"></i></button>
                        <button onclick="hapusProgram(${p.id})" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead><tr class="text-left text-slate-600">
                            <th class="px-4 py-3">Kegiatan</th><th class="px-4 py-3">Bulan</th><th class="px-4 py-3">Anggaran</th><th class="px-4 py-3">Dokumentasi</th><th class="px-4 py-3 text-center">Aksi</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">${rows}</tbody>
                    </table>
                </div>
            </div>`;
        }

        function loadProgram(page = 1) {
            pkPage = page;
            $.get(`${pkBase}/data`, {
                page: page,
                tahun: $('#filterTahun').val(),
                bidang_id: $('#filterBidang').val()
            }).done(function(res) {
                const data = res.data || [];
                pkRows = {};
                data.forEach(p => pkRows[p.id] = p);

                $('#daftarProgram').html(data.length ? data.map(renderProgram).join('') :
                    '<div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center text-slate-500">Program kerja tidak ditemukan.</div>');

                let html = '';
                if (res.last_page > 1) {
                    for (let i = 1; i <= res.last_page; i++) {
                        html += `<button onclick="loadProgram(${i})"
                            class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
                    }
                }
                $('#paginationProgram').html(html);
            }).fail(function() {
                $('#daftarProgram').html('<div class="px-6 py-10 text-center text-red-600">Gagal memuat program kerja.</div>');
            });
        }

        ['modalProgram', 'modalKegiatan', 'modalDokumentasi'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) tutupModal(id);
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') ['modalProgram', 'modalKegiatan', 'modalDokumentasi'].forEach(tutupModal);
        });

        $('#filterTahun, #filterBidang').on('change', () => loadProgram(1));

        $(document).ready(() => loadProgram());
    </script>
@endpush
