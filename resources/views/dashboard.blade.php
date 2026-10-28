@extends('layouts.app')

@section('title', 'Ringkasan')
@section('page-heading', 'Ringkasan')

@section('header-actions')
    <div class="surface-card clock-widget" id="dateTimeWidget">
        <p class="clock-time" id="clockDisplay">00:00:00</p>
        <p class="clock-date" id="dateDisplay">Memuat waktu WIB...</p>
    </div>
@endsection

@section('content')
    <section class="overview-hero">
        <div class="overview-hero-copy">
            <span class="overview-kicker"><i class="bi bi-stars"></i> Ruang baca yang tertata</span>
            <h2>Halo, {{ auth()->user()->name }}.<br><span>Semua cerita, satu ruang.</span></h2>
            <p>Kelola koleksi, kenali aktivitas pengguna, dan temukan gambaran perpustakaan Anda dalam satu tampilan.</p>
            <a class="btn-accent overview-cta" href="{{ route('buku.index') }}"><i class="bi bi-journal-bookmark"></i> Jelajahi koleksi <i class="bi bi-arrow-up-right ms-1"></i></a>
        </div>
        <div class="overview-hero-art" aria-hidden="true">
            <span class="hero-orbit hero-orbit-one"></span>
            <span class="hero-orbit hero-orbit-two"></span>
            <span class="hero-book hero-book-back"></span>
            <span class="hero-book hero-book-front"><i class="bi bi-book-half"></i></span>
            <span class="hero-spark hero-spark-one">✦</span>
            <span class="hero-spark hero-spark-two">✧</span>
        </div>
    </section>

    <section class="overview-stats" aria-label="Angka ringkasan perpustakaan">
        <article class="surface-card overview-stat-card">
            <span class="overview-stat-icon stat-icon-blue"><i class="bi bi-journal-richtext"></i></span>
            <div><p class="stat-label">Judul dalam koleksi</p><p class="overview-stat-value">{{ number_format($totalBooks, 0, ',', '.') }}</p><p class="overview-stat-foot">buku terdaftar</p></div>
            <span class="overview-stat-decoration"><i class="bi bi-journal-bookmark"></i></span>
        </article>
        <article class="surface-card overview-stat-card">
            <span class="overview-stat-icon stat-icon-purple"><i class="bi bi-people"></i></span>
            <div><p class="stat-label">Pengguna terdaftar</p><p class="overview-stat-value">{{ number_format($totalUsers, 0, ',', '.') }}</p><p class="overview-stat-foot">di 3 kelompok akses</p></div>
            <span class="overview-stat-decoration"><i class="bi bi-person-check"></i></span>
        </article>
        <article class="surface-card overview-stat-card">
            <span class="overview-stat-icon stat-icon-green"><i class="bi bi-pen"></i></span>
            <div><p class="stat-label">Penulis berbeda</p><p class="overview-stat-value">{{ number_format($totalAuthors, 0, ',', '.') }}</p><p class="overview-stat-foot">nama dalam katalog</p></div>
            <span class="overview-stat-decoration"><i class="bi bi-feather"></i></span>
        </article>
        <article class="surface-card overview-stat-card">
            <span class="overview-stat-icon stat-icon-orange"><i class="bi bi-calendar2-heart"></i></span>
            <div><p class="stat-label">Terbitan terbaru</p><p class="overview-stat-value">{{ $latestPublicationYear ?: '—' }}</p><p class="overview-stat-foot">{{ $latestPublicationYear ? 'tahun terbit terakhir' : 'belum ada data' }}</p></div>
            <span class="overview-stat-decoration"><i class="bi bi-stars"></i></span>
        </article>
    </section>

    <section class="analytics-grid" aria-label="Analitik koleksi dan akun">
        <article class="surface-card analytics-card year-analytics">
            <header class="analytics-card-header">
                <div>
                    <span class="analytics-eyebrow"><i class="bi bi-bar-chart-line"></i> Perkembangan koleksi</span>
                    <h2 class="section-title">Buku menurut tahun terbit</h2>
                    <p class="section-note">Lima tahun terbit terbaru dalam koleksi saat ini.</p>
                </div>
                <span class="analytics-header-icon"><i class="bi bi-graph-up-arrow"></i></span>
            </header>

            @if($yearDistribution->isNotEmpty())
                <div class="year-chart" role="img" aria-label="Grafik jumlah buku dan persentase berdasarkan tahun terbit">
                    @foreach($yearDistribution as $year)
                        <div class="year-chart-row">
                            <span class="year-chart-label">{{ $year['year'] }}</span>
                            <div class="year-chart-track" role="progressbar" aria-label="Tahun {{ $year['year'] }}" aria-valuenow="{{ round($year['percentage']) }}" aria-valuemin="0" aria-valuemax="100">
                                <span class="year-chart-fill" style="--bar-width: {{ max($year['percentage'], $year['count'] > 0 ? 2 : 0) }}%; --bar-delay: {{ $loop->index * 70 }}ms"></span>
                            </div>
                            <span class="year-chart-count">{{ number_format($year['count'], 0, ',', '.') }} <small>buku</small></span>
                            <span class="year-chart-percent">{{ number_format($year['percentage'], 1, ',', '.') }}%</span>
                        </div>
                    @endforeach
                </div>
                <p class="analytics-explanation"><i class="bi bi-info-circle"></i>
                    @if($totalBooks > 0)
                        Setiap persentase menunjukkan bagian tahun tersebut dari {{ number_format($totalBooks, 0, ',', '.') }} buku yang tercatat. Angka berubah ketika koleksi bertambah, diubah, atau dihapus.
                    @else
                        Tambahkan buku pertama untuk mulai melihat persebaran tahun terbit di sini.
                    @endif
                </p>
            @else
                <div class="chart-empty-state"><i class="bi bi-bar-chart"></i><span>Grafik akan muncul setelah buku ditambahkan.</span></div>
            @endif
        </article>

        <article class="surface-card analytics-card role-analytics">
            <header class="analytics-card-header">
                <div>
                    <span class="analytics-eyebrow"><i class="bi bi-people"></i> Komunitas pembaca</span>
                    <h2 class="section-title">Akun berdasarkan role</h2>
                    <p class="section-note">Perbandingan akun yang terdaftar saat ini.</p>
                </div>
                <span class="analytics-header-icon"><i class="bi bi-pie-chart"></i></span>
            </header>

            <div class="role-chart-layout">
                <div class="role-chart-ring" role="img" aria-label="Grafik donat distribusi {{ $totalUsers }} akun berdasarkan role" style="--role-chart: {{ $roleChart }}">
                    <div class="role-chart-center">
                        <strong>{{ number_format($totalUsers, 0, ',', '.') }}</strong>
                        <span>total akun</span>
                    </div>
                </div>
                <ul class="role-legend" aria-label="Rincian akun per role">
                    @foreach($roleDistribution as $role)
                        <li class="role-legend-item">
                            <span class="role-legend-dot" style="--role-color: {{ $role['color'] }}" aria-hidden="true"></span>
                            <span class="role-legend-copy">
                                <strong>{{ $role['label'] }}</strong>
                                <small>{{ $role['description'] }}</small>
                            </span>
                            <span class="role-legend-stats">
                                <strong>{{ number_format($role['count'], 0, ',', '.') }}</strong>
                                <small>{{ number_format($role['percentage'], 1, ',', '.') }}%</small>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <p class="analytics-explanation"><i class="bi bi-arrow-repeat"></i>
                @if($totalUsers > 0 && $largestRole && $largestRole['count'] > 0)
                    {{ $largestRole['label'] }} merupakan kelompok terbesar dengan {{ number_format($largestRole['count'], 0, ',', '.') }} akun ({{ number_format($largestRole['percentage'], 1, ',', '.') }}%). Grafik diperbarui otomatis dari data akun dan role.
                @else
                    Belum ada akun yang tercatat. Grafik akan menyesuaikan setelah akun dibuat.
                @endif
            </p>
            @can('kelola role pengguna')
                <a class="analytics-manage-link" href="{{ route('users.roles.index') }}">Kelola akun &amp; role <i class="bi bi-arrow-right"></i></a>
            @endcan
        </article>
    </section>

    <section class="overview-bottom-grid">
        <article class="surface-card highlights-card">
            <header class="overview-section-header">
                <div>
                    <span class="analytics-eyebrow"><i class="bi bi-stars"></i> Dibuat untuk membaca lebih baik</span>
                    <h2 class="section-title">Yang menarik dari Ruang Baca</h2>
                </div>
            </header>
            <div class="highlight-list">
                <div class="highlight-item">
                    <span class="highlight-icon highlight-violet"><i class="bi bi-shield-lock"></i></span>
                    <div><h3>Akses teratur, data terlindungi</h3><p>Hak Admin, Dosen, dan Mahasiswa diterapkan pada setiap aksi, termasuk permintaan langsung ke server.</p></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon highlight-blue"><i class="bi bi-sliders2"></i></span>
                    <div><h3>Ruang kerja yang terasa personal</h3><p>Atur tema, warna, font, ukuran antarmuka, dan widget jam WIB sesuai kenyamanan Anda.</p></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon highlight-green"><i class="bi bi-search-heart"></i></span>
                    <div><h3>Koleksi mudah ditemukan</h3><p>Cari judul, penulis, atau penerbit, lalu sortir dan jelajahi buku lewat tampilan kartu yang responsif.</p></div>
                </div>
            </div>
        </article>

        <aside class="surface-card creator-card">
            <span class="creator-orbit creator-orbit-one" aria-hidden="true"></span>
            <span class="creator-orbit creator-orbit-two" aria-hidden="true"></span>
            <span class="creator-avatar"><i class="bi bi-code-slash"></i></span>
            <span class="creator-eyebrow">Dibangun dengan sepenuh hati</span>
            <h2>Di balik Ruang Baca</h2>
            <p>Aplikasi ini dirancang dan dikembangkan oleh</p>
            <strong class="creator-name">jamilalfaris</strong>
            <a class="creator-github-link" href="https://github.com/Salamalfaa/" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-github" aria-hidden="true"></i>
                <span>Kunjungi GitHub</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
            <p class="creator-credit">Laravel · Breeze · Spatie Permission</p>
        </aside>
    </section>
@endsection
