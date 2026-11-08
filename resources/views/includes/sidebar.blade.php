@php
    $periodeAktif = request()->route('periode') ?? auth()->user()->periode;
    $page = trim($__env->yieldContent('page'));

    $menuKepengurusan = [];

    if ($periodeAktif) {
        foreach (['program_kerja' => ['Program Kerja', 'fa-clipboard-list'], 'bidang' => ['Bidang', 'fa-sitemap'], 'jabatan' => ['Jabatan', 'fa-id-badge'], 'pengurus' => ['Pengurus', 'fa-user-tie'], 'pendampingan' => ['Rekap Pendampingan', 'fa-handshake-angle']] as $kunci => [$label, $ikon]) {
            $menuKepengurusan[] = ['izin' => $kunci, 'label' => $label, 'ikon' => $ikon, 'url' => route('kepengurusan.' . $kunci . '.index', $periodeAktif), 'halaman' => [$kunci]];
        }
    }

    // Entri dengan 'item' tampil sebagai grup, lainnya sebagai menu tunggal. Izin 'admin' hanya untuk administrator, 'semua' untuk setiap akun.
    $daftarMenu = [
        ['izin' => 'dashboard', 'label' => 'Dashboard', 'ikon' => 'fa-house', 'url' => route('dashboard'), 'halaman' => ['dashboard']],
        // 'ringkas': kalau hanya satu submenu yang boleh dibuka, tampil sebagai menu tunggal.
        ['kunci' => 'anggota', 'label' => 'Data Anggota', 'ikon' => 'fa-users', 'ringkas' => true, 'item' => [
            ['izin' => 'data_anggota', 'label' => 'Daftar Anggota', 'ikon' => 'fa-address-book', 'url' => route('data_anggota.index'), 'halaman' => ['anggota']],
            ['izin' => 'jemaat_kelompok', 'label' => 'Kelompok', 'ikon' => 'fa-users-rectangle', 'url' => route('jemaat.kelompok.index'), 'halaman' => ['jemaat_kelompok']],
        ]],
        ['kunci' => 'inventaris', 'label' => 'Inventaris & Peminjaman', 'ikon' => 'fa-boxes-stacked', 'item' => [
            ['izin' => 'inventaris', 'label' => 'Inventaris', 'ikon' => 'fa-box-archive', 'url' => route('inventaris.index'), 'halaman' => ['inventaris']],
            ['izin' => 'peminjaman', 'label' => 'Peminjaman', 'ikon' => 'fa-handshake', 'url' => route('peminjaman-admin'), 'halaman' => ['peminjaman', 'pengembalian']],
        ]],
        ['kunci' => 'jemaat', 'label' => 'Kepengurusan', 'ikon' => 'fa-people-group', 'item' => [
            ['izin' => 'jemaat_bidang', 'label' => 'Bidang', 'ikon' => 'fa-sitemap', 'url' => route('jemaat.bidang.index'), 'halaman' => ['jemaat_bidang']],
            ['izin' => 'jemaat_pengurus', 'label' => 'Pengurus', 'ikon' => 'fa-user-tie', 'url' => route('jemaat.pengurus.index'), 'halaman' => ['jemaat_pengurus']],
        ]],
        ['izin' => 'artikel', 'label' => 'Artikel', 'ikon' => 'fa-newspaper', 'url' => route('jemaat.artikel.index'), 'halaman' => ['artikel']],
        ['izin' => 'jemaat_pemilihan', 'label' => 'Pemilihan', 'ikon' => 'fa-check-to-slot', 'url' => route('jemaat.pemilihan.index'), 'halaman' => ['jemaat_pemilihan']],
        ['kunci' => 'kepengurusan', 'label' => 'Kepengurusan', 'ikon' => 'fa-people-group', 'item' => $menuKepengurusan],
        ['izin' => 'persetujuan_artikel', 'label' => 'Persetujuan Artikel', 'ikon' => 'fa-file-circle-check', 'url' => route('persetujuan_artikel.index'), 'halaman' => ['persetujuan_artikel']],
        ['izin' => 'tim_tanggap', 'label' => 'Golongan Darah', 'ikon' => 'fa-droplet', 'url' => route('tim_tanggap.index'), 'halaman' => ['tim_tanggap']],
        ['izin' => 'admin', 'label' => 'Pemilihan Konferensi', 'ikon' => 'fa-check-to-slot', 'url' => route('pemilihan.index'), 'halaman' => ['pemilihan']],
        ['kunci' => 'layanan', 'label' => 'Layanan', 'ikon' => 'fa-layer-group', 'item' => [
            ['izin' => 'absen', 'label' => 'Absen', 'ikon' => 'fa-clipboard-check', 'url' => route('absen.index'), 'halaman' => ['absen']],
            ['izin' => 'linktree', 'label' => 'Linktree', 'ikon' => 'fa-link', 'url' => route('linktree.index'), 'halaman' => ['linktree']],
            ['izin' => 'generator_qr', 'label' => 'Generator QR Code', 'ikon' => 'fa-qrcode', 'url' => route('generate_qr.index'), 'halaman' => ['generator_qr']],
        ]],
        ['kunci' => 'administrasi', 'label' => 'Administrasi', 'ikon' => 'fa-shield-halved', 'item' => [
            ['izin' => 'admin', 'label' => 'Manajemen Role', 'ikon' => 'fa-user-shield', 'url' => route('role.index'), 'halaman' => ['role']],
            ['izin' => 'admin', 'label' => 'Master Periode', 'ikon' => 'fa-calendar-days', 'url' => route('periode.index'), 'halaman' => ['periode']],
            ['izin' => 'admin', 'label' => 'Master Pengguna', 'ikon' => 'fa-user-gear', 'url' => route('pengguna.index'), 'halaman' => ['pengguna']],
            ['izin' => 'admin', 'label' => 'Audit Log', 'ikon' => 'fa-clock-rotate-left', 'url' => route('audit_log.index'), 'halaman' => ['audit_log']],
        ]],
        ['izin' => 'semua', 'label' => 'Panduan', 'ikon' => 'fa-circle-question', 'url' => route('panduan'), 'halaman' => ['panduan']],
    ];

    $bolehBuka = fn (array $menu) => match ($menu['izin']) {
        'semua' => true,
        'admin' => auth()->user()->isAdmin(),
        default => auth()->user()->bisaMenu($menu['izin']),
    };
