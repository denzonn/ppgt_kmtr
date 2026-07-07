<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>

    @stack('prepend-style')
    @include('includes.style')
    @stack('addon-style')

    <link rel="icon" href="{{ asset('images/logo.png') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-slate-100 text-slate-800 font-poppins" data-page="@yield('page')"
    data-toast-success="{{ session('toast_success') }}" data-toast-error="{{ session('error') }}">

    <div class="flex min-h-screen">

        {{-- ========================= --}}
        {{-- Sidebar --}}
        {{-- ========================= --}}

        @include('includes.sidebar')

        {{-- ========================= --}}
        {{-- Main --}}
        {{-- ========================= --}}

        <div class="flex-1 lg:ml-72">

            {{-- Navbar --}}
            @include('includes.admin.navbar')

            {{-- Content --}}
            <main class="p-6 lg:p-8">

                @yield('content')

            </main>

        </div>

    </div>

    @stack('prepend-script')
    @include('includes.script')
    @stack('addon-script')

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        document.getElementById('btnOpenSidebar')?.addEventListener('click', () => {

            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');

        });

        document.getElementById('btnCloseSidebar')?.addEventListener('click', () => {

            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');

        });

        overlay?.addEventListener('click', () => {

            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');

        });
    </script>

</body>

</html>
