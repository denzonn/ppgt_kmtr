<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Belum Ada</title>
    @include('includes.style')
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50">
    <div class="rounded-xl bg-white p-8 text-center shadow">
        <p class="mb-4 text-slate-700">Akun Anda belum memiliki akses. Hubungi admin.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-red-600">Logout</button>
        </form>
    </div>
</body>
</html>