@endphp

{{-- Overlay Mobile --}}
<div id="overlay"
    class="fixed inset-0 z-40 hidden bg-black/40 backdrop-blur-sm
    opacity-0 transition-opacity duration-300 lg:hidden">
</div>

{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed left-0 top-0 z-50 flex h-screen w-72
    -translate-x-full flex-col border-r border-slate-200
    bg-white shadow-xl transition-transform duration-300 ease-in-out
    lg:translate-x-0 lg:shadow-none">

    {{-- Logo --}}
    <div class="flex h-20 shrink-0 items-center border-b border-slate-200 px-6">

        <img src="{{ asset('images/logo.png') }}" alt="Logo PPGT KMTr" class="h-10 w-10 object-contain md:h-12 md:w-12">

        <div class="ml-3">
            <h1 class="text-base font-bold text-slate-800 md:text-lg">
                PPGT KMTr
            </h1>

            <p class="text-xs text-slate-500">
                Sistem Informasi
            </p>
        </div>

        {{-- Close Mobile --}}
        <button type="button" id="btnCloseSidebar" aria-label="Tutup sidebar"
            class="ml-auto rounded-lg p-2 text-slate-500
            transition duration-200 hover:bg-slate-100 hover:text-slate-800
            lg:hidden">

            <i class="fa-solid fa-xmark text-base md:text-xl"></i>
        </button>

    </div>

    {{-- Menu --}}
    <div class="flex-1 overflow-y-auto px-4 py-6">

        <p class="mb-3 px-3 text-[10px] font-semibold uppercase
            tracking-wider text-slate-400 md:text-xs">
            Menu
        </p>

        <nav class="space-y-2">

            @foreach ($daftarMenu as $menu)
                @php
                    if ($menu['ringkas'] ?? false) {
                        $bolehRingkas = array_filter($menu['item'], $bolehBuka);

                        if (count($bolehRingkas) === 1) {
                            $menu = ['label' => $menu['label'], 'ikon' => $menu['ikon']] + reset($bolehRingkas);
                        }
                    }
                @endphp

                @if (isset($menu['item']))
                    @php
                        $itemBoleh = array_filter($menu['item'], $bolehBuka);
                        $grupTerbuka = in_array($page, array_merge(...array_values(array_column($itemBoleh, 'halaman')) ?: [[]]), true);
                    @endphp

                    @if ($itemBoleh)
                        <div class="space-y-1">

                            <button type="button" data-grup="{{ $menu['kunci'] }}" aria-expanded="{{ $grupTerbuka ? 'true' : 'false' }}"
                                aria-controls="submenu-{{ $menu['kunci'] }}"
                                class="group flex w-full items-center gap-3 rounded-xl px-4 py-3
                                text-sm transition-all duration-200 ease-in-out md:text-base
                                {{ $grupTerbuka ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100' }}">

                                <i class="fa-solid {{ $menu['ikon'] }} w-5 text-center
                                    transition-transform duration-200 group-hover:scale-110"></i>

                                <span class="flex-1 text-left font-medium">{{ $menu['label'] }}</span>

                                <i data-grup-ikon class="fa-solid fa-chevron-down text-xs
                                    transition-transform duration-300 ease-in-out
                                    {{ $grupTerbuka ? 'rotate-180' : '' }}"></i>

                            </button>

                            <div id="submenu-{{ $menu['kunci'] }}"
                                class="grid transition-[grid-template-rows,opacity]
                                duration-300 ease-in-out
                                {{ $grupTerbuka ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0' }}">

                                <div class="overflow-hidden">
                                    <div class="ml-5 space-y-1 border-l-2 border-slate-200 py-1 pl-4">

                                        @foreach ($itemBoleh as $item)
                                            <a href="{{ $item['url'] }}"
                                                class="flex items-center gap-3 rounded-xl px-4 py-2.5
                                                text-sm transition-all duration-200 ease-in-out
                                                {{ in_array($page, $item['halaman'], true)
                                                    ? 'bg-primary text-white shadow-md'
                                                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                                                <i class="fa-solid {{ $item['ikon'] }} w-5 text-center"></i>

                                                <span class="font-medium">{{ $item['label'] }}</span>
                                            </a>
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                @elseif ($bolehBuka($menu))
                    <a href="{{ $menu['url'] }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3
                        text-sm transition-all duration-200 ease-in-out md:text-base
                        {{ in_array($page, $menu['halaman'], true)
                            ? 'bg-primary text-white shadow-md'
                            : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                        <i class="fa-solid {{ $menu['ikon'] }} w-5 text-center"></i>

                        <span class="font-medium">{{ $menu['label'] }}</span>
                    </a>
                @endif
            @endforeach

            {{-- Pengaturan --}}
            <a href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3
                text-sm transition-all duration-200 ease-in-out md:text-base
                {{ $page == 'pengaturan'
                    ? 'bg-primary text-white shadow-md'
                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                <i
                    class="fa-solid fa-gear w-5 text-center
                    transition-transform duration-300
                    group-hover:rotate-45"></i>

                <span class="font-medium">
                    Pengaturan
                </span>

            </a>

        </nav>

    </div>

    {{-- Footer --}}
    <div class="shrink-0 border-t border-slate-200 p-5">

        <div class="flex items-center">

            <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full
                bg-slate-200 md:h-11 md:w-11">

                @if (auth()->user()->foto)
                    <img src="{{ asset('storage/user/' . auth()->user()->foto) }}" alt="Foto profil"
                        class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center">
                        <i class="fa-regular fa-user text-slate-500"></i>
                    </div>
                @endif

            </div>

            <div class="ml-3 min-w-0 flex-1">

                <div class="truncate text-xs font-semibold md:text-sm">
                    {{ auth()->user()->name }}
                </div>

                <div class="text-[10px] text-slate-500 md:text-xs">
                    {{ auth()->user()->role?->nama }}
                </div>

            </div>

        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-5">

            @csrf

            <button type="submit"
                class="w-full rounded-xl border border-red-200
                py-3 text-xs text-red-600
                transition-all duration-200 ease-in-out
                hover:border-red-300 hover:bg-red-50
                active:scale-[0.98] md:text-base">

                <i class="fa-solid fa-right-from-bracket mr-2"></i>

                Logout

            </button>

        </form>

    </div>

</aside>

{{-- JavaScript Sidebar --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const btnCloseSidebar = document.getElementById('btnCloseSidebar');

        // Buka tutup grup menu
        document.querySelectorAll('[data-grup]').forEach(function(btnMenu) {
            const submenu = document.getElementById(btnMenu.getAttribute('aria-controls'));
            const iconMenu = btnMenu.querySelector('[data-grup-ikon]');

            btnMenu.addEventListener('click', function() {
                const isOpen = btnMenu.getAttribute('aria-expanded') === 'true';

                btnMenu.setAttribute('aria-expanded', String(!isOpen));

                submenu.classList.toggle('grid-rows-[1fr]', !isOpen);
                submenu.classList.toggle('grid-rows-[0fr]', isOpen);

                submenu.classList.toggle('opacity-100', !isOpen);
                submenu.classList.toggle('opacity-0', isOpen);

                iconMenu.classList.toggle('rotate-180', !isOpen);
            });
        });

        // Buka sidebar mobile
        window.openSidebar = function() {
            if (!sidebar || !overlay) return;

            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');

            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
            });

            document.body.classList.add('overflow-hidden');
        };

        // Tutup sidebar mobile
        window.closeSidebar = function() {
            if (!sidebar || !overlay) return;

            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');

            document.body.classList.remove('overflow-hidden');

            setTimeout(() => {
                if (window.innerWidth < 1024) {
                    overlay.classList.add('hidden');
                }
            }, 300);
        };

        if (btnCloseSidebar) {
            btnCloseSidebar.addEventListener('click', window.closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', window.closeSidebar);
        }

        // Menutup sidebar ketika berpindah ke desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar?.classList.remove('-translate-x-full');
                overlay?.classList.add('hidden');
                overlay?.classList.remove('opacity-100');
                overlay?.classList.add('opacity-0');
                document.body.classList.remove('overflow-hidden');
            } else {
                sidebar?.classList.add('-translate-x-full');
            }
        });
    });
</script>
