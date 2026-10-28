<x-guest-layout>
    <div class="auth-heading">
        <p class="eyebrow">Mulai perjalanan membaca</p>
        <h1 class="page-title">Buat akun baru</h1>
        <p class="auth-copy">Daftar dan jelajahi koleksi buku pilihan.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Nama lengkap</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama Anda">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Alamat email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Buat password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Konfirmasi password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password">
        </div>
        <p class="field-hint mb-3"><i class="bi bi-info-circle me-1"></i>Akun baru mendapatkan akses Mahasiswa.</p>
        <button class="btn-accent full-width" type="submit"><i class="bi bi-person-plus"></i> Buat akun</button>
    </form>

    <p class="auth-footer">Sudah memiliki akun? <a class="auth-link" href="{{ route('login') }}">Masuk</a></p>
</x-guest-layout>
