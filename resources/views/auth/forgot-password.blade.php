<x-guest-layout>
    @section('title', 'Reset password')

    <div class="auth-heading">
        <p class="eyebrow">Keamanan akun</p>
        <h1 class="page-title">Reset password</h1>
        <p class="auth-copy">Masukkan email akun Anda. Permintaan reset akan ditinjau administrator. Jika disetujui, formulir untuk membuat password baru akan tersedia di halaman ini.</p>
    </div>

    @if(session('password_reset_request_tracking'))
        <div
            class="password-reset-status {{ $approved ? 'password-reset-status-approved' : 'password-reset-status-pending' }}"
            role="status"
            aria-live="polite"
            data-password-reset-status
            data-status-url="{{ route('password.request.status') }}"
            data-initial-status="{{ $approved ? 'approved' : 'pending' }}"
        >
            <span class="password-reset-status-icon" data-status-icon><i class="bi {{ $approved ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i></span>
            <span class="password-reset-status-copy">
                <strong data-status-title>{{ $approved ? 'Permintaan Disetujui' : 'Menunggu Administrator' }}</strong>
                <span data-status-message>{{ $approved ? 'Permintaan disetujui. Silakan buat password baru melalui formulir di bawah ini.' : session('status', 'Jika email terdaftar, permintaan reset akan ditinjau administrator. Password baru dapat dibuat setelah disetujui.') }}</span>
            </span>
            <button class="password-reset-dismiss" type="button" aria-label="Tutup notifikasi" data-dismiss-reset-status>
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    <div data-reset-request-form @if($approved) hidden @endif>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">Alamat email</label>
                <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button class="btn-accent full-width" type="submit"><i class="bi bi-send"></i> Kirim permintaan reset</button>
        </form>

        <p class="password-reset-help"><i class="bi bi-shield-lock"></i> Demi keamanan, password baru hanya dapat dibuat setelah permintaan disetujui Administrator.</p>
    </div>

    <form method="POST" action="{{ route('password.approved.store') }}" class="approved-password-form" data-approved-password-form @if(! $approved) hidden @endif>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="new_password">Password baru</label>
            <input class="form-control @error('password') is-invalid @enderror" id="new_password" type="password" name="password" required autocomplete="new-password" @if($approved) autofocus @endif>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="new_password_confirmation">Konfirmasi password baru</label>
            <input class="form-control" id="new_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button class="btn-accent full-width" type="submit"><i class="bi bi-shield-check"></i> Simpan password baru</button>
    </form>
</x-guest-layout>
