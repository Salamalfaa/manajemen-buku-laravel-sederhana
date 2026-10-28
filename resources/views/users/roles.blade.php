@extends('layouts.app')

@section('title', 'Pengguna & role')
@section('page-heading', 'Pengguna & role')

@section('content')
    @php($currentUser = auth()->user())
    <section class="dashboard-intro">
        <div>
            <p class="dashboard-subtitle mt-0">Atur role dan aktif/nonaktif akun sesuai tanggung jawab setiap pengguna.</p>
        </div>
        <a class="btn-accent" href="{{ route('users.management.index') }}"><i class="bi bi-person-gear"></i> Akun &amp; permintaan reset @if($pendingRequestCount > 0)<span class="management-link-count">{{ number_format($pendingRequestCount, 0, ',', '.') }}</span>@endif</a>
    </section>

    <section class="surface-card catalog-card">
        <div class="catalog-head">
            <div>
                <h2 class="section-title">Daftar pengguna</h2>
                <p class="section-note">Status diperbarui saat pengguna beraktivitas. Online berarti aktif dalam 5 menit terakhir.</p>
            </div>
            <span class="badge-soft admin-view-badge"><i class="bi bi-shield-check me-1"></i> Hanya Admin</span>
        </div>
        <div class="table-wrap">
            <table class="table book-table user-role-table">
                <thead>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Email</th>
                        <th scope="col">Status akun</th>
                        <th scope="col">Role aktif</th>
                        <th scope="col" class="text-end">Perbarui akses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="user-identity-cell">
                                @if($user->avatar_path)
                                    <button
                                        class="book-initial user-avatar-trigger"
                                        type="button"
                                        data-user-avatar-preview
                                        data-avatar-url="{{ asset('storage/'.$user->avatar_path) }}"
                                        data-avatar-name="{{ $user->name }}"
                                        data-avatar-email="{{ $user->email }}"
                                        aria-label="Perbesar foto profil {{ $user->name }}"
                                        aria-haspopup="dialog"
                                    >
                                        <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="">
                                        <span class="user-avatar-zoom" aria-hidden="true"><i class="bi bi-arrows-angle-expand"></i></span>
                                    </button>
                                @else
                                    <span class="book-initial user-avatar-fallback" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="user-identity-copy">
                                    <span class="book-title">{{ $user->name }}</span>
                                    <span class="account-activity">
                                        <span class="activity-indicator {{ $user->is_online ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
                                        <span class="activity-copy">
                                            <span class="activity-status {{ $user->is_online ? 'text-online' : 'text-offline' }}" data-account-activity data-online="{{ $user->is_online ? 'true' : 'false' }}">
                                                {{ $user->is_active ? ($user->is_online ? 'Online' : 'Offline') : 'Akun nonaktif' }}
                                            </span>
                                            <span class="activity-time">
                                                @if($user->last_active_at)
                                                    Terakhir aktif {{ $user->last_active_at->locale('id')->translatedFormat('H:i:s · l, d F Y') }}
                                                @else
                                                    Belum pernah aktif
                                                @endif
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <div class="account-status-cell">
                                    <span class="account-state-pill {{ $user->is_active ? 'is-account-active' : 'is-account-inactive' }}" data-account-state data-active="{{ $user->is_active ? 'true' : 'false' }}">
                                        <span class="account-state-dot"></span>
                                        <span>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </span>
                                    @if($user->is($currentUser))
                                        <span class="account-toggle-locked" title="Anda tidak dapat menonaktifkan akun sendiri"><i class="bi bi-lock"></i> Akun Anda</span>
                                    @else
                                        <form method="POST" action="{{ route('users.status.toggle', $user) }}" class="account-toggle-form" data-account-toggle-form>
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                class="account-toggle {{ $user->is_active ? 'is-on' : 'is-off' }}"
                                                type="submit"
                                                role="switch"
                                                aria-checked="{{ $user->is_active ? 'true' : 'false' }}"
                                                aria-label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}"
                                                title="{{ $user->is_active ? 'Klik untuk menonaktifkan akun' : 'Klik untuk mengaktifkan akun' }}"
                                                data-account-switch
                                                data-account-name="{{ $user->name }}"
                                            >
                                                <span class="account-toggle-thumb"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge-soft">{{ $user->roles->first()?->name ?? 'Belum ditentukan' }}</span></td>
                            <td>
                                <form class="d-flex justify-content-end gap-2" method="POST" action="{{ route('users.roles.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select role-select" name="role" aria-label="Role untuk {{ $user->name }}" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" @selected($user->hasRole($role))>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn-quiet role-save-button" type="submit" aria-label="Simpan role {{ $user->name }}" title="Simpan perubahan role"><i class="bi bi-check2"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="pagination-footer">
                <span>Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna</span>
                {{ $users->links() }}
            </div>
        @endif
    </section>

    <dialog class="profile-dialog user-avatar-dialog" id="userAvatarPreviewDialog" aria-labelledby="userAvatarPreviewTitle">
        <div class="avatar-dialog-panel">
            <div class="avatar-dialog-header">
                <div>
                    <p class="eyebrow">Foto profil pengguna</p>
                    <h2 class="section-title" id="userAvatarPreviewTitle" data-preview-avatar-name></h2>
                    <p class="user-avatar-preview-email" data-preview-avatar-email></p>
                </div>
                <button class="icon-button" type="button" data-close-dialog aria-label="Tutup pratinjau foto profil"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="avatar-preview-stage">
                <img data-preview-avatar-image alt="">
            </div>
            <p class="avatar-dialog-caption"><i class="bi bi-person-badge"></i> Pratinjau foto profil pengguna</p>
        </div>
    </dialog>
@endsection
