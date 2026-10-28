@extends('layouts.app')

@section('title', 'Profil & keamanan')
@section('page-heading', 'Profil akun')

@section('content')
    @if(in_array(session('status'), ['profile-updated', 'avatar-deleted', 'avatar-already-removed'], true))
        <div class="profile-toast flash-message" role="status" data-auto-dismiss>
            <span class="profile-toast-icon"><i class="bi bi-check2"></i></span>
            <span class="profile-toast-copy">
                <strong>
                    @if(session('status') === 'profile-updated')
                        Profil berhasil disimpan
                    @elseif(session('status') === 'avatar-deleted')
                        Foto profil berhasil dihapus
                    @else
                        Foto profil sudah tidak tersedia
                    @endif
                </strong>
                <span>
                    @if(session('status') === 'profile-updated')
                        Informasi akun Anda telah diperbarui.
                    @elseif(session('status') === 'avatar-deleted')
                        Foto lama sudah dihapus dari akun Anda.
                    @else
                        Tidak ada foto profil yang perlu dihapus.
                    @endif
                </span>
            </span>
            <button type="button" class="profile-toast-close" data-dismiss-message aria-label="Tutup notifikasi"><i class="bi bi-x-lg"></i></button>
        </div>
    @elseif(session('status') === 'password-updated')
        <div class="profile-toast flash-message" role="status" data-auto-dismiss>
            <span class="profile-toast-icon"><i class="bi bi-check2"></i></span>
            <span class="profile-toast-copy"><strong>Password berhasil diperbarui</strong><span>Keamanan akun Anda telah diperbarui.</span></span>
            <button type="button" class="profile-toast-close" data-dismiss-message aria-label="Tutup notifikasi"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="profile-layout">
        <section class="surface-card profile-card">
            <div class="profile-hero mb-4">
                @if($user->avatar_path)
                    <button class="avatar avatar-preview-trigger" type="button" data-open-avatar aria-label="Lihat foto profil {{ $user->name }}" aria-haspopup="dialog">
                        <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="">
                        <span class="avatar-preview-hint"><i class="bi bi-arrows-angle-expand"></i></span>
                    </button>
                @else
                    <span class="avatar">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                @endif
                <div>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p>
                    <span class="role-pill">{{ $user->getRoleNames()->first() ?: 'Pengguna' }}</span>
                </div>
            </div>
            <h3 class="profile-section-title">Informasi pribadi</h3>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label" for="avatar">Foto profil</label>
                    <input class="form-control @error('avatar') is-invalid @enderror" id="avatar" type="file" name="avatar" accept="image/*">
                    <p class="field-hint">PNG, JPG, WEBP hingga 2 MB.</p>
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($user->avatar_path)
                        <button class="btn-remove-avatar" type="button" data-open-delete-avatar>
                            <i class="bi bi-trash3"></i> Hapus foto profil
                        </button>
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name">Nama pengguna</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Alamat email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Nomor telepon</label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="Contoh: 0812 3456 7890">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn-accent full-width" type="submit"><i class="bi bi-check2"></i> Simpan profil</button>
            </form>
        </section>

        <section class="surface-card profile-card">
            <h2 class="profile-section-title">Ubah password</h2>
            <p class="section-note mb-4">Gunakan password yang kuat dan belum pernah dipakai sebelumnya.</p>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current_password">Password saat ini</label>
                    <input class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                    @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password baru</label>
                    <input class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password" required>
                    @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Konfirmasi password baru</label>
                    <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
                <button class="btn-accent full-width" type="submit"><i class="bi bi-shield-lock"></i> Simpan password</button>
            </form>
            <div class="mt-4 pt-3 border-top">
                <p class="setting-label mb-1">Sesi aman</p>
                <p class="setting-description">Perubahan password meminta password saat ini agar hanya pemilik akun yang dapat memperbaruinya.</p>
            </div>
        </section>
    </div>

    @if($user->avatar_path)
        <dialog class="profile-dialog avatar-dialog" id="avatarPreviewDialog" aria-labelledby="avatarPreviewTitle">
            <div class="avatar-dialog-panel">
                <div class="avatar-dialog-header">
                    <div>
                        <p class="eyebrow">Foto profil</p>
                        <h2 class="section-title" id="avatarPreviewTitle">{{ $user->name }}</h2>
                    </div>
                    <button class="icon-button" type="button" data-close-dialog aria-label="Tutup pratinjau foto"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="avatar-preview-stage">
                    <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Foto profil {{ $user->name }}">
                </div>
                <p class="avatar-dialog-caption"><i class="bi bi-shield-check"></i> Hanya Anda yang dapat mengubah foto profil ini.</p>
            </div>
        </dialog>

        <dialog class="profile-dialog confirm-dialog profile-confirm-dialog" id="deleteAvatarDialog" aria-labelledby="deleteAvatarTitle">
            <div class="profile-confirm-panel">
                <span class="profile-confirm-icon"><i class="bi bi-image"></i></span>
                <h2 class="section-title" id="deleteAvatarTitle">Hapus foto profil?</h2>
                <p class="profile-confirm-copy">Foto ini akan dihapus dari akun Anda. Anda dapat menambahkan foto baru kapan saja.</p>
                <div class="profile-confirm-actions">
                    <button class="btn-quiet" type="button" data-close-dialog>Batal</button>
                    <form method="POST" action="{{ route('profile.avatar.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger-soft" type="submit"><i class="bi bi-trash3"></i> Ya, hapus foto</button>
                    </form>
                </div>
            </div>
        </dialog>
    @endif
@endsection
