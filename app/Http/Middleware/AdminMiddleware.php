<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses ke panel administrator.');
        }

        if ($user->isBanned()) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda sedang dinonaktifkan atau disuspend.']);
        }

        return $next($request);
    }
}
