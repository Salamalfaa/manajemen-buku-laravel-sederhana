<x-guest-layout>
    @section('title', 'Buat password baru')

    <div class="auth-heading">
        <p class="eyebrow">Keamanan akun</p>
        <h1 class="page-title">Buat password baru</h1>
        <p class="auth-copy">Permintaan Anda telah disetujui. Buat password baru untuk melanjutkan ke akun.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="form-label" for="email">Alamat email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password baru</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Konfirmasi password baru</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button class="btn-accent full-width" type="submit"><i class="bi bi-shield-check"></i> Simpan password baru</button>
    </form>
</x-guest-layout>
