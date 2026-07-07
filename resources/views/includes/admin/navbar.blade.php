<header class="sticky top-0 z-30 bg-white border-b border-slate-200">

    <div class="h-20 flex items-center justify-between px-6 lg:px-8">

        {{-- Left --}}
        <div class="flex items-center gap-4">

            {{-- Mobile --}}
            <button id="btnOpenSidebar"
                class="lg:hidden w-11 h-11 rounded-xl border border-slate-200 hover:bg-slate-100 transition">

                <i class="fa-solid fa-bars"></i>

            </button>

        </div>

        {{-- Right --}}
        <div class="flex items-center gap-4">

            {{-- Search --}}
            <div class="hidden md:flex relative">

                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                </i>

                <input type="text" placeholder="Cari..."
                    class="w-72 pl-11 pr-4 h-11 rounded-xl
                    border border-slate-200
                    bg-slate-50
                    focus:border-primary
                    focus:ring-2
                    focus:ring-primary/20
                    outline-none">

            </div>

            {{-- Notification --}}
            <button class="relative w-11 h-11 rounded-xl border border-slate-200 hover:bg-slate-100 transition">

                <i class="fa-regular fa-bell"></i>

                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500">
                </span>

            </button>

            {{-- User --}}
            <div class="relative group">

                <button
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50 transition">

                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200">

                        @if (auth()->user()->foto)
                            <img src="{{ asset('storage/user/' . auth()->user()->foto) }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">

                                <i class="fa-regular fa-user text-slate-500"></i>

                            </div>
                        @endif

                    </div>

                    <div class="hidden lg:block text-left">

                        <div class="text-sm font-semibold">
                            {{ auth()->user()->nama_lengkap }}
                        </div>

                        <div class="text-xs text-slate-500">
                            Administrator
                        </div>

                    </div>

                    <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>

                </button>

                {{-- Dropdown --}}
                <div
                    class="absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-lg
                    opacity-0 invisible translate-y-2
                    group-hover:opacity-100 group-hover:visible group-hover:translate-y-0
                    transition-all duration-200">

                    <div class="p-4 border-b border-slate-100">

                        <div class="font-semibold">
                            {{ auth()->user()->nama_lengkap }}
                        </div>

                        <div class="text-sm text-slate-500 mt-1">
                            {{ auth()->user()->email }}
                        </div>

                    </div>

                    <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50">

                        <i class="fa-regular fa-user"></i>

                        Profile

                    </a>

                    <form action="{{ route('logout') }}" method="POST">

                        @csrf

                        <button class="flex items-center gap-3 w-full px-4 py-3 text-red-600 hover:bg-red-50">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            Logout

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</header>
