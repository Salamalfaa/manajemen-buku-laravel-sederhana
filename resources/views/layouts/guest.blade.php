<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · Ruang Baca</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <main class="auth-shell">
        <div class="full-width" style="max-width: 460px">
            <a class="brand justify-content-center mb-3" href="{{ url('/') }}" aria-label="Kembali ke beranda Ruang Baca">
                <span class="brand-mark"><i class="bi bi-bookshelf"></i></span>
                <span>
                    <span class="brand-name d-block">Ruang Baca</span>
                    <span class="brand-caption d-block">Perpustakaan digital</span>
                </span>
            </a>
            <section class="surface-card auth-card">
                {{ $slot }}
            </section>
            <p class="text-center text-secondary small mt-3">Tempat setiap cerita menemukan pembacanya.</p>
        </div>
    </main>
</body>
</html>
