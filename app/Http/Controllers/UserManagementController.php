<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordResetRequestRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $requests = PasswordResetRequest::query()
            ->with(['user.roles', 'reviewer'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12);

        return view('users.management', [
            'requests' => $requests,
            'pendingRequestCount' => PasswordResetRequest::where('status', PasswordResetRequest::STATUS_PENDING)->count(),
            'roles' => ['Admin', 'Dosen', 'Mahasiswa'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
            ]);
            $user->assignRole($validated['role']);

            return $user;
        });

        return redirect()
            ->route('users.management.index')
            ->with('success', 'Akun berhasil dibuat dengan password baru dan role yang dipilih.');
    }

    public function approve(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return back()->withErrors(['password_reset_request' => 'Permintaan ini sudah pernah ditinjau.']);
        }

        $user = $passwordResetRequest->user;

        if (! $user || ! $user->is_active || strcasecmp($user->email, $passwordResetRequest->email) !== 0) {
            return back()->withErrors(['password_reset_request' => 'Akun pemohon tidak lagi tersedia. Permintaan tidak dapat disetujui.']);
        }

        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan disetujui. Pemohon dapat membuat password baru pada halaman reset password.');
    }

    public function reject(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return back()->withErrors(['password_reset_request' => 'Permintaan ini sudah pernah ditinjau.']);
        }

        $user = $passwordResetRequest->user;

        if ($user && strcasecmp($user->email, $passwordResetRequest->email) === 0) {
            $user->notify(new PasswordResetRequestRejected($user->name));
        }

        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan reset password ditolak.');
    }

    public function destroy(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $passwordResetRequest->delete();

        return back()->with('success', 'Riwayat permintaan reset password berhasil dihapus. Akun pengguna tidak dihapus.');
    }
}
