@extends('layouts.app')

@section('title', 'Persetujuan Artikel')
@section('page', 'persetujuan_artikel')
@section('page_title', 'Persetujuan Artikel')

@push('addon-style')
    @vite(['resources/css/konten-artikel.css'])
@endpush

@section('content')

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Persetujuan Artikel</h2>
            <p class="mt-1 text-sm text-slate-500">Baca artikel kiriman jemaat, lalu setujui atau tolak dengan alasan.</p>
        </div>

        <div id="tabStatus" class="grid grid-cols-2 gap-3 lg:grid-cols-4" role="group" aria-label="Filter status artikel"></div>

        <div id="daftarPersetujuan" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"></div>
        <div id="halamanPersetujuan" class="flex items-center justify-between gap-3 text-sm text-slate-500 empty:hidden"></div>
    </div>

    <div id="modalBaca"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content relative mx-auto my-6 w-full max-w-3xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div id="bacaSampul" class="relative hidden h-64 overflow-hidden rounded-t-2xl bg-slate-100 sm:h-80">
                <img id="bacaFotoLatar" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-xl">
                <img id="bacaFoto" alt="Foto sampul artikel" class="relative h-full w-full object-contain">
            </div>

            <button type="button" onclick="closeModal('modalBaca')" aria-label="Tutup"
                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-lg bg-white/90 text-slate-600 shadow-sm transition hover:bg-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <div class="px-6 pt-6 sm:px-10 sm:pt-8">
                <span id="bacaStatus" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold"></span>
                <h3 id="bacaJudul" class="mt-3 break-words pr-10 text-2xl font-bold leading-snug text-slate-800"></h3>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 border-b border-slate-100 pb-5 text-sm text-slate-500">
                    <span><i class="fa-solid fa-church mr-1.5 text-xs text-primary"></i><span id="bacaJemaat"></span></span>
                    <span><i class="fa-solid fa-pen-nib mr-1.5 text-xs text-primary"></i><span id="bacaPenulis"></span></span>
                    <span><i class="fa-regular fa-calendar mr-1.5 text-xs text-primary"></i><span id="bacaTanggal"></span></span>
                </div>
            </div>

            <div class="px-6 py-6 sm:px-10">
                {{-- Isi sudah disaring server saat disimpan. Class ck-content membuatnya tampil sama seperti di editor. --}}
                <div id="bacaIsi" class="ck-content"></div>
            </div>

            <div id="bacaHasil" class="mx-6 mb-6 hidden break-words rounded-xl p-4 text-sm sm:mx-10"></div>

            {{-- Menempel di bawah layar supaya tombol tetap terlihat saat artikel panjang digulir. --}}
            <div id="bacaAksi" class="sticky -bottom-4 z-10 flex flex-col gap-3 rounded-b-2xl border-t border-slate-200 bg-white px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-10">
                <p class="text-xs text-slate-500">Baca sampai selesai sebelum memberi keputusan.</p>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <button type="button" onclick="tolakArtikel()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        <i class="fa-solid fa-xmark"></i> Tolak
                    </button>
                    <button type="button" onclick="setujuiArtikel()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                        <i class="fa-solid fa-check"></i> Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const persetujuanUrl = @json(route('persetujuan_artikel.index'));
        const persetujuanDataUrl = @json(route('persetujuan_artikel.getData'));
        const csrfPersetujuan = '{{ csrf_token() }}';
        const statusArtikel = {
            menunggu: { label: 'Menunggu', ikon: 'fa-clock', lencana: 'bg-amber-100 text-amber-800', kotak: 'bg-amber-50 text-amber-600' },
            disetujui: { label: 'Disetujui', ikon: 'fa-circle-check', lencana: 'bg-emerald-100 text-emerald-800', kotak: 'bg-emerald-50 text-emerald-600' },
            ditolak: { label: 'Ditolak', ikon: 'fa-circle-xmark', lencana: 'bg-red-100 text-red-800', kotak: 'bg-red-50 text-red-600' }
        };
        const statusLain = { ikon: 'fa-circle', lencana: 'bg-slate-100 text-slate-700' };

        let persetujuanItems = [];
        let persetujuanPage = 1;
        // Yang menunggu diperiksa tampil lebih dulu, karena itu tugas utama halaman ini.
        let filterStatus = 'menunggu';
        let artikelDibaca = null;

        function escapePersetujuan(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function renderTab(statistik) {
            const tab = [
                ...Object.entries(statusArtikel).map(([kunci, s]) => ({ kunci, label: s.label, ikon: s.ikon, kotak: s.kotak, jumlah: statistik[kunci] })),
                { kunci: '', label: 'Semua', ikon: 'fa-newspaper', kotak: 'bg-primary/10 text-primary', jumlah: statistik.semua }
            ];

            $('#tabStatus').html(tab.map(t => `
                <button type="button" onclick="pilihStatus('${t.kunci}')" aria-pressed="${filterStatus === t.kunci}"
                    class="flex items-center gap-3 rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md ${filterStatus === t.kunci ? 'border-primary ring-2 ring-primary/20' : 'border-slate-200'}">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${t.kotak}"><i class="fa-solid ${t.ikon}"></i></span>
                    <span class="min-w-0">
                        <span class="block text-xl font-bold leading-tight text-slate-800">${Number(t.jumlah).toLocaleString('id-ID')}</span>
                        <span class="block truncate text-xs text-slate-500">${t.label}</span>
                    </span>
                </button>`).join(''));
        }

        function pilihStatus(status) {
            filterStatus = status;
            loadPersetujuan(1);
        }

        function kartuPersetujuan(item) {
            const status = statusArtikel[item.status] || { ...statusLain, label: item.status };
            const foto = escapePersetujuan(item.foto);

            return `
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="relative h-44 overflow-hidden bg-gradient-to-br from-blue-50 via-slate-50 to-cyan-50">
                        ${item.foto ? `
                            <img src="${foto}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-xl" loading="lazy">
                            <img src="${foto}" alt="Foto sampul ${escapePersetujuan(item.judul)}" class="relative h-full w-full object-contain" loading="lazy">` : `
                            <span class="flex h-full items-center justify-center text-slate-300"><i class="fa-solid fa-image text-3xl"></i></span>`}
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold shadow-sm ${status.lencana}">
                            <i class="fa-solid ${status.ikon} text-[10px]"></i>${escapePersetujuan(status.label)}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h4 class="line-clamp-2 break-words font-bold leading-snug text-slate-800">${escapePersetujuan(item.judul)}</h4>
                        <p class="mt-2 line-clamp-3 break-words text-sm leading-relaxed text-slate-500">${escapePersetujuan(item.ringkasan)}</p>

                        <div class="mt-auto pt-4">
                            <div class="flex items-center gap-2 border-t border-slate-100 pt-3">
                                <div class="min-w-0 flex-1 text-xs text-slate-500">
                                    <p class="truncate font-medium text-slate-600"><i class="fa-solid fa-church mr-1 text-[10px] text-primary"></i>${escapePersetujuan(item.jemaat ?? '-')}</p>
                                    <p class="mt-0.5 truncate">${escapePersetujuan(item.penulis)} &middot; ${escapePersetujuan(item.tanggal)}</p>
                                </div>
                                <button onclick="bacaArtikel(${item.id})" aria-label="Baca ${escapePersetujuan(item.judul)}"
                                    class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-3.5 text-sm font-semibold text-white transition hover:opacity-90">
                                    <i class="fas fa-book-open text-xs"></i> Baca
                                </button>
                            </div>
                        </div>
                    </div>
                </article>`;
        }

        function pesanKosong() {
            if (filterStatus === 'menunggu') {
                return `
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-14 text-center">
                        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-circle-check text-2xl"></i></span>
                        <h3 class="mt-4 font-bold text-slate-800">Semua artikel sudah diperiksa</h3>
                        <p class="mt-1 text-sm text-slate-500">Belum ada artikel baru yang menunggu persetujuan.</p>
                    </div>`;
            }

            return '<div class="col-span-full rounded-2xl border border-slate-200 bg-white py-14 text-center text-sm text-slate-500">Tidak ada artikel.</div>';
        }

        function loadPersetujuan(page = 1) {
            persetujuanPage = page;

            $.get(persetujuanDataUrl, {
                page: page,
                status: filterStatus
            }).done(function(res) {
                // Halaman terakhir bisa kosong setelah artikelnya diperiksa.
                if (res.data.length === 0 && res.total > 0) {
                    loadPersetujuan(res.last_page);
                    return;
                }

                persetujuanItems = res.data;
                renderTab(res.statistik);

                $('#daftarPersetujuan').html(persetujuanItems.length === 0 ? pesanKosong() : persetujuanItems.map(kartuPersetujuan).join(''));

                const tombol = 'rounded-lg border bg-white px-3 py-2 text-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40';

                $('#halamanPersetujuan').html(res.last_page <= 1 ? '' : `
                    <span>Halaman ${res.current_page} dari ${res.last_page}</span>
                    <div class="flex gap-2">
                        <button class="${tombol}" ${res.current_page <= 1 ? 'disabled' : ''} onclick="loadPersetujuan(${res.current_page - 1})" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="${tombol}" ${res.current_page >= res.last_page ? 'disabled' : ''} onclick="loadPersetujuan(${res.current_page + 1})" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>`);
            }).fail(function() {
                $('#daftarPersetujuan').html('<div class="col-span-full py-12 text-center text-sm text-red-600">Gagal memuat artikel.</div>');
            });
        }

        function bacaArtikel(id) {
            artikelDibaca = persetujuanItems.find(a => a.id === id);

            const status = statusArtikel[artikelDibaca.status] || { ...statusLain, label: artikelDibaca.status };

            $('#bacaStatus')
                .attr('class', `inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold ${status.lencana}`)
                .html(`<i class="fa-solid ${status.ikon} text-[10px]"></i>${escapePersetujuan(status.label)}`);
            $('#bacaJudul').text(artikelDibaca.judul);
            $('#bacaJemaat').text(artikelDibaca.jemaat ?? '-');
            $('#bacaPenulis').text(artikelDibaca.penulis);
            $('#bacaTanggal').text(artikelDibaca.tanggal);
            $('#bacaFoto, #bacaFotoLatar').attr('src', artikelDibaca.foto || '');
            $('#bacaSampul').toggleClass('hidden', !artikelDibaca.foto);
            $('#bacaIsi').html(artikelDibaca.isi);

            const ditolak = artikelDibaca.status === 'ditolak';
            const hasil = artikelDibaca.status === 'disetujui' ? `Disetujui oleh ${artikelDibaca.diperiksa_oleh ?? '-'}.` :
                ditolak ? `Ditolak oleh ${artikelDibaca.diperiksa_oleh ?? '-'}. Alasan: ${artikelDibaca.alasan_tolak ?? '-'}` : '';

            $('#bacaHasil').text(hasil)
                .toggleClass('hidden', !hasil)
                .toggleClass('bg-red-50 text-red-700', ditolak)
                .toggleClass('bg-emerald-50 text-emerald-700', !ditolak);
            $('#bacaAksi').toggleClass('hidden', artikelDibaca.status !== 'menunggu');
            showModal('modalBaca');
        }

        function kirimPemeriksaan(aksi, tambahan = {}) {
            $.ajax({
                url: `${persetujuanUrl}/${artikelDibaca.id}/${aksi}`,
                type: 'POST',
                data: {
                    _token: csrfPersetujuan,
                    versi: artikelDibaca.versi,
                    ...tambahan
                }
            }).done(function(res) {
                closeModal('modalBaca');
                loadPersetujuan(persetujuanPage);
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                const errors = xhr.responseJSON?.errors;

                closeModal('modalBaca');
                loadPersetujuan(persetujuanPage);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Artikel gagal diproses.')
                });
            });
        }

        function setujuiArtikel() {
            Swal.fire({
                icon: 'question',
                title: 'Setujui artikel ini?',
                text: artikelDibaca.judul,
                showCancelButton: true,
                confirmButtonText: 'Setujui',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (hasil.isConfirmed) kirimPemeriksaan('setujui');
            });
        }

        function tolakArtikel() {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak artikel ini?',
                input: 'textarea',
                inputLabel: 'Alasan penolakan',
                inputPlaceholder: 'Tulis alasan agar penulis bisa memperbaiki',
                inputValidator: nilai => nilai.trim() ? null : 'Alasan penolakan wajib diisi.',
                showCancelButton: true,
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (hasil.isConfirmed) kirimPemeriksaan('tolak', { alasan: hasil.value });
            });
        }

        loadPersetujuan();
    </script>
@endpush
