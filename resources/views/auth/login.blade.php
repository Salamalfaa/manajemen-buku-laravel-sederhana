<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Perpustakaan digital Anda</p>
        <h1 class="page-title">Selamat datang kembali</h1>
        <p class="auth-copy">Masuk untuk melanjutkan perjalanan membaca.</p>
    </div>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Alamat email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="password">Password</label>
                @if(Route::has('password.request'))
                    <a class="auth-link small mb-2" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" id="remember_me" type="checkbox" name="remember">
            <label class="form-check-label small text-secondary" for="remember_me">Ingat saya</label>
        </div>
        <button class="btn-accent full-width" type="submit"><i class="bi bi-arrow-right-circle"></i> Masuk ke workspace</button>
    </form>

    @if(Route::has('register'))
        <p class="auth-footer">Belum memiliki akun? <a class="auth-link" href="{{ route('register') }}">Daftar sekarang</a></p>
    @endif
</x-guest-layout>
