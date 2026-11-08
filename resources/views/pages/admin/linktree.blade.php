@extends('layouts.app')

@section('title', 'Linktree')
@section('page', 'linktree')
@section('page_title', 'Linktree')

@section('content')

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Linktree</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Tampil untuk semua orang di
                    <a href="{{ route('linktree') }}" target="_blank" rel="noopener" class="font-semibold text-primary">{{ route('linktree') }}</a>.
                    Gunakan tombol panah untuk mengatur urutan.
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <button type="button" onclick="bukaFormLink('judul')"
                    class="inline-flex flex-1 items-center whitespace-nowrap justify-center gap-2 rounded-xl border border-slate-300 px-3 py-2.5 text-xs md:px-4 sm:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    <i class="fas fa-heading"></i>
                    Tambah Sub-judul
                </button>
                <button type="button" onclick="bukaFormLink('link')"
                    class="inline-flex flex-1 items-center whitespace-nowrap justify-center gap-2 rounded-xl bg-primary px-3 py-2.5 text-xs md:px-4 sm:text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
                    <i class="fas fa-plus"></i>
                    Tambah Link
                </button>
            </div>
        </div>

        <div id="daftarLink" class="space-y-3"></div>
    </div>

    <div id="modalLink"
        class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">
        <div class="modal-content mx-auto my-6 w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h3 id="modalLinkTitle" class="text-lg font-bold text-slate-800">Tambah Link</h3>
                <button type="button" onclick="closeModal('modalLink')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formLink" class="space-y-5 p-6">
                <input type="hidden" id="link_id">
                <input type="hidden" id="link_jenis">

                <div>
                    <label for="link_nama" id="labelLinkNama" class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                    <input type="text" id="link_nama" required maxlength="150"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div id="wrapLinkUrl">
                    <label for="link_url" class="mb-2 block text-sm font-semibold text-slate-700">Link</label>
                    <input type="url" id="link_url" maxlength="2048" placeholder="https://contoh.com"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('modalLink')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanLink"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        const linkUrl = @json(route('linktree.index'));
        const linkDataUrl = @json(route('linktree.getData'));
        const linkUrutanUrl = @json(route('linktree.urutan'));
        const csrfLink = '{{ csrf_token() }}';

        let linkItems = [];

        function escapeLink(value) {
            return $('<div>').text(value ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function cariLink(id) {
            return linkItems.find(item => item.id === id);
        }

        function pesanGagalLink(xhr, bawaan) {
            const errors = xhr.responseJSON?.errors;

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || bawaan)
            });
        }

        function tombolGeser(item, index) {
            const kelas = 'flex h-7 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40';

            return `
                <div class="flex flex-col gap-1">
                    <button onclick="geserLink(${item.id}, -1)" title="Naikkan" aria-label="Naikkan ${escapeLink(item.nama)}" class="${kelas}" ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-up text-xs"></i>
                    </button>
                    <button onclick="geserLink(${item.id}, 1)" title="Turunkan" aria-label="Turunkan ${escapeLink(item.nama)}" class="${kelas}" ${index === linkItems.length - 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                </div>`;
        }

        function renderLink() {
            $('#daftarLink').html(linkItems.length === 0 ?
                '<div class="rounded-2xl border border-slate-200 bg-white py-12 text-center text-sm text-slate-500">Belum ada link.</div>' :
                linkItems.map((item, index) => `
                <div class="flex items-center gap-3 rounded-2xl border p-4 shadow-sm ${item.link ? 'border-slate-200 bg-white' : 'border-primary/10 bg-primary/5'}">
                    ${tombolGeser(item, index)}
                    <div class="min-w-0 flex-1">
                        ${item.link ?
                            `<h4 class="break-words font-bold text-slate-800">${escapeLink(item.nama)}</h4>
                             <a href="${escapeLink(item.link)}" target="_blank" rel="noopener noreferrer" class="block truncate text-xs text-slate-500 hover:text-primary">${escapeLink(item.link)}</a>` :
                            `<p class="text-xs font-semibold text-primary">Sub-judul</p>
                             <h4 class="break-words font-bold text-slate-800">${escapeLink(item.nama)}</h4>`}
                    </div>
                    <button onclick="editLink(${item.id})" title="Edit"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 transition hover:border-amber-200 hover:bg-amber-500 hover:text-white">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button onclick="hapusLink(${item.id})" title="Hapus"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition hover:border-red-200 hover:bg-red-600 hover:text-white">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>`).join(''));
        }

        function loadLink() {
            $.get(linkDataUrl).done(function(res) {
                linkItems = res.data;
                renderLink();
            }).fail(function() {
                $('#daftarLink').html('<div class="py-12 text-center text-sm text-red-600">Gagal memuat data link.</div>');
            });
        }

        // Daftar langsung berubah di layar, lalu urutan barunya disimpan.
        function geserLink(id, arah) {
            const asal = linkItems.findIndex(item => item.id === id);
            const tujuan = asal + arah;

            if (tujuan < 0 || tujuan >= linkItems.length) return;

            [linkItems[asal], linkItems[tujuan]] = [linkItems[tujuan], linkItems[asal]];
            renderLink();

            $.ajax({
                url: linkUrutanUrl,
                type: 'POST',
                data: {
                    _token: csrfLink,
                    _method: 'PUT',
                    ids: linkItems.map(item => item.id)
                }
            }).fail(function(xhr) {
                pesanGagalLink(xhr, 'Urutan gagal disimpan.');
                loadLink();
            });
        }

        function isiFormLink(jenis, judulModal) {
            const judul = jenis === 'judul';

            $('#link_jenis').val(jenis);
            $('#modalLinkTitle').text(judulModal);
            $('#labelLinkNama').text(judul ? 'Nama Sub-judul' : 'Nama');
            $('#link_nama').attr('placeholder', judul ? 'Contoh: Pedoman' : 'Contoh: Instagram PPGT');
            $('#wrapLinkUrl').toggleClass('hidden', judul);
            $('#link_url').prop('required', !judul);
            showModal('modalLink');
        }

        function bukaFormLink(jenis) {
            $('#formLink')[0].reset();
            $('#link_id').val('');
            isiFormLink(jenis, jenis === 'judul' ? 'Tambah Sub-judul' : 'Tambah Link');
        }

        function editLink(id) {
            const item = cariLink(id);

            $('#link_id').val(id);
            $('#link_nama').val(item.nama);
            $('#link_url').val(item.link);
            isiFormLink(item.link ? 'link' : 'judul', item.link ? 'Edit Link' : 'Edit Sub-judul');
        }

        $('#formLink').on('submit', function(e) {
            e.preventDefault();

            const id = $('#link_id').val();
            const btn = $('#btnSimpanLink').prop('disabled', true);

            $.ajax({
                url: id ? `${linkUrl}/${id}` : linkUrl,
                type: 'POST',
                data: {
                    _token: csrfLink,
                    _method: id ? 'PUT' : 'POST',
                    jenis: $('#link_jenis').val(),
                    nama: $('#link_nama').val(),
                    link: $('#link_url').val()
                }
            }).done(function(res) {
                closeModal('modalLink');
                loadLink();
                Swal.fire('Berhasil', res.message, 'success');
            }).fail(function(xhr) {
                pesanGagalLink(xhr, 'Data gagal disimpan.');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });

        function hapusLink(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus item ini?',
                text: cariLink(id).nama,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(hasil) {
                if (!hasil.isConfirmed) return;

                $.ajax({
                    url: `${linkUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfLink,
                        _method: 'DELETE'
                    }
                }).done(function(res) {
                    loadLink();
                    Swal.fire('Berhasil', res.message, 'success');
                }).fail(function(xhr) {
                    pesanGagalLink(xhr, 'Data gagal dihapus.');
                });
            });
        }

        loadLink();
    </script>
@endpush
