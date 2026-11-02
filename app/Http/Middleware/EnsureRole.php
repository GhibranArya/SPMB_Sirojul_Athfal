<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Memastikan pengguna login dan memiliki salah satu peran (NFR-04). */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        // Akun dengan sandi sementara (hasil reset panitia) wajib menggantinya lebih dulu.
        if ($user->must_change_password) {
            return redirect('/ganti-sandi');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect($user->isAdmin() ? '/admin/pendaftar' : '/pendaftar/dashboard');
        }

        view()->share('authUser', $user);

        return $next($request);
    }
}
