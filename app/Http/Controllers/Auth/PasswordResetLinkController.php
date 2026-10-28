<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(Request $request): View
    {
        $resetRequest = null;

        if ($request->session()->get('password_reset_request_tracking')) {
            $resetRequest = PasswordResetRequest::query()
                ->find($request->session()->get('password_reset_request_id'));
        }

        return view('auth.forgot-password', [
            'approved' => $resetRequest?->status === PasswordResetRequest::STATUS_APPROVED,
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = mb_strtolower(trim($validated['email']));
        $user = User::where('email', $email)->where('is_active', true)->first();

        $resetRequest = null;

        if ($user) {
            $resetRequest = DB::transaction(function () use ($user, $email): PasswordResetRequest {
                $pendingRequest = PasswordResetRequest::query()
                    ->where('email', $email)
                    ->where('status', PasswordResetRequest::STATUS_PENDING)
                    ->lockForUpdate()
                    ->first();

                return $pendingRequest ?? PasswordResetRequest::create([
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            });
        }

        $request->session()->put('password_reset_request_tracking', true);
        $request->session()->put('password_reset_request_id', $resetRequest?->id);

        return back()
            ->withInput(['email' => $email])
            ->with('reset_request_submitted', true)
            ->with('status', 'Jika email terdaftar, permintaan reset password akan ditinjau administrator. Jika disetujui, Anda dapat membuat password baru pada halaman ini.');
    }

    public function status(Request $request): JsonResponse
    {
        if (! $request->session()->get('password_reset_request_tracking')) {
            return response()->json(['state' => 'idle']);
        }

        $requestId = $request->session()->get('password_reset_request_id');

        if (! $requestId) {
            return response()->json([
                'state' => 'checking',
                'message' => 'Jika alamat email terdaftar, permintaan sedang menunggu Administrator. Status akan diperbarui di sini jika permintaan dapat ditinjau.',
            ]);
        }

        $resetRequest = PasswordResetRequest::query()->find($requestId);

        if (! $resetRequest) {
            return response()->json([
                'state' => 'unavailable',
                'message' => 'Riwayat permintaan ini tidak lagi tersedia. Silakan ajukan permintaan baru atau hubungi Administrator.',
            ]);
        }

        return match ($resetRequest->status) {
            PasswordResetRequest::STATUS_APPROVED => response()->json([
                'state' => 'approved',
                'message' => 'Permintaan disetujui. Silakan buat password baru melalui formulir di bawah ini.',
            ]),
            PasswordResetRequest::STATUS_REJECTED => response()->json([
                'state' => 'rejected',
                'message' => 'Permintaan reset password ditolak Administrator. Anda dapat menghubungi Admin jika membutuhkan bantuan.',
            ]),
            default => response()->json([
                'state' => 'pending',
                'message' => 'Permintaan reset password Anda sedang menunggu persetujuan Administrator.',
            ]),
        };
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! $request->session()->get('password_reset_request_tracking')) {
            throw ValidationException::withMessages([
                'password' => 'Sesi permintaan reset tidak tersedia. Silakan ajukan permintaan baru.',
            ]);
        }

        $requestId = $request->session()->get('password_reset_request_id');

        if (! $requestId) {
            throw ValidationException::withMessages([
                'password' => 'Permintaan reset tidak dapat diverifikasi. Silakan ajukan permintaan baru.',
            ]);
        }

        DB::transaction(function () use ($request, $requestId, $validated): void {
            $resetRequest = PasswordResetRequest::query()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->first();

            if (! $resetRequest || $resetRequest->status !== PasswordResetRequest::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'password' => 'Permintaan reset belum disetujui atau sudah digunakan.',
                ]);
            }

            $user = $resetRequest->user;

            if (! $user || ! $user->is_active || strcasecmp($user->email, $resetRequest->email) !== 0) {
                throw ValidationException::withMessages([
                    'password' => 'Akun pemohon tidak lagi tersedia atau tidak aktif.',
                ]);
            }

            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ])->save();

            Password::broker()->deleteToken($user);
            event(new PasswordReset($user));

            $resetRequest->update([
                'status' => PasswordResetRequest::STATUS_COMPLETED,
            ]);
        });

        $request->session()->forget([
            'password_reset_request_tracking',
            'password_reset_request_id',
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Password berhasil diperbarui. Silakan masuk menggunakan password baru.');
    }
}
