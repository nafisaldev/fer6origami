<?php

namespace App\Http\Middleware;

use App\Models\WebsiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckSiteMaintenance
{
    /**
     * Handled within 'web' and 'api' middleware groups where auth is available.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('login', 'logout', 'admin', 'admin/*', 'payments/midtrans/webhook')) {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        try {
            $enabled = $this->isEnabled();

            if ($enabled) {
                $message = $this->message();

                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'status' => 'maintenance',
                        'message' => $message,
                    ], 503);
                }

                return response()->view('errors.maintenance', ['message' => $message], 503);
            }
        } catch (Throwable) {
            // Fail open when the settings table is unavailable.
        }

        return $next($request);
    }

    private function isEnabled(): bool
    {
        $primary = WebsiteSetting::get('maintenance_mode', null);
        $legacy = WebsiteSetting::get('site_maintenance', '0');

        return filter_var($primary, FILTER_VALIDATE_BOOLEAN)
            || filter_var($legacy, FILTER_VALIDATE_BOOLEAN);
    }

    private function message(): string
    {
        $default = 'AnimeStream is currently undergoing scheduled maintenance. We will be back online shortly.';
        $legacyEnabled = filter_var(WebsiteSetting::get('site_maintenance', '0'), FILTER_VALIDATE_BOOLEAN);

        $message = $legacyEnabled
            ? WebsiteSetting::get('site_maintenance_message', null)
            : WebsiteSetting::get('maintenance_message', null);

        if (trim((string) $message) === '') {
            $message = $legacyEnabled
                ? WebsiteSetting::get('maintenance_message', null)
                : WebsiteSetting::get('site_maintenance_message', null);
        }

        return trim((string) $message) !== '' ? (string) $message : $default;
    }
}
