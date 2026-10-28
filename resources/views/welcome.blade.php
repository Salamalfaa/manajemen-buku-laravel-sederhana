<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ruang Baca adalah aplikasi manajemen buku dengan autentikasi dan pengelolaan akses berbasis role.">
    <meta name="theme-color" content="#f5f6fa">
    <title>Ruang Baca · Manajemen buku</title>
    @vite(['resources/js/app.js'])
</head>
<body class="landing-body">
    <main class="landing-shell">
        <nav class="landing-nav" aria-label="Navigasi utama">
            <a class="brand landing-brand" href="{{ url('/') }}">
                <span class="brand-mark"><i class="bi bi-bookshelf"></i></span>
                <span>
                    <span class="brand-name d-block">Ruang Baca</span>
                    <span class="brand-caption d-block">Manajemen perpustakaan</span>
                </span>
            </a>
            <div class="landing-nav-actions">
                <a class="landing-text-link" href="#tentang">Tentang aplikasi</a>
                <a class="btn-quiet landing-login" href="{{ route('login') }}">Masuk</a>
                <a class="btn-accent landing-register" href="{{ route('register') }}">Buat akun <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </nav>

        <section class="landing-hero">
            <div class="landing-copy">
                <span class="landing-kicker"><i class="bi bi-stars"></i> Perpustakaan digital yang lebih tertata</span>
                <h1>Manajemen buku,<br><span>dalam satu ruang.</span></h1>
                <p class="landing-description">
                    Temukan, kelola, dan rawat koleksi bacaan dengan lebih mudah.
                    Ruang Baca menyatukan katalog buku dan pengaturan akses dalam pengalaman yang nyaman.
                </p>
                <div class="landing-actions">
                    <a class="btn-accent landing-primary" href="{{ route('login') }}">Jelajahi koleksi <i class="bi bi-arrow-right"></i></a>
                    <a class="landing-secondary" href="#tentang"><span class="landing-play"><i class="bi bi-arrow-down"></i></span> Kenali Ruang Baca</a>
                </div>
                <div class="landing-trust">
                    <div class="landing-avatars" aria-hidden="true">
                        <span><i class="bi bi-person-fill"></i></span>
                        <span><i class="bi bi-mortarboard-fill"></i></span>
                        <span><i class="bi bi-book-half"></i></span>
                    </div>
                    <p><strong>Satu katalog, tiga peran.</strong><br>Hak akses tertata untuk Admin, Dosen, dan Mahasiswa.</p>
                </div>
            </div>

            <div class="landing-visual">
                <div class="landing-photo-frame">
                    <img
                        src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&amp;fit=crop&amp;w=1400&amp;q=85"
                        alt="Suasana perpustakaan yang hangat dengan rak buku tinggi"
                        fetchpriority="high"
                    >
                    <div class="landing-photo-shade"></div>
                    <div class="landing-photo-caption">
                        <span class="landing-caption-icon"><i class="bi bi-book"></i></span>
                        <span><strong>Temukan cerita berikutnya</strong><small>Jelajahi koleksi pilihan Anda</small></span>
                        <i class="bi bi-arrow-up-right landing-caption-arrow"></i>
                    </div>
                </div>
                <div class="landing-float-card landing-float-top">
                    <span class="landing-float-icon"><i class="bi bi-shield-check"></i></span>
                    <span><strong>Akses terjaga</strong><small>Berbasis role &amp; permission</small></span>
                </div>
                <div class="landing-float-card landing-float-bottom">
                    <span class="landing-live-dot"></span>
                    <span><strong>Koleksi terorganisir</strong><small>Cari buku dengan cepat</small></span>
                </div>
                <span class="landing-orbit landing-orbit-one"></span>
                <span class="landing-orbit landing-orbit-two"></span>
            </div>
        </section>

        <section class="landing-features" id="tentang" aria-labelledby="features-title">
            <div class="landing-section-heading">
                <div>
                    <p class="eyebrow">Dibuat untuk kegiatan belajar</p>
                    <h2 id="features-title">Semua yang dibutuhkan, tanpa terasa rumit.</h2>
                </div>
                <p>Pengelolaan buku yang mudah digunakan, dengan akses yang tetap aman untuk setiap pengguna.</p>
            </div>
            <div class="landing-feature-grid">
                <article class="landing-feature-card">
                    <span class="landing-feature-icon feature-violet"><i class="bi bi-journal-richtext"></i></span>
                    <h3>Katalog yang praktis</h3>
                    <p>Lihat dan cari koleksi berdasarkan judul, penulis, atau penerbit dari satu halaman.</p>
                </article>
                <article class="landing-feature-card">
                    <span class="landing-feature-icon feature-blue"><i class="bi bi-person-lock"></i></span>
                    <h3>Akses sesuai peran</h3>
                    <p>Admin, Dosen, dan Mahasiswa memiliki hak yang sesuai kebutuhan masing-masing.</p>
                </article>
                <article class="landing-feature-card">
                    <span class="landing-feature-icon feature-green"><i class="bi bi-shield-check"></i></span>
                    <h3>Keamanan berlapis</h3>
                    <p>Autentikasi dan pemeriksaan permission melindungi fitur, bukan sekadar menyembunyikan tombol.</p>
                </article>
            </div>
        </section>

        <footer class="landing-footer">
            <a class="brand landing-brand" href="{{ url('/') }}">
                <span class="brand-mark"><i class="bi bi-bookshelf"></i></span>
                <span class="brand-name">Ruang Baca</span>
            </a>
            <p>Ruang yang nyaman untuk mengelola pengetahuan.</p>
            <a href="{{ route('login') }}">Masuk ke perpustakaan <i class="bi bi-arrow-right"></i></a>
        </footer>
    </main>
</body>
</html>
