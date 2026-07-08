@php
    $page = trim($__env->yieldContent('page'));
@endphp

{{-- Overlay Mobile --}}
<div id="overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden lg:hidden">
</div>

{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed lg:fixed top-0 left-0 z-50 h-screen w-72
    bg-white border-r border-slate-200
    flex flex-col
    transition-transform duration-300
    -translate-x-full lg:translate-x-0">

    {{-- Logo --}}
    <div class="h-20 border-b border-slate-200 flex items-center px-6">

        <img src="{{ asset('images/logo.png') }}" class="w-10 h-10 md:w-12 md:h-12 object-contain">

        <div class="ml-3">

            <h1 class="font-bold text-base md:text-lg text-slate-800">
                PPGT KMTr
            </h1>

            <p class="text-xs text-slate-500">
                Sistem Informasi
            </p>

        </div>

        {{-- Close Mobile --}}
        <button id="btnCloseSidebar" class="ml-auto lg:hidden text-slate-500">

            <i class="fa-solid fa-xmark text-base md:text-xl"></i>

        </button>

    </div>

    {{-- Menu --}}
    <div class="flex-1 overflow-y-auto px-4 py-6">

        <p class="text-[10px] md:text-xs font-semibold text-slate-400 uppercase mb-3 px-3">
            Menu
        </p>

        <nav class="space-y-2">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition text-sm md:text-base
                {{ $page == 'dashboard' ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            {{-- Inventaris --}}
            <a href="{{ route('inventaris') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition  text-sm md:text-base
                {{ $page == 'inventaris' ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-box-archive w-5 text-center"></i>

                <span class="font-medium">
                    Inventaris
                </span>

            </a>

            @php
                $page = trim($__env->yieldContent('page'));

                $transaksiOpen = in_array($page, ['peminjaman', 'pengembalian']);
            @endphp

            <a href="{{ route('peminjaman-admin') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition  text-sm md:text-base
                {{ $page == 'peminjaman' ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-handshake w-5 text-center"></i>

                Peminjaman

            </a>

            {{-- Anggota --}}
            <a href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition  text-sm md:text-base
                {{ $page == 'anggota' ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-users w-5 text-center"></i>

                <span class="font-medium">
                    Anggota
                </span>

            </a>

            {{-- Pengaturan --}}
            <a href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition  text-sm md:text-base
                {{ $page == 'pengaturan' ? 'bg-primary text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-gear w-5 text-center"></i>

                <span class="font-medium">
                    Pengaturan
                </span>

            </a>

        </nav>

    </div>

    {{-- Footer --}}
    <div class="border-t border-slate-200 p-5">

        <div class="flex items-center">

            <div class="w-9 h-9 md:w-11 md:h-11 rounded-full bg-slate-200 overflow-hidden">

                @if (auth()->user()->foto)
                    <img src="{{ asset('storage/user/' . auth()->user()->foto) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">

                        <i class="fa-regular fa-user text-slate-500"></i>

                    </div>
                @endif

            </div>

            <div class="ml-3 flex-1">

                <div class="font-semibold text-xs md:text-sm">
                    {{ auth()->user()->name }}
                </div>

                <div class="text-[10px] md:text-xs text-slate-500">
                    Administrator
                </div>

            </div>

        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-5">

            @csrf

            <button
                class="w-full rounded-xl border border-red-200
                text-red-600 py-3 text-xs md:text-base
                hover:bg-red-50
                transition">

                <i class="fa-solid fa-right-from-bracket mr-2"></i>

                Logout

            </button>

        </form>

    </div>

</aside>
