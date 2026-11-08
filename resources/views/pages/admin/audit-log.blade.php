@extends('layouts.app')

@section('title', 'Audit Log')
@section('page', 'audit_log')
@section('page_title', 'Audit Log')

@section('content')

    <div class="space-y-6">

        <div>
            <h2 class="text-xl font-bold text-slate-800">Audit Log</h2>
            <p class="mt-1 text-sm text-slate-500">Riwayat siapa mengubah apa dan kapan. Catatan ini hanya bisa dibaca.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="searchAudit" placeholder="Cari pelaku, nama data, atau jenis data..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                </div>
                <select id="filterAksi" aria-label="Filter aksi"
                    class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                    <option value="">Semua Aksi</option>
                    <option value="dibuat">Dibuat</option>
                    <option value="diubah">Diubah</option>
                    <option value="dihapus">Dihapus</option>
                    <option value="login">Login</option>
                    <option value="login gagal">Login Gagal</option>
                    <option value="logout">Logout</option>
                    <option value="kirim pesan wa">Kirim Pesan WA</option>
                </select>
            </div>

            <div id="daftarAudit" class="divide-y divide-slate-100"></div>

            <div id="halamanAudit" class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 text-sm text-slate-500 empty:hidden"></div>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const auditDataUrl = @json(route('audit_log.getData'));
        const warnaAksi = {
            'dibuat': 'bg-emerald-50 text-emerald-700',
            'diubah': 'bg-amber-50 text-amber-700',
            'dihapus': 'bg-red-50 text-red-700',
            'login gagal': 'bg-red-50 text-red-700'
        };

        let auditTimer = null;
        let auditRequest = null;

        function escapeAudit(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function nilaiAudit(value) {
            if (value === null || value === undefined || value === '') {
                return '<span class="text-slate-400">kosong</span>';
            }

            return escapeAudit(typeof value === 'object' ? JSON.stringify(value) : value);
        }

        function rincianAudit(perubahan) {
            const baris = Object.entries(perubahan || {});

            if (baris.length === 0) return '';

            return `
                <details class="mt-2">
                    <summary class="cursor-pointer text-xs font-semibold text-primary">Lihat rincian (${baris.length})</summary>
                    <div class="mt-2 space-y-1 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                        ${baris.map(([kolom, isi]) => `
                            <div class="break-words">
                                <span class="font-semibold text-slate-700">${escapeAudit(kolom)}:</span>
                                ${'lama' in isi ? nilaiAudit(isi.lama) : ''}
                                ${'lama' in isi && 'baru' in isi ? '<i class="fas fa-arrow-right px-1 text-[10px] text-slate-400"></i>' : ''}
                                ${'baru' in isi ? nilaiAudit(isi.baru) : ''}
                            </div>`).join('')}
                    </div>
                </details>`;
        }

        function barisAudit(log) {
            const warna = warnaAksi[log.aksi] || 'bg-primary/10 text-primary';
            const objek = log.objek ? `${log.objek}${log.label ? ': ' : log.model_id ? ' #' + log.model_id : ''}` : '';

            return `
                <div class="p-4">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 break-words text-sm text-slate-800">
                            <span class="mr-1 inline-block rounded-lg px-2 py-0.5 text-xs font-semibold ${warna}">${escapeAudit(log.aksi)}</span>
                            <span class="font-semibold">${escapeAudit(objek)}${escapeAudit(log.label)}</span>
                        </div>
                        <div class="shrink-0 whitespace-nowrap text-xs text-slate-500">${escapeAudit(log.waktu)}</div>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        ${log.pelaku ? `Oleh <span class="font-medium text-slate-600">${escapeAudit(log.pelaku)}</span>` : 'Tanpa login'}
                        ${log.ip ? ` &middot; IP ${escapeAudit(log.ip)}` : ''}
                    </p>
                    ${rincianAudit(log.perubahan)}
                </div>`;
        }

        function loadAudit(page = 1) {
            if (auditRequest) auditRequest.abort();

            auditRequest = $.get(auditDataUrl, {
                page: page,
                search: $('#searchAudit').val(),
                aksi: $('#filterAksi').val()
            }).done(function(res) {
                $('#daftarAudit').html(res.data.length === 0 ?
                    '<div class="py-12 text-center text-sm text-slate-500">Belum ada catatan.</div>' :
                    res.data.map(barisAudit).join(''));

                const tombol = 'rounded-lg border px-3 py-2 text-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40';

                $('#halamanAudit').html(res.last_page <= 1 ? '' : `
                    <span>Halaman ${res.current_page} dari ${res.last_page}</span>
                    <div class="flex gap-2">
                        <button class="${tombol}" ${res.current_page <= 1 ? 'disabled' : ''} onclick="loadAudit(${res.current_page - 1})" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="${tombol}" ${res.current_page >= res.last_page ? 'disabled' : ''} onclick="loadAudit(${res.current_page + 1})" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>`);
            }).fail(function(xhr, status) {
                if (status === 'abort') return;

                $('#daftarAudit').html('<div class="py-12 text-center text-sm text-red-600">Gagal memuat audit log.</div>');
            });
        }

        $('#searchAudit').on('input', function() {
            clearTimeout(auditTimer);
            auditTimer = setTimeout(() => loadAudit(1), 300);
        });

        $('#filterAksi').on('change', () => loadAudit(1));

        loadAudit();
    </script>
@endpush
