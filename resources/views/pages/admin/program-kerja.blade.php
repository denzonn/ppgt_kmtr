@extends('layouts.app')

@section('title', 'Program Kerja')
@section('page', 'program_kerja')
@section('page_title', 'Program Kerja')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Program Kerja</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola program dan kegiatan periode {{ $periode->nama }}.</p>
            </div>
            <button type="button" onclick="tambahProgram()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Program
            </button>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:flex-row xl:items-center xl:justify-between">
            <dl class="grid grid-cols-2 gap-y-3 sm:flex sm:divide-x sm:divide-slate-200">
                <div class="sm:pr-8">
                    <dt class="text-xs text-slate-500">Program</dt>
                    <dd id="ringkasanProgram" class="mt-0.5 text-lg font-bold text-slate-800">-</dd>
                </div>
                <div class="sm:px-8">
                    <dt class="text-xs text-slate-500">Kegiatan</dt>
                    <dd id="ringkasanKegiatan" class="mt-0.5 text-lg font-bold text-slate-800">-</dd>
                </div>
                <div class="col-span-2 border-t border-slate-100 pt-3 sm:border-t-0 sm:pl-8 sm:pt-0">
                    <dt class="text-xs text-slate-500">Total anggaran</dt>
                    <dd id="ringkasanAnggaran" class="mt-0.5 whitespace-nowrap text-lg font-bold text-primary">-</dd>
                </div>
            </dl>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                <select id="filterTahun" aria-label="Saring tahun" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 xl:w-40">
                    <option value="">Semua Tahun</option>
                    @foreach ($tahuns as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
                <select id="filterBidang" aria-label="Saring bidang" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 xl:w-64">
                    <option value="">Semua Bidang</option>
                    @foreach ($bidangs as $b)
                        <option value="{{ $b->id }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="daftarProgram" class="space-y-8"></div>

        <div id="paginationProgram" class="flex flex-wrap justify-end gap-2 empty:hidden"></div>
    </div>

    {{-- Modal Program --}}
    <div id="modalProgram"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-clipboard-list text-lg"></i>
                    </div>
                    <h3 id="modalProgramTitle" class="text-lg font-bold text-slate-800">Tambah Program</h3>
                </div>
                <button type="button" onclick="tutupModal('modalProgram')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formProgram" class="space-y-5 p-6">
                <input type="hidden" id="program_id">

                <div class="flex items-center justify-between rounded-xl bg-primary/5 px-4 py-3">
                    <span class="text-sm font-semibold text-slate-700"><i class="fas fa-calendar-days mr-2 text-primary"></i>Tahun Program</span>
                    <span id="program_tahun_teks" class="font-bold text-primary">{{ $tahunBerjalan }}</span>
                </div>

<div>
                    <label for="program_bidang" class="mb-2 block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="program_bidang" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih bidang</option>
                        @foreach ($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Program</label>
                    <input type="text" id="program_nama" placeholder="Contoh: Perlawatan" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
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
    <div id="modalKegiatan"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-calendar-check text-lg"></i>
                    </div>
                    <h3 id="modalKegiatanTitle" class="text-lg font-bold text-slate-800">Tambah Kegiatan</h3>
                </div>
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
                    <input type="text" id="kegiatan_nama" placeholder="Contoh: Perlawatan Jemaat Tahun 2026" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="kegiatan_deskripsi" class="mb-2 block text-sm font-semibold text-slate-700">Deskripsi</label>
                    <textarea id="kegiatan_deskripsi" placeholder="Contoh: Melakukan perkunjungan ke jemaat-jemaat" rows="3" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary"></textarea>
                </div>

                <div>
                    <label for="kegiatan_anggaran" class="mb-2 block text-sm font-semibold text-slate-700">Anggaran (Rp)</label>
                    <input type="number" id="kegiatan_anggaran" placeholder="Contoh: 5000000" min="0" step="1" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Bulan Pelaksanaan</span>
                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                        @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="kegiatan_bulan" value="{{ $i + 1 }}" class="peer sr-only">
                                <span class="block rounded-lg border border-slate-300 px-2 py-2 text-center text-xs font-medium text-slate-600 transition hover:border-primary peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $bulan }}</span>
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
    <div id="modalDokumentasi"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-camera text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Dokumentasi Kegiatan</h3>
                </div>
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
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                    <label id="dok_hapus_wrap" class="mt-2 hidden items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="dok_hapus" class="rounded border-slate-300 text-primary focus:ring-primary">
                        Hapus foto saat ini
                    </label>
                </div>

                <div>
                    <label for="dok_link" class="mb-2 block text-sm font-semibold text-slate-700">Link Dokumentasi</label>
                    <input type="url" id="dok_link" maxlength="2048" placeholder="https://..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
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
        const tahunBerjalan = @json($tahunBerjalan);
        const daftarBidang = @json($bidangs->map(fn ($b) => ['id' => $b->id, 'nama' => $b->nama])->values());
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
            showModal(id);
        }

        function tutupModal(id) {
            closeModal(id);
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
        function tambahProgram(bidangId = '') {
            $('#formProgram')[0].reset();
            $('#program_id').val('');
            $('#program_bidang').val(bidangId);
            $('#program_tahun_teks').text(tahunBerjalan);
            $('#modalProgramTitle').text('Tambah Program');
            bukaModal('modalProgram');
        }

        function editProgram(id) {
            const p = pkRows[id];
            $('#program_id').val(p.id);
            $('#program_tahun_teks').text(p.tahun);
            $('#program_bidang').val(p.bidang_id);
            $('#program_nama').val(p.nama);
            $('#modalProgramTitle').text('Edit Program');
            bukaModal('modalProgram');
        }

        $('#formProgram').on('submit', function(e) {
            e.preventDefault();
            const id = $('#program_id').val();
            kirim(id ? `${pkBase}/${id}` : pkBase, id ? 'PUT' : 'POST', {
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
            $('#dok_preview').toggleClass('hidden', !k.foto).attr('src', k.foto || '');
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
        const tombolAksi = 'flex h-9 w-9 flex-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition';
        const tombolEdit = `${tombolAksi} hover:border-primary hover:text-primary`;
        const tombolHapus = `${tombolAksi} hover:border-red-300 hover:bg-red-50 hover:text-red-600`;

        // Bulan berurutan diringkas jadi rentang: [1,2,3,7] menjadi "Jan–Mar, Jul".
        function labelBulan(bulan) {
            const rentang = [];
            [...bulan].sort((a, b) => a - b).forEach(b => {
                const akhir = rentang[rentang.length - 1];
                if (akhir && akhir[1] === b - 1) akhir[1] = b;
                else rentang.push([b, b]);
            });

            return rentang.map(([a, b]) => a === b ? namaBulan[a - 1] : `${namaBulan[a - 1]}–${namaBulan[b - 1]}`).join(', ');
        }

        function stripBulan(bulan) {
            return namaBulan.map((nama, i) => {
                const aktif = bulan.includes(i + 1);
                return `<span title="${nama}" class="flex h-6 flex-1 items-center justify-center rounded text-[10px] font-semibold ${aktif ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400'}">${nama[0]}</span>`;
            }).join('');
        }

        function bukaDeskripsi(tombol) {
            const teks = tombol.previousElementSibling;
            const tertutup = teks.classList.toggle('line-clamp-2');
            tombol.textContent = tertutup ? 'Selengkapnya' : 'Tutup';
            tombol.setAttribute('aria-expanded', String(!tertutup));
        }

        function renderKegiatan(k) {
            const panjang = k.deskripsi && (k.deskripsi.length > 150 || k.deskripsi.includes('\n'));
            const foto = k.foto ?
                `<a href="${esc(k.foto)}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-primary/5 px-2.5 py-1 text-xs font-semibold text-primary hover:bg-primary/10"><i class="fas fa-image"></i>Foto</a>` : '';
            const link = k.link ?
                `<a href="${esc(k.link)}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-primary/5 px-2.5 py-1 text-xs font-semibold text-primary hover:bg-primary/10"><i class="fas fa-link"></i>Link</a>` : '';

            return `
            <article class="grid gap-4 px-5 py-5 transition hover:bg-slate-50/70 lg:grid-cols-[minmax(0,1fr)_15rem_8.5rem_auto] lg:gap-6">
                <div class="min-w-0">
                    <h5 class="font-semibold leading-snug text-slate-800">${esc(k.nama)}</h5>
                    <p class="${panjang ? 'line-clamp-2 ' : ''}mt-1 whitespace-pre-line text-sm leading-6 text-slate-500">${esc(k.deskripsi)}</p>
                    ${panjang ? '<button type="button" onclick="bukaDeskripsi(this)" aria-expanded="false" class="mt-1 text-xs font-semibold text-primary hover:underline">Selengkapnya</button>' : ''}
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        ${foto}${link}
                        <button type="button" onclick="dokumentasi(${k.id})" class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-500 transition hover:border-primary hover:text-primary"><i class="fas fa-camera"></i>${k.foto || k.link ? 'Ubah dokumentasi' : 'Tambah dokumentasi'}</button>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Pelaksanaan: <span class="font-semibold text-slate-700">${esc(labelBulan(k.bulan))}</span></p>
                    <div class="mt-2 flex gap-0.5" aria-hidden="true">${stripBulan(k.bulan)}</div>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Anggaran</p>
                    <p class="mt-1 whitespace-nowrap font-bold text-slate-800">${esc(rupiah(k.anggaran))}</p>
                </div>

                <div class="flex items-start gap-2">
                    <button type="button" onclick="editKegiatan(${k.id})" title="Edit kegiatan" aria-label="Edit kegiatan ${esc(k.nama)}" class="${tombolEdit}"><i class="fas fa-pen text-xs"></i></button>
                    <button type="button" onclick="hapusKegiatan(${k.id})" title="Hapus kegiatan" aria-label="Hapus kegiatan ${esc(k.nama)}" class="${tombolHapus}"><i class="fas fa-trash text-xs"></i></button>
                </div>
            </article>`;
        }

        function renderProgram(p) {
            const total = p.kegiatans.reduce((jumlah, k) => jumlah + Number(k.anggaran), 0);
            const isi = p.kegiatans.length ?
                `<div class="divide-y divide-slate-100">${p.kegiatans.map(renderKegiatan).join('')}</div>` :
                `<div class="flex flex-col items-center gap-3 px-5 py-8 text-center">
                    <p class="text-sm text-slate-500">Belum ada kegiatan pada program ini.</p>
                    <button type="button" onclick="tambahKegiatan(${p.id})" class="inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-primary hover:text-primary"><i class="fas fa-plus text-xs"></i>Tambah kegiatan pertama</button>
                </div>`;

            return `
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-4 border-b border-slate-100 border-l-4 border-l-primary px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h4 class="text-lg font-bold leading-snug text-slate-800">${esc(p.nama)}</h4>
                        <p class="mt-1 text-xs text-slate-500">Tahun ${esc(p.tahun)} · ${p.kegiatans.length} kegiatan · ${esc(rupiah(total))}</p>
                    </div>
                    <div class="flex flex-none items-center gap-2">
                        <button type="button" onclick="tambahKegiatan(${p.id})" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-primary/10 px-3 text-xs font-semibold text-primary transition hover:bg-primary hover:text-white"><i class="fas fa-plus"></i>Kegiatan</button>
                        <button type="button" onclick="editProgram(${p.id})" title="Edit program" aria-label="Edit program ${esc(p.nama)}" class="${tombolEdit}"><i class="fas fa-pen text-xs"></i></button>
                        <button type="button" onclick="hapusProgram(${p.id})" title="Hapus program" aria-label="Hapus program ${esc(p.nama)}" class="${tombolHapus}"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                </header>
                ${isi}
            </section>`;
        }

        function renderBidang(b, nomor, programs) {
            const kegiatans = programs.flatMap(p => p.kegiatans);
            const total = kegiatans.reduce((jumlah, k) => jumlah + Number(k.anggaran), 0);
            const isi = programs.length ?
                `<div class="space-y-4">${programs.map(renderProgram).join('')}</div>` :
                '<p class="rounded-2xl border border-dashed border-slate-300 px-5 py-6 text-center text-sm text-slate-500">Belum ada program pada bidang ini.</p>';

            return `
            <section>
                <header class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-primary text-sm font-bold text-white">${String(nomor).padStart(2, '0')}</span>
                        <div class="min-w-0">
                            <h3 class="font-bold leading-snug text-slate-800">${esc(b.nama)}</h3>
                            <p class="text-xs text-slate-500">${programs.length} program · ${kegiatans.length} kegiatan · ${esc(rupiah(total))}</p>
                        </div>
                    </div>
                    <button type="button" onclick="tambahProgram(${b.id})" class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-primary hover:text-primary"><i class="fas fa-plus"></i>Program</button>
                </header>
                ${isi}
            </section>`;
        }

        // Bidang kosong hanya ditampilkan kalau semua program muat di satu halaman,
        // karena di halaman lain programnya belum tentu benar-benar kosong.
        function renderDaftar(data, satuHalaman) {
            const terpilih = Number($('#filterBidang').val());
            const html = daftarBidang
                .map((b, i) => ({ b, nomor: i + 1, programs: data.filter(p => Number(p.bidang_id) === b.id) }))
                .filter(g => (!terpilih || g.b.id === terpilih) && (satuHalaman || g.programs.length))
                .map(g => renderBidang(g.b, g.nomor, g.programs))
                .join('');

            return html || '<div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center text-sm text-slate-500">Program kerja tidak ditemukan.</div>';
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

                $('#ringkasanProgram').text(res.total);
                $('#ringkasanKegiatan').text(res.ringkasan.kegiatan);
                $('#ringkasanAnggaran').text(rupiah(res.ringkasan.anggaran));

                $('#daftarProgram').html(renderDaftar(data, res.last_page === 1));

                let html = '';
                if (res.last_page > 1) {
                    for (let i = 1; i <= res.last_page; i++) {
                        html += `<button type="button" onclick="loadProgram(${i})"
                            class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'border-slate-200 bg-white hover:bg-slate-50'}">${i}</button>`;
                    }
                }
                $('#paginationProgram').html(html);
            }).fail(function() {
                $('#daftarProgram').html('<div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-red-600">Gagal memuat program kerja.</div>');
            });
        }

        $('#filterTahun, #filterBidang').on('change', () => loadProgram(1));

        $(document).ready(() => loadProgram());
    </script>
@endpush
