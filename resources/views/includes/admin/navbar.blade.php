<header class="sticky top-0 z-30 bg-white border-b border-slate-200">

    <div class="h-16 sm:h-20 flex items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- Left --}}
        <div class="flex items-center gap-2 sm:gap-4">

            {{-- Mobile --}}
            <button id="btnOpenSidebar"
                class="lg:hidden flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl border border-slate-200 hover:bg-slate-100 transition">

                <i class="fa-solid fa-bars"></i>

            </button>

        </div>

        {{-- Right --}}
        <div class="flex items-center gap-2 sm:gap-4">

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
            <button
                class="relative flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl hover:bg-slate-100 transition">

                <i class="fa-regular fa-bell"></i>

                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500">
                </span>

            </button>

            {{-- User --}}
            <div class="relative group">

                <button
                    class="flex items-center gap-2 sm:gap-3 rounded-xl px-2.5 sm:px-3 py-1.5 sm:py-2 hover:bg-slate-50 transition">

                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full overflow-hidden bg-slate-200 flex-shrink-0">

                        @if (auth()->user()->foto)
                            <img src="{{ asset('storage/user/' . auth()->user()->foto) }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">

                                <i class="fa-regular fa-user text-slate-500"></i>

                            </div>
                        @endif

                    </div>

                    <div class="hidden lg:block text-left max-w-[180px]">

                        <div class="text-sm font-semibold truncate">
                            {{ auth()->user()->nama_lengkap }}
                        </div>

                        <div class="text-xs text-slate-500 truncate">
                            Administrator
                        </div>

                    </div>

                    <i class="hidden lg:block fa-solid fa-chevron-down text-xs text-slate-400"></i>

                </button>

                {{-- Dropdown --}}
                <div
                    class="absolute right-0 mt-3 w-64 max-w-[calc(100vw-2rem)] bg-white border border-slate-200 rounded-2xl shadow-lg
                    opacity-0 invisible translate-y-2 text-xs md:text-base
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
