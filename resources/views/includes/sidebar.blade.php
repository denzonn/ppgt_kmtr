@php
    $page = trim($__env->yieldContent('page'));

    $inventarisPeminjamanOpen = in_array($page, ['inventaris', 'peminjaman', 'pengembalian']);
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

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3
                text-sm transition-all duration-200 ease-in-out md:text-base
                {{ $page == 'dashboard'
                    ? 'bg-primary text-white shadow-md'
                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            {{-- Inventaris & Peminjaman --}}
            <div class="space-y-1">

                {{-- Menu Utama --}}
                <button type="button" id="btnInventarisPeminjaman" aria-controls="submenuInventarisPeminjaman"
                    aria-expanded="{{ $inventarisPeminjamanOpen ? 'true' : 'false' }}"
                    class="group flex w-full items-center gap-3 rounded-xl px-4 py-3
                    text-sm transition-all duration-200 ease-in-out md:text-base
                    {{ $inventarisPeminjamanOpen ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100' }}">

                    <i
                        class="fa-solid fa-boxes-stacked w-5 text-center
                        transition-transform duration-200 group-hover:scale-110"></i>

                    <span class="flex-1 text-left font-medium">
                        Inventaris & Peminjaman
                    </span>

                    <i id="iconInventarisPeminjaman"
                        class="fa-solid fa-chevron-down text-xs
                        transition-transform duration-300 ease-in-out
                        {{ $inventarisPeminjamanOpen ? 'rotate-180' : '' }}">
                    </i>

                </button>

                {{-- Submenu dengan animasi smooth --}}
                <div id="submenuInventarisPeminjaman"
                    class="grid transition-[grid-template-rows,opacity]
                    duration-300 ease-in-out
                    {{ $inventarisPeminjamanOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0' }}">

                    <div class="overflow-hidden">

                        <div class="ml-5 space-y-1 border-l-2 border-slate-200 py-1 pl-4">

                            {{-- Submenu Inventaris --}}
                            <a href="{{ route('inventaris') }}"
                                class="flex items-center gap-3 rounded-xl px-4 py-2.5
                                text-sm transition-all duration-200 ease-in-out
                                {{ $page == 'inventaris'
                                    ? 'bg-primary text-white shadow-md'
                                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                                <i class="fa-solid fa-box-archive w-5 text-center"></i>

                                <span class="font-medium">
                                    Inventaris
                                </span>

                            </a>

                            {{-- Submenu Peminjaman --}}
                            <a href="{{ route('peminjaman-admin') }}"
                                class="flex items-center gap-3 rounded-xl px-4 py-2.5
                                text-sm transition-all duration-200 ease-in-out
                                {{ in_array($page, ['peminjaman', 'pengembalian'])
                                    ? 'bg-primary text-white shadow-md'
                                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                                <i class="fa-solid fa-handshake w-5 text-center"></i>

                                <span class="font-medium">
                                    Peminjaman
                                </span>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Anggota --}}
            <a href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3
                text-sm transition-all duration-200 ease-in-out md:text-base
                {{ $page == 'anggota'
                    ? 'bg-primary text-white shadow-md'
                    : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                <i class="fa-solid fa-users w-5 text-center"></i>

                <span class="font-medium">
                    Anggota
                </span>

            </a>

            {{-- Generator QR Code --}}
            <a href="{{ route('generate_qr.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3
    text-sm transition-all duration-200 ease-in-out md:text-base
    {{ $page == 'generator_qr'
        ? 'bg-primary text-white shadow-md'
        : 'text-slate-600 hover:translate-x-1 hover:bg-slate-100' }}">

                <i class="fa-solid fa-qrcode w-5 text-center"></i>

                <span class="font-medium">
                    Generator QR Code
                </span>
            </a>

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
                    Administrator
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
        const btnMenu = document.getElementById('btnInventarisPeminjaman');
        const submenu = document.getElementById('submenuInventarisPeminjaman');
        const iconMenu = document.getElementById('iconInventarisPeminjaman');

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const btnCloseSidebar = document.getElementById('btnCloseSidebar');

        // Toggle submenu Inventaris & Peminjaman
        if (btnMenu && submenu && iconMenu) {
            btnMenu.addEventListener('click', function() {
                const isOpen = btnMenu.getAttribute('aria-expanded') === 'true';

                btnMenu.setAttribute('aria-expanded', String(!isOpen));

                submenu.classList.toggle('grid-rows-[1fr]', !isOpen);
                submenu.classList.toggle('grid-rows-[0fr]', isOpen);

                submenu.classList.toggle('opacity-100', !isOpen);
                submenu.classList.toggle('opacity-0', isOpen);

                iconMenu.classList.toggle('rotate-180', !isOpen);
            });
        }

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
