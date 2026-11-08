@extends('layouts.app')

@section('title', 'Master Periode')
@section('page', 'periode')
@section('page_title', 'Master Periode')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Master Periode</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola periode kepengurusan beserta bidang dan penggunanya.</p>
            </div>
            <button type="button" onclick="tambahPeriode()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                <i class="fas fa-plus"></i>
                Tambah Periode
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-white p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-calendar-days"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Periode</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Periode kepengurusan yang telah terdaftar.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="text" id="searchPeriode" placeholder="Cari tahun..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>
            </div>

            <div id="gridPeriode" class="p-5">
                <div class="col-span-full py-12 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data periode...</div>
            </div>

<div id="paginationPeriode" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-white px-5 py-4 empty:hidden"></div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="modalPeriode"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

        <div class="modal-content mx-auto my-6 w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-calendar-days text-lg"></i>
                    </div>
                    <h3 id="modalPeriodeTitle" class="text-lg font-bold text-slate-800">Tambah Periode</h3>
                </div>

                <button type="button" onclick="tutupModalPeriode()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg
                text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formPeriode" class="space-y-5 p-6">
                <input type="hidden" id="periode_id">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tahun_mulai" class="mb-2 block text-sm font-semibold text-slate-700">Tahun Mulai</label>
                        <input type="number" id="tahun_mulai" placeholder="Contoh: 2020" required min="1900" max="2100"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3
                        text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label for="tahun_selesai" class="mb-2 block text-sm font-semibold text-slate-700">Tahun Selesai</label>
                        <input type="number" id="tahun_selesai" placeholder="Contoh: 2022" required min="1900" max="2100"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3
                        text-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <fieldset id="periode_bidang_wrap">
                    <legend class="mb-1 text-sm font-semibold text-slate-700">Bidang pada periode ini</legend>
                    <p class="mb-3 text-xs text-slate-500">Bidang yang dicentang langsung dibuat. Pilih minimal satu.</p>
                    <div class="space-y-2">
                        @foreach ($bidangDefault as $nama)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-primary hover:bg-blue-50 has-[:checked]:border-primary has-[:checked]:bg-blue-50 has-[:checked]:font-semibold">
                                <input type="checkbox" name="periode_bidang" value="{{ $nama }}" checked
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                                {{ $nama }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="tutupModalPeriode()"
                        class="rounded-xl border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSimpanPeriode"
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
        const periodeBaseUrl = @json(route('periode.index'));
        const periodeDataUrl = @json(route('periode.getData'));
        const kepengurusanBaseUrl = @json(url('/kepengurusan'));

        let periodePage = 1;
        let periodeTimer = null;

        function escapePeriode(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function urlPeriode(id) {
            return `${periodeBaseUrl.replace(/\/$/, '')}/${encodeURIComponent(id)}`;
        }

        function bukaModalPeriode() {
            showModal('modalPeriode');
        }

        function tutupModalPeriode() {
            closeModal('modalPeriode');
        }

        function tambahPeriode() {
            $('#formPeriode')[0].reset();
            $('#periode_id').val('');
            $('#periode_bidang_wrap').removeClass('hidden');
            $('#modalPeriodeTitle').text('Tambah Periode');
            bukaModalPeriode();
        }

        function editPeriode(id) {
            $.get(urlPeriode(id)).done(function(item) {
                $('#formPeriode')[0].reset();
                $('#periode_id').val(item.id);
                $('#tahun_mulai').val(item.tahun_mulai);
                $('#tahun_selesai').val(item.tahun_selesai);
                // Bidang periode yang sudah ada diatur di menu Bidang.
                $('#periode_bidang_wrap').addClass('hidden');
                $('#modalPeriodeTitle').text('Edit Periode');
                bukaModalPeriode();
            }).fail(function() {
                Swal.fire('Gagal', 'Periode tidak ditemukan.', 'error');
            });
        }

        $('#formPeriode').on('submit', function(e) {
            e.preventDefault();

            const id = $('#periode_id').val();
            const btn = $('#btnSimpanPeriode');
            const bidangs = $('input[name=periode_bidang]:checked').map((_, el) => el.value).get();

            if (!id && bidangs.length === 0) {
                Swal.fire('Gagal', 'Pilih minimal satu bidang.', 'error');
                return;
            }

            $.ajax({
                url: id ? urlPeriode(id) : periodeBaseUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: id ? 'PUT' : 'POST',
                    tahun_mulai: $('#tahun_mulai').val(),
                    tahun_selesai: $('#tahun_selesai').val(),
                    ...(id ? {} : { bidangs })
                },
                beforeSend: () => btn.prop('disabled', true),
                success: function(res) {
                    tutupModalPeriode();
                    loadPeriode(periodePage);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Periode gagal disimpan.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().map(escapePeriode).join('<br>');
                    } else {
                        message = escapePeriode(message);
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

        function loadPeriode(page = 1) {
            periodePage = page;

            $.get(periodeDataUrl, {
                page: page,
                search: $('#searchPeriode').val()
            }).done(function(res) {
                const data = res.data || [];

                if (data.length === 0) {
                    $('#gridPeriode').html(
                        '<div class="col-span-full py-12 text-center text-sm text-slate-500">Periode tidak ditemukan.</div>'
                    );
                    $('#paginationPeriode').html('');
                    return;
                }

                const tahunIni = new Date().getFullYear();
                const angka = (nilai, label, ikon) => `
                    <div class="flex items-center gap-1.5 text-sm text-slate-500">
                        <i class="fas ${ikon} text-xs text-primary"></i>
                        <b class="font-semibold text-slate-800">${Number(nilai)}</b> ${label}
                    </div>`;

                $('#gridPeriode').html(data.map(function(item, i) {
                    const kelola = `${kepengurusanBaseUrl}/${encodeURIComponent(item.id)}/program-kerja`;
                    const berjalan = tahunIni >= item.tahun_mulai && tahunIni <= item.tahun_selesai;
                    const akanDatang = tahunIni < item.tahun_mulai;
                    const pertama = i === 0;
                    const terakhir = i === data.length - 1;

                    const status = berjalan ?
                        '<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"><i class="fas fa-circle text-[6px]"></i>Berjalan</span>' :
                        `<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">${akanDatang ? 'Akan datang' : 'Selesai'}</span>`;

                    const penanda = berjalan ?
                        'bg-primary text-white ring-4 ring-primary/15' :
                        (akanDatang ? 'border border-primary/30 bg-white text-primary' : 'bg-slate-100 text-slate-400');
                    const ikon = berjalan ? 'fa-flag' : (akanDatang ? 'fa-clock' : 'fa-check');

                    return `
                    <div class="flex gap-3 sm:gap-4">
                        <div class="relative flex w-9 shrink-0 justify-center">
                            ${data.length > 1 ? `<span class="absolute w-0.5 bg-slate-200 ${pertama ? 'bottom-0 top-7' : (terakhir ? 'top-0 h-7' : 'inset-y-0')}"></span>` : ''}
                            <span class="relative mt-3 flex h-9 w-9 items-center justify-center rounded-full text-xs ${penanda}">
                                <i class="fas ${ikon}"></i>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 ${terakhir ? '' : 'pb-4'}">
                            <div class="flex flex-col gap-3 rounded-2xl border p-4 transition hover:shadow-md lg:flex-row lg:items-center lg:justify-between ${berjalan ? 'border-primary/40 bg-primary/[0.02]' : 'border-slate-200 bg-white'}">
                                <div class="lg:w-56">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-slate-800">${escapePeriode(item.tahun_mulai)} - ${escapePeriode(item.tahun_selesai)}</span>
                                        ${status}
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-wrap gap-x-5 gap-y-1">
                                    ${angka(item.bidangs_count, 'bidang', 'fa-sitemap')}
                                    ${angka(item.pengurus_count, 'pengurus', 'fa-user-tie')}
                                    ${angka(item.users_count, 'pengguna', 'fa-users')}
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="${kelola}" class="inline-flex h-9 items-center gap-2 rounded-xl px-4 text-sm font-semibold transition ${berjalan ? 'bg-primary text-white hover:opacity-90' : 'bg-primary/10 text-primary hover:bg-primary hover:text-white'}">
                                        Kelola <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                    <button onclick="editPeriode(${Number(item.id)})" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="hapusPeriode(${Number(item.id)})" title="Hapus" class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join(''));

renderPaginationPeriode(res);
            }).fail(function() {
                $('#gridPeriode').html(
                    '<div class="col-span-full py-10 text-center text-sm text-red-600">Gagal memuat data periode.</div>'
                );
            });
        }

function renderPaginationPeriode(res) {
            if (res.last_page <= 1) {
                $('#paginationPeriode').html('');
                return;
            }

            let html = '';
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button onclick="loadPeriode(${i})"
                    class="rounded-lg border px-3 py-2 text-sm transition ${i === res.current_page ? 'border-primary bg-primary text-white' : 'hover:bg-slate-50'}">${i}</button>`;
            }
            $('#paginationPeriode').html(html);
        }

        function hapusPeriode(id) {
            Swal.fire({
                title: 'Hapus Periode?',
                text: 'Periode ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlPeriode(id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadPeriode(periodePage);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Periode gagal dihapus.', 'error');
                    }
                });
            });
        }

        $('#searchPeriode').on('input', function() {
            clearTimeout(periodeTimer);
            periodeTimer = setTimeout(() => loadPeriode(1), 400);
        });

        $(document).ready(() => loadPeriode());
    </script>
@endpush
