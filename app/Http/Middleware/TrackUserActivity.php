<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if (! $user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Akun Anda sedang dinonaktifkan. Hubungi administrator untuk mendapatkan bantuan.']);
            }

            $user->newQuery()
                ->whereKey($user->getKey())
                ->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
