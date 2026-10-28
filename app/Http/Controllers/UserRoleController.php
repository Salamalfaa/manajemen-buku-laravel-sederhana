<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserRoleController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->paginate(10);
        $lastSessionActivity = DB::table('sessions')
            ->whereIn('user_id', $users->getCollection()->pluck('id'))
            ->select('user_id')
            ->selectRaw('MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');
        $onlineThreshold = now()->subMinutes(5);

        $users->getCollection()->each(function (User $user) use ($lastSessionActivity, $onlineThreshold) {
            $lastActiveAt = $user->last_seen_at;
            $sessionTimestamp = $lastSessionActivity->get($user->id);

            if ($sessionTimestamp !== null) {
                $sessionLastActiveAt = Carbon::createFromTimestamp((int) $sessionTimestamp, config('app.timezone'));

                if ($lastActiveAt === null || $sessionLastActiveAt->greaterThan($lastActiveAt)) {
                    $lastActiveAt = $sessionLastActiveAt;
                }
            }

            $user->setAttribute('last_active_at', $lastActiveAt);
            $user->setAttribute(
                'is_online',
                $sessionTimestamp !== null && (int) $sessionTimestamp >= $onlineThreshold->timestamp
            );
        });

        return view('users.roles', [
            'users' => $users,
            'roles' => ['Admin', 'Dosen', 'Mahasiswa'],
            'pendingRequestCount' => PasswordResetRequest::where('status', PasswordResetRequest::STATUS_PENDING)->count(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        if ($user->is($request->user()) && $validated['role'] !== 'Admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat mencabut role Admin dari akun sendiri.']);
        }

        if ($user->is_active && $user->hasRole('Admin') && $validated['role'] !== 'Admin'
            && User::role('Admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'Aplikasi harus memiliki setidaknya satu Administrator aktif.']);
        }

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function toggleAccount(Request $request, User $user): Response
    {
        $result = DB::transaction(function () use ($request, $user): array {
            User::role('Admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'is_active']);

            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->is($request->user())) {
                return ['active' => $lockedUser->is_active, 'error' => 'Anda tidak dapat menonaktifkan akun sendiri.'];
            }

            if ($lockedUser->is_active && $lockedUser->hasRole('Admin')
                && User::role('Admin')->where('is_active', true)->count() <= 1) {
                return ['active' => $lockedUser->is_active, 'error' => 'Aplikasi harus memiliki setidaknya satu Administrator aktif.'];
            }

            $lockedUser->forceFill(['is_active' => ! $lockedUser->is_active])->save();

            if (! $lockedUser->is_active) {
                DB::table('sessions')->where('user_id', $lockedUser->id)->delete();
            }

            return ['active' => $lockedUser->is_active, 'error' => null];
        });

        if ($result['error'] !== null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['error']], 422);
            }

            return back()->withErrors(['account_status' => $result['error']]);
        }

        $status = $result['active'] ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Akun {$user->name} berhasil {$status}.";

        if ($request->expectsJson()) {
            return response()->json([
                'active' => $result['active'],
                'message' => $message,
            ]);
        }

        return back()->with('account_status', [
            'title' => 'Status akun diperbarui',
            'message' => $message,
            'active' => $result['active'],
        ]);
    }
}
