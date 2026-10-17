<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang dinonaktifkan Owner langsung kehilangan akses di SEMUA halaman
 * terautentikasi (termasuk /profile & logout-less routes), bukan hanya yang
 * dilindungi middleware role.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'Akun Anda dinonaktifkan.');
            }

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan.']);
        }

        return $next($request);
    }
}
