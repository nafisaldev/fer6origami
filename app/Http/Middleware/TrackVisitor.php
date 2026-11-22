<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use App\Services\DeviceDetector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET responses for frontend pages
        if (!$request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return $response;
        }

        // Ignore admin, api, debug, and asset routes
        if ($request->is('admin*', 'api*', '_debugbar*', 'sanctum*')) {
            return $response;
        }

        try {
            $ip = $request->ip() ?: '127.0.0.1';
            $ua = (string) $request->userAgent();

            // Throttle: Log at most once per 10 minutes per IP to keep stats clean & lightweight
            $recent = VisitorLog::where('ip_address', $ip)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->first();

            if (!$recent) {
                $deviceInfo = DeviceDetector::detect($ua);

                VisitorLog::create([
                    'ip_address' => $ip,
                    'user_id' => auth()->id(),
                    'url' => substr($request->fullUrl(), 0, 500),
                    'user_agent' => substr($ua, 0, 255),
                    'device_name' => $deviceInfo['device_name'],
                    'device_type' => $deviceInfo['device_type'],
                    'browser' => $deviceInfo['browser'],
                    'platform' => $deviceInfo['platform'],
                ]);
            } else {
                $updates = [];
                if (auth()->check() && !$recent->user_id) {
                    $updates['user_id'] = auth()->id();
                }
                if (empty($recent->device_name)) {
                    $deviceInfo = DeviceDetector::detect($ua);
                    $updates['device_name'] = $deviceInfo['device_name'];
                    $updates['device_type'] = $deviceInfo['device_type'];
                    $updates['browser'] = $deviceInfo['browser'];
                    $updates['platform'] = $deviceInfo['platform'];
                }
                if (!empty($updates)) {
                    $recent->update($updates);
                }
            }
        } catch (Throwable) {
            // Silently ignore visitor logging failures
        }

        return $response;
    }
}
