@extends('layouts.app')

@section('title', 'Artikel')
@section('page', 'artikel')
@section('page_title', 'Artikel')

@section('content')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Artikel</h2>
                <p class="mt-1 text-sm text-slate-500">Artikel {{ auth()->user()->jemaat?->nama }}. Setiap artikel diperiksa Pengurus Klasis sebelum disetujui.</p>
            </div>
            <a href="{{ route('jemaat.artikel.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tulis Artikel
            </a>
        </div>

        <div id="tabStatus" class="grid grid-cols-2 gap-3 lg:grid-cols-4" role="group" aria-label="Filter status artikel"></div>

        <div id="daftarArtikel" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"></div>
        <div id="halamanArtikel" class="flex items-center justify-between gap-3 text-sm text-slate-500 empty:hidden"></div>
    </div>

@endsection

@push('addon-script')
    <script>
        const artikelUrl = @json(route('jemaat.artikel.index'));
        const artikelDataUrl = @json(route('jemaat.artikel.getData'));
        const csrfArtikel = '{{ csrf_token() }}';
        const statusArtikel = {
            menunggu: { label: 'Menunggu', ikon: 'fa-clock', lencana: 'bg-amber-100 text-amber-800', kotak: 'bg-amber-50 text-amber-600' },
            disetujui: { label: 'Disetujui', ikon: 'fa-circle-check', lencana: 'bg-emerald-100 text-emerald-800', kotak: 'bg-emerald-50 text-emerald-600' },
            ditolak: { label: 'Ditolak', ikon: 'fa-circle-xmark', lencana: 'bg-red-100 text-red-800', kotak: 'bg-red-50 text-red-600' }
        };

        let artikelItems = [];
        let artikelPage = 1;
        let filterStatus = '';

        function escapeArtikel(value) {
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
                { kunci: '', label: 'Semua', ikon: 'fa-newspaper', kotak: 'bg-primary/10 text-primary', jumlah: statistik.semua },
                ...Object.entries(statusArtikel).map(([kunci, s]) => ({ kunci, label: s.label, ikon: s.ikon, kotak: s.kotak, jumlah: statistik[kunci] }))
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

        function kartuArtikel(item) {
            const status = statusArtikel[item.status] || { label: item.status, ikon: 'fa-circle', lencana: 'bg-slate-100 text-slate-700' };
            const foto = escapeArtikel(item.foto);

            return `
                <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="relative h-44 overflow-hidden bg-gradient-to-br from-blue-50 via-slate-50 to-cyan-50">
                        ${item.foto ? `
                            <img src="${foto}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-xl" loading="lazy">
                            <img src="${foto}" alt="Foto sampul ${escapeArtikel(item.judul)}" class="relative h-full w-full object-contain" loading="lazy">` : `
                            <span class="flex h-full items-center justify-center text-slate-300"><i class="fa-solid fa-image text-3xl"></i></span>`}
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold shadow-sm ${status.lencana}">
                            <i class="fa-solid ${status.ikon} text-[10px]"></i>${escapeArtikel(status.label)}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h4 class="line-clamp-2 break-words font-bold leading-snug text-slate-800">${escapeArtikel(item.judul)}</h4>
                        <p class="mt-2 line-clamp-3 break-words text-sm leading-relaxed text-slate-500">${escapeArtikel(item.ringkasan)}</p>

                        ${item.status === 'ditolak' ? `
                            <p class="mt-3 break-words rounded-xl bg-red-50 p-3 text-xs text-red-700"><span class="font-semibold">Alasan ditolak:</span> ${escapeArtikel(item.alasan_tolak)}</p>` : ''}

                        <div class="mt-auto pt-4">
                            <div class="flex items-center gap-2 border-t border-slate-100 pt-3">
                                <div class="min-w-0 flex-1 text-xs text-slate-500">
                                    <p class="truncate font-medium text-slate-600"><i class="fa-solid fa-pen-nib mr-1 text-[10px] text-primary"></i>${escapeArtikel(item.penulis)}</p>
                                    <p class="mt-0.5"><i class="fa-regular fa-calendar mr-1 text-[10px]"></i>${escapeArtikel(item.tanggal)}</p>
                                </div>
                                <a href="${escapeArtikel(artikelUrl)}/${item.id}/edit" title="Edit" aria-label="Edit ${escapeArtikel(item.judul)}"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <button onclick="hapusArtikel(${item.id})" title="Hapus" aria-label="Hapus ${escapeArtikel(item.judul)}"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>`;
        }

        function pesanKosong() {
            if (filterStatus) {
                return `<div class="col-span-full rounded-2xl border border-slate-200 bg-white py-14 text-center text-sm text-slate-500">Tidak ada artikel berstatus ${escapeArtikel(statusArtikel[filterStatus].label)}.</div>`;
            }

            return `
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-14 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary"><i class="fa-solid fa-feather-pointed text-2xl"></i></span>
                    <h3 class="mt-4 font-bold text-slate-800">Belum ada artikel</h3>
                    <p class="mt-1 text-sm text-slate-500">Mulai bagikan kabar dan cerita dari jemaat Anda.</p>
                </div>`;
        }

        function pilihStatus(status) {
            filterStatus = status;
            loadArtikel(1);
        }

        function loadArtikel(page = 1) {
            artikelPage = page;

            $.get(artikelDataUrl, { page: page, status: filterStatus }).done(function(res) {
                // Halaman terakhir bisa kosong setelah isinya dihapus.
                if (res.data.length === 0 && res.total > 0) {
                    loadArtikel(res.last_page);
                    return;
                }

                artikelItems = res.data;
                renderTab(res.statistik);

                $('#daftarArtikel').html(artikelItems.length === 0 ? pesanKosong() : artikelItems.map(kartuArtikel).join(''));

                const tombol = 'rounded-lg border bg-white px-3 py-2 text-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40';

                $('#halamanArtikel').html(res.last_page <= 1 ? '' : `
                    <span>Halaman ${res.current_page} dari ${res.last_page}</span>
                    <div class="flex gap-2">
                        <button class="${tombol}" ${res.current_page <= 1 ? 'disabled' : ''} onclick="loadArtikel(${res.current_page - 1})" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="${tombol}" ${res.current_page >= res.last_page ? 'disabled' : ''} onclick="loadArtikel(${res.current_page + 1})" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>`);
            }).fail(function() {
                $('#daftarArtikel').html('<div class="col-span-full py-12 text-center text-sm text-red-600">Gagal memuat artikel.</div>');
            });
        }

        function hapusArtikel(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus artikel ini?',
                text: artikelItems.find(a => a.id === id).judul,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${artikelUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfArtikel,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadArtikel(artikelPage);
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Artikel gagal dihapus.', 'error');
                });
            });
        }

        loadArtikel();
    </script>
@endpush
