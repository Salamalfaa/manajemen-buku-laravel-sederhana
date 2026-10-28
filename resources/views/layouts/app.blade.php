<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') · Ruang Baca</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    @php($currentUser = auth()->user())
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-bookshelf"></i></span>
                <span>
                    <span class="brand-name d-block">Ruang Baca</span>
                    <span class="brand-caption d-block">Perpustakaan digital</span>
                </span>
            </a>

            <p class="nav-caption">Workspace</p>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-dashboard-nav href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i><span>Ringkasan</span>
                </a>
                <a class="side-link {{ request()->routeIs('buku.*') ? 'active' : '' }}" data-collection-nav href="{{ route('buku.index') }}">
                    <i class="bi bi-journal-bookmark"></i><span>Koleksi buku</span>
                </a>
                @can('kelola role pengguna')
                    <a class="side-link {{ request()->routeIs('users.roles.*', 'users.management.*', 'users.password-reset-requests.*') ? 'active' : '' }}" data-users-nav href="{{ route('users.roles.index') }}">
                        <i class="bi bi-people"></i><span>Pengguna &amp; role</span>
                    </a>
                @endcan
            </nav>

            <div class="sidebar-spacer"></div>
            <p class="nav-caption">Akun</p>
            <div class="profile-mini {{ request()->routeIs('profile.*') ? 'profile-mini-active' : '' }}">
                <a class="avatar" href="{{ route('profile.edit') }}" aria-label="Buka profil">
                    @if($currentUser->avatar_path)
                        <img src="{{ asset('storage/'.$currentUser->avatar_path) }}" alt="Foto {{ $currentUser->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($currentUser->name, 0, 1)) }}
                    @endif
                </a>
                <a class="profile-mini-copy" href="{{ route('profile.edit') }}">
                    <p class="profile-mini-name">{{ $currentUser->name }}</p>
                    <p class="profile-mini-meta">{{ $currentUser->phone ?: $currentUser->getRoleNames()->first() }}</p>
                </a>
                <button class="icon-button" type="button" data-open-settings aria-controls="settingsDrawer" aria-expanded="false" aria-label="Buka pengaturan tampilan">
                    <i class="bi bi-gear"></i>
                </button>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Ruang kerja pribadi</p>
                    <h1 class="page-title">@yield('page-heading', 'Selamat datang')</h1>
                </div>
                <div class="topbar-actions">
                    @yield('header-actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success flash-message d-flex align-items-center gap-2 mb-3" role="status" data-auto-dismiss>
                    <i class="bi bi-check-circle-fill text-success"></i><span>{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any() && !request()->routeIs('profile.*'))
                @unless($errors->has('account_status'))
                <div class="alert alert-danger mb-3" role="alert">
                    <strong>Periksa kembali data yang dimasukkan.</strong>
                    <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endunless
            @endif

            @yield('content')
        </main>
    </div>

    @if($errors->has('account_status'))
        <div class="account-toast account-toast-neutral" role="alert" aria-live="assertive" data-auto-dismiss>
            <span class="account-toast-icon"><i class="bi bi-exclamation-circle-fill"></i></span>
            <span class="account-toast-copy">
                <strong>Status akun tidak berubah</strong>
                <span>{{ $errors->first('account_status') }}</span>
            </span>
            <button type="button" class="account-toast-close" data-dismiss-message aria-label="Tutup notifikasi"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    @if(session('account_status'))
        <div class="account-toast {{ session('account_status.active') ? 'account-toast-success' : 'account-toast-neutral' }}" role="status" aria-live="polite" data-auto-dismiss>
            <span class="account-toast-icon"><i class="bi {{ session('account_status.active') ? 'bi-person-check-fill' : 'bi-person-dash-fill' }}"></i></span>
            <span class="account-toast-copy">
                <strong>{{ session('account_status.title') }}</strong>
                <span>{{ session('account_status.message') }}</span>
            </span>
            <button type="button" class="account-toast-close" data-dismiss-message aria-label="Tutup notifikasi"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <dialog class="settings-dialog" id="settingsDrawer" aria-labelledby="settingsDrawerLabel">
        <div class="offcanvas-header">
            <div>
                <p class="eyebrow">Preferensi workspace</p>
                <h2 class="section-title" id="settingsDrawerLabel">Pengaturan tampilan</h2>
            </div>
            <button type="button" class="icon-button" data-close-settings aria-label="Tutup pengaturan"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="offcanvas-body">
            <section class="setting-group">
                <h3 class="setting-heading">Tampilan</h3>
                <div class="setting-row">
                    <div><p class="setting-label">Tampilan</p><p class="setting-description">Pilih mode terang atau gelap</p></div>
                    <label class="apple-switch" for="themeToggle">
                        <input class="apple-switch-input" type="checkbox" id="themeToggle" role="switch" aria-label="Aktifkan mode gelap">
                        <span class="apple-switch-track" aria-hidden="true">
                            <i class="bi bi-sun-fill"></i>
                            <i class="bi bi-moon-stars-fill"></i>
                            <span class="apple-switch-thumb"></span>
                        </span>
                    </label>
                </div>
                <div class="setting-row">
                    <label class="setting-label" for="accentPicker">Warna aksen</label>
                    <input class="form-control form-control-color" type="color" id="accentPicker" value="#5279f7" title="Pilih warna aksen">
                </div>
                <div class="setting-row">
                    <label class="setting-label" for="fontPicker">Font antarmuka</label>
                    <select class="form-select" id="fontPicker">
                        <option value="inter">Inter · modern</option>
                        <option value="system">System · native</option>
                        <option value="serif">Serif · editorial</option>
                        <option value="mono">Monospace</option>
                    </select>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="setting-label" for="scaleSlider">Ukuran antarmuka</label>
                        <span class="setting-value" id="scaleValue">100%</span>
                    </div>
                    <input class="setting-range" type="range" id="scaleSlider" min="75" max="130" step="1" value="100" aria-label="Skala ukuran antarmuka">
                </div>
            </section>

            <section class="setting-group">
                <h3 class="setting-heading">Widget waktu WIB</h3>
                <div class="setting-row">
                    <div><p class="setting-label">Tampilkan jam</p><p class="setting-description">Jam real-time zona Asia/Jakarta</p></div>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" id="widgetToggle" role="switch" checked aria-label="Tampilkan widget jam"></div>
                </div>
                <div class="setting-row">
                    <label class="setting-label" for="clockFontPicker">Font jam</label>
                    <select class="form-select" id="clockFontPicker">
                        <option value="mono">Monospace</option>
                        <option value="inter">Inter</option>
                        <option value="serif">Serif</option>
                    </select>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="setting-label" for="clockSizeSlider">Ukuran jam</label>
                        <span class="setting-value" id="clockSizeValue">34px</span>
                    </div>
                    <input class="setting-range" type="range" id="clockSizeSlider" min="24" max="52" step="1" value="34" aria-label="Ukuran font jam">
                </div>
            </section>

            <section class="setting-group">
                <h3 class="setting-heading">Akun</h3>
                <a class="settings-account-button" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> Profil &amp; keamanan</a>
                <button class="settings-account-button settings-logout-button" type="button" data-confirm-logout>
                    <i class="bi bi-box-arrow-right me-1"></i> Keluar dari akun
                </button>
            </section>
        </div>
        <footer class="settings-footer">
            <a href="https://github.com/Salamalfaa/" target="_blank" rel="noopener noreferrer" aria-label="Powered By jamilalfaris, buka GitHub">
                <i class="bi bi-github" aria-hidden="true"></i>
                <span>Powered By <strong>jamilalfaris</strong></span>
                <i class="bi bi-arrow-up-right settings-footer-arrow" aria-hidden="true"></i>
            </a>
        </footer>
    </dialog>

    <dialog class="confirm-dialog" id="logoutConfirmModal" aria-labelledby="logoutConfirmTitle">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body p-4 text-center">
                    <div class="empty-icon"><i class="bi bi-box-arrow-right"></i></div>
                    <h2 class="section-title mb-2" id="logoutConfirmTitle">Keluar sekarang?</h2>
                    <p class="text-secondary small mb-4">Sesi Anda akan diakhiri dan Anda perlu masuk kembali untuk melanjutkan.</p>
                    <div class="logout-confirm-actions">
                        <button class="btn-quiet flex-fill" type="button" data-close-confirmation>Batal</button>
                        <form method="POST" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button class="btn-danger-soft" type="submit" data-logout-submit>
                                <span class="logout-submit-spinner" aria-hidden="true" hidden></span>
                                <i class="bi bi-box-arrow-right" data-logout-icon aria-hidden="true"></i>
                                <span data-logout-label>Ya, keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <nav class="mobile-bottom-nav" aria-label="Navigasi bawah">
        <span class="mobile-nav-indicator" aria-hidden="true"></span>
        <a class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-mobile-nav-key="dashboard" data-dashboard-nav href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Ringkasan</span>
        </a>
        <a class="mobile-nav-item {{ request()->routeIs('buku.*') ? 'active' : '' }}" data-mobile-nav-key="collection" data-collection-nav href="{{ route('buku.index') }}">
            <i class="bi bi-journal-bookmark" aria-hidden="true"></i><span>Koleksi</span>
        </a>
        @can('kelola role pengguna')
            <a class="mobile-nav-item {{ request()->routeIs('users.roles.*', 'users.management.*', 'users.password-reset-requests.*') ? 'active' : '' }}" data-users-nav data-mobile-nav-key="users" href="{{ route('users.roles.index') }}">
                <i class="bi bi-people" aria-hidden="true"></i><span>Pengguna</span>
            </a>
        @endcan
        <a class="mobile-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-mobile-nav-key="account" href="{{ route('profile.edit') }}">
            <i class="bi bi-person-circle" aria-hidden="true"></i><span>Akun</span>
        </a>
        <button class="mobile-nav-item" type="button" data-mobile-nav-key="settings" data-open-settings aria-controls="settingsDrawer" aria-expanded="false" aria-label="Buka pengaturan tampilan">
            <i class="bi bi-gear" aria-hidden="true"></i><span>Pengaturan</span>
        </button>
    </nav>
    @stack('scripts')
</body>
</html>
