@extends('layouts.app')

@section('title', 'Akun & reset password')
@section('page-heading', 'Akun & reset password')

@section('content')
    <section class="management-intro">
        <div>
            <span class="analytics-eyebrow"><i class="bi bi-shield-check"></i> Pusat administrasi</span>
            <h2>Kelola akses dengan aman.</h2>
            <p>Buat akun dengan role yang tepat dan tinjau permintaan reset password sebelum tautan dikirim.</p>
        </div>
        <a class="btn-quiet" href="{{ route('users.roles.index') }}"><i class="bi bi-arrow-left"></i> Kembali ke pengguna &amp; role</a>
    </section>

    <section class="management-grid">
        <article class="surface-card management-create-card">
            <header class="management-card-header">
                <span class="management-icon management-icon-purple"><i class="bi bi-person-add"></i></span>
                <div>
                    <span class="analytics-eyebrow">Akun baru</span>
                    <h2 class="section-title">Buat akun pengguna</h2>
                    <p class="section-note">Tentukan password awal dan role pengguna sebelum akun dibuat.</p>
                </div>
            </header>

            <form method="POST" action="{{ route('users.management.store') }}" class="management-account-form">
                @csrf
                <div>
                    <label class="form-label" for="accountName">Nama lengkap</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="accountName" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="accountEmail">Alamat email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="accountEmail" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="accountPhone">Nomor telepon <span class="management-optional">opsional</span></label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="accountPhone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="30">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="accountPassword">Password baru</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="accountPassword" type="password" name="password" autocomplete="new-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="accountPasswordConfirmation">Konfirmasi password baru</label>
                    <input class="form-control" id="accountPasswordConfirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
                <div>
                    <label class="form-label" for="accountRole">Role akun</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="accountRole" name="role" required>
                        <option value="" disabled @selected(old('role') === null)>Pilih role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role === 'Admin' ? 'Administrator' : $role }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn-accent full-width" type="submit"><i class="bi bi-person-check"></i> Buat akun</button>
            </form>
        </article>

        <article class="surface-card management-request-card">
            <header class="management-card-header management-request-header">
                <span class="management-icon management-icon-blue"><i class="bi bi-key"></i></span>
                <div class="management-request-heading">
                    <span class="analytics-eyebrow">Pemulihan akun</span>
                    <h2 class="section-title">Permintaan reset password</h2>
                    <p class="section-note">Tautan reset hanya dikirim setelah permintaan disetujui Admin.</p>
                </div>
                <span class="management-pending-count" aria-label="{{ $pendingRequestCount }} permintaan menunggu">{{ number_format($pendingRequestCount, 0, ',', '.') }}</span>
            </header>

            @if($requests->isNotEmpty())
                <div class="management-request-list">
                    @foreach($requests as $resetRequest)
                        <article class="reset-request-item {{ $resetRequest->status === 'pending' ? 'is-pending' : '' }}">
                            <div class="reset-request-main">
                                <span class="reset-request-avatar"><i class="bi bi-person"></i></span>
                                <div class="reset-request-copy">
                                    <div class="reset-request-title-row">
                                        <h3>{{ $resetRequest->user?->name ?? 'Akun tidak tersedia' }}</h3>
                                        @if($role = $resetRequest->user?->getRoleNames()->first())
                                            <span class="reset-request-role">{{ $role === 'Admin' ? 'Administrator' : $role }}</span>
                                        @endif
                                        <span class="request-status-pill request-status-{{ $resetRequest->status }}">
                                            @if($resetRequest->status === 'pending')
                                                <i class="bi bi-clock-history"></i> Menunggu
                                            @elseif($resetRequest->status === 'approved')
                                                <i class="bi bi-check2-circle"></i> Disetujui
                                            @elseif($resetRequest->status === 'completed')
                                                <i class="bi bi-shield-check"></i> Password diperbarui
                                            @else
                                                <i class="bi bi-x-circle"></i> Ditolak
                                            @endif
                                        </span>
                                    </div>
                                    <p>{{ $resetRequest->email }}</p>
                                    <small>
                                        Diajukan {{ $resetRequest->created_at->locale('id')->translatedFormat('d M Y · H:i') }}
                                        @if($resetRequest->reviewer)
                                            · Ditinjau {{ $resetRequest->reviewer->name }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="reset-request-actions">
                                @if($resetRequest->status === 'pending')
                                    <form method="POST" action="{{ route('users.password-reset-requests.approve', $resetRequest) }}">
                                        @csrf
                                        <button class="btn-request-approve" type="submit"><i class="bi bi-check-lg"></i> Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('users.password-reset-requests.reject', $resetRequest) }}">
                                        @csrf
                                        <button class="btn-request-reject" type="submit"><i class="bi bi-x-lg"></i> Tolak</button>
                                    </form>
                                @endif
                                <form
                                    class="reset-request-delete-form"
                                    method="POST"
                                    action="{{ route('users.password-reset-requests.destroy', $resetRequest) }}"
                                    data-request-delete-form
                                    data-request-name="{{ $resetRequest->user?->name ?? 'Akun tidak tersedia' }}"
                                    data-request-email="{{ $resetRequest->email }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="btn-request-delete"
                                        type="submit"
                                        aria-label="Hapus riwayat permintaan reset {{ $resetRequest->user?->name ?? $resetRequest->email }}"
                                        title="Hapus riwayat permintaan"
                                    ><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($requests->hasPages())
                    <div class="pagination-footer management-pagination">
                        <span>{{ $requests->firstItem() }}–{{ $requests->lastItem() }} dari {{ $requests->total() }} permintaan</span>
                        {{ $requests->links() }}
                    </div>
                @endif
            @else
                <div class="management-empty-state">
                    <span class="management-empty-icon"><i class="bi bi-inbox"></i></span>
                    <h3>Belum ada permintaan</h3>
                    <p>Permintaan reset password yang dikirim dari halaman lupa password akan muncul di sini.</p>
                </div>
            @endif
        </article>
    </section>

    <dialog class="confirm-dialog request-delete-dialog" id="deletePasswordResetRequestDialog" aria-labelledby="deletePasswordResetRequestTitle">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="request-delete-icon"><i class="bi bi-trash3"></i></div>
                    <span class="analytics-eyebrow">Konfirmasi penghapusan</span>
                    <h2 class="section-title mb-2" id="deletePasswordResetRequestTitle">Hapus riwayat permintaan?</h2>
                    <p class="request-delete-description">
                        Riwayat permintaan reset password milik
                        <strong data-delete-request-name>pengguna</strong>
                        (<span data-delete-request-email>email</span>) akan dihapus permanen.
                        <strong>Akun pengguna dan password-nya tidak akan dihapus atau diubah.</strong>
                    </p>
                    <div class="logout-confirm-actions request-delete-actions">
                        <button class="btn-quiet" type="button" data-cancel-request-delete>Tidak, kembali</button>
                        <button class="btn-danger-soft" type="button" data-confirm-request-delete>Ya, hapus riwayat</button>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
@endsection
