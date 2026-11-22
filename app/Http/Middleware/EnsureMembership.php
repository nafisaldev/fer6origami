<?php

namespace App\Http\Middleware;

use App\Services\MembershipService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMembership
{
    public function __construct(private readonly MembershipService $membership)
    {
    }

    public function handle(Request $request, Closure $next, string $plan = 'premium'): Response
    {
        if (! $request->user()) {
            return redirect()->route('login')->with('error', 'Masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        if (! $this->membership->hasAtLeast($request->user(), $plan)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Paket ' . strtoupper($plan) . ' diperlukan.'], 403);
            }

            return redirect()->route('membership.pricing')
                ->with('error', 'Halaman ini memerlukan paket ' . strtoupper($plan) . '.');
        }

        return $next($request);
    }
}
