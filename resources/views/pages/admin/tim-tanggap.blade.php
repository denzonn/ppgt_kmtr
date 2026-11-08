@extends('layouts.app')

@section('title', 'Golongan Darah')
@section('page', 'tim_tanggap')
@section('page_title', 'Golongan Darah')

@section('content')

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Golongan Darah</h2>
            <p class="mt-1 text-sm text-slate-500">Cari calon pendonor dari seluruh anggota, lalu hubungi lewat WhatsApp.</p>
        </div>

        <div id="statistikDarah" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"></div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <label for="pesanWa" class="block font-bold text-slate-800">Pesan WhatsApp</label>
            <p class="mt-1 text-xs text-slate-500">
                Ubah sesuai kebutuhan, misalnya tambahkan nama rumah sakit. <code>{nama}</code> dan <code>{golongan}</code> terisi otomatis untuk tiap orang.
            </p>
            <textarea id="pesanWa" rows="4" maxlength="1500"
                class="mt-3 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">Shalom {nama}, kami dari PPGT KMTr sedang membutuhkan donor darah golongan {golongan}. Apakah Anda bersedia mendonorkan darah? Mohon balas pesan ini. Terima kasih, Tuhan memberkati.</textarea>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-3">
                <div class="relative sm:col-span-3">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="cariDarah" placeholder="Cari nama atau no. anggota..." aria-label="Cari anggota"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                </div>
                <select id="filterGolongan" aria-label="Filter golongan darah"
                    class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none focus:border-primary focus:bg-white">
                    <option value="">Semua Golongan</option>
                    @foreach ($golongan as $g)
                        <option value="{{ $g }}">Golongan {{ $g }}</option>
                    @endforeach
                    <option value="kosong">Belum diisi</option>
                </select>
                <select id="filterRhesus" aria-label="Filter rhesus"
                    class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none focus:border-primary focus:bg-white">
                    <option value="">Semua Rhesus</option>
                    <option value="Positif">Rhesus Positif</option>
                    <option value="Negatif">Rhesus Negatif</option>
                </select>
                <select id="filterJemaat" aria-label="Filter jemaat"
                    class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3.5 pr-9 text-sm text-slate-600 outline-none focus:border-primary focus:bg-white">
                    <option value="">Semua Jemaat</option>
                    @foreach ($jemaats as $jemaat)
                        <option value="{{ $jemaat->id }}">{{ $jemaat->nama }}</option>
                    @endforeach
                </select>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 sm:col-span-3">
                    <input type="checkbox" id="filterPunyaWa" class="rounded border-slate-300 text-primary focus:ring-primary">
                    Hanya yang punya nomor telepon
                </label>
            </div>

            <p id="jumlahDarah" class="border-b border-slate-100 px-4 py-3 text-xs text-slate-500"></p>
            <div id="daftarDarah" class="divide-y divide-slate-100"></div>
            <div id="halamanDarah" class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 text-sm text-slate-500 empty:hidden"></div>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const darahUrl = @json(route('tim_tanggap.index'));
        const darahDataUrl = @json(route('tim_tanggap.getData'));
        const csrfDarah = '{{ csrf_token() }}';

        let darahItems = [];
        let darahTimer = null;
        let darahRequest = null;

        // Penanda di layar saja, supaya orang yang sama tidak terhubungi dua kali dalam satu sesi kerja.
        const sudahDibuka = new Set();

        function escapeDarah(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function labelGolongan(item) {
            return item.golongan ? `${item.golongan}${item.rhesus ? ' ' + item.rhesus : ''}` : '';
        }

        function renderStatistik(statistik) {
            const warna = { A: 'text-red-600 bg-red-50', B: 'text-amber-600 bg-amber-50', AB: 'text-primary bg-primary/10', O: 'text-emerald-600 bg-emerald-50', kosong: 'text-slate-500 bg-slate-100' };

            $('#statistikDarah').html(Object.entries(statistik).map(([kunci, s]) => `
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-slate-500">${kunci === 'kosong' ? 'Belum diisi' : 'Golongan ' + escapeDarah(kunci)}</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${warna[kunci]}"><i class="fa-solid fa-droplet text-sm"></i></span>
                    </div>
                    <p class="mt-1 text-2xl font-bold text-slate-800">${Number(s.total).toLocaleString('id-ID')}</p>
                    <p class="mt-1 text-xs text-slate-500">${Number(s.punya_wa).toLocaleString('id-ID')} punya nomor</p>
                </div>`).join(''));
        }

        function tombolWa(item) {
            if (!item.wa) {
                return `<span class="shrink-0 text-xs text-slate-400">${item.telepon ? 'Nomor tidak valid' : 'Tanpa nomor'}</span>`;
            }

            const dibuka = sudahDibuka.has(item.id);

            return `
                <button type="button" onclick="kirimWa(${item.id})"
                    class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold transition ${dibuka ? 'border border-slate-200 text-slate-500 hover:bg-slate-50' : 'bg-emerald-600 text-white hover:opacity-90'}">
                    <i class="${dibuka ? 'fas fa-check' : 'fab fa-whatsapp'}"></i>
                    ${dibuka ? 'Sudah dibuka' : 'Kirim WA'}
                </button>`;
        }

        function barisDarah(item) {
            const golongan = labelGolongan(item);

            return `
                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold ${golongan ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-400'}">${escapeDarah(item.golongan ?? '?')}</span>
                    <div class="min-w-0 flex-1">
                        <div class="break-words text-sm font-semibold text-slate-800">${escapeDarah(item.nama)}${item.status === 'Tidak Aktif' ? ' <span class="ml-1 rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-500">Tidak Aktif</span>' : ''}</div>
                        <div class="break-words text-xs text-slate-500">${escapeDarah(item.jemaat ?? '-')} &middot; ${golongan ? 'Golongan ' + escapeDarah(golongan) : 'Golongan belum diisi'}${item.telepon ? ' &middot; ' + escapeDarah(item.telepon) : ''}</div>
                    </div>
                    ${tombolWa(item)}
                </div>`;
        }

        function renderDaftar() {
            $('#daftarDarah').html(darahItems.length === 0 ?
                '<div class="py-12 text-center text-sm text-slate-500">Anggota tidak ditemukan.</div>' :
                darahItems.map(barisDarah).join(''));
        }

        function loadDarah(page = 1) {
            if (darahRequest) darahRequest.abort();

            darahRequest = $.get(darahDataUrl, {
                page: page,
                search: $('#cariDarah').val(),
                golongan: $('#filterGolongan').val(),
                rhesus: $('#filterRhesus').val(),
                jemaat: $('#filterJemaat').val(),
                punya_wa: $('#filterPunyaWa').is(':checked') ? 1 : 0
            }).done(function(res) {
                darahItems = res.data;
                renderStatistik(res.statistik);
                renderDaftar();
                $('#jumlahDarah').text(`${Number(res.total).toLocaleString('id-ID')} anggota ditemukan`);

                const tombol = 'rounded-lg border px-3 py-2 text-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40';

                $('#halamanDarah').html(res.last_page <= 1 ? '' : `
                    <span>Halaman ${res.current_page} dari ${res.last_page}</span>
                    <div class="flex gap-2">
                        <button class="${tombol}" ${res.current_page <= 1 ? 'disabled' : ''} onclick="loadDarah(${res.current_page - 1})" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="${tombol}" ${res.current_page >= res.last_page ? 'disabled' : ''} onclick="loadDarah(${res.current_page + 1})" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>`);
            }).fail(function(xhr, status) {
                if (status === 'abort') return;

                $('#daftarDarah').html('<div class="py-12 text-center text-sm text-red-600">Gagal memuat data.</div>');
            });
        }

        function kirimWa(id) {
            const item = darahItems.find(a => a.id === id);
            const pesan = $('#pesanWa').val().trim();

            if (!pesan) {
                Swal.fire('Pesan kosong', 'Tulis pesannya dulu.', 'warning');
                return;
            }

            const teks = pesan
                .replaceAll('{nama}', item.nama)
                .replaceAll('{golongan}', labelGolongan(item) || 'yang dibutuhkan');

            // Dibuka langsung saat diklik supaya tidak diblokir browser.
            window.open(`https://wa.me/${item.wa}?text=${encodeURIComponent(teks)}`, '_blank', 'noopener');

            $.post(`${darahUrl}/${id}/pesan`, { _token: csrfDarah });

            sudahDibuka.add(id);
            renderDaftar();
        }

        $('#cariDarah').on('input', function() {
            clearTimeout(darahTimer);
            darahTimer = setTimeout(() => loadDarah(1), 300);
        });

        $('#filterGolongan, #filterRhesus, #filterJemaat, #filterPunyaWa').on('change', () => loadDarah(1));

        loadDarah();
    </script>
@endpush
