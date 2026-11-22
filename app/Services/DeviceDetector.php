<?php

namespace App\Services;

class DeviceDetector
{
    /**
     * Parse User-Agent string and return detected device details.
     *
     * @return array{
     *     device_name: string,
     *     device_type: string,
     *     browser: string,
     *     platform: string
     * }
     */
    public static function detect(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return [
                'device_name' => 'Unknown Device',
                'device_type' => 'Desktop',
                'browser' => 'Unknown',
                'platform' => 'Unknown',
            ];
        }

        // 1. Detect Bots / Crawlers
        if (preg_match('/(bot|googlebot|bingbot|crawler|spider|slurp|duckduckbot|baiduspider|yandexbot)/i', $ua, $matches)) {
            $botName = ucfirst(strtolower($matches[1]));
            return [
                'device_name' => "Search Bot ({$botName})",
                'device_type' => 'Bot',
                'browser' => 'Bot Crawler',
                'platform' => 'Web Crawler',
            ];
        }

        // 2. Detect Platform
        $platform = 'Unknown';
        if (stripos($ua, 'Windows NT 10.0') !== false) {
            $platform = 'Windows 10/11';
        } elseif (stripos($ua, 'Windows NT 6.3') !== false) {
            $platform = 'Windows 8.1';
        } elseif (stripos($ua, 'Windows NT 6.1') !== false) {
            $platform = 'Windows 7';
        } elseif (stripos($ua, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($ua, 'iPhone') !== false) {
            $platform = 'iOS';
        } elseif (stripos($ua, 'iPad') !== false) {
            $platform = 'iPadOS';
        } elseif (stripos($ua, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) {
            $platform = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $platform = 'Linux';
        }

        // 3. Detect Device Type & Model
        $deviceType = 'Desktop';
        $deviceName = 'Desktop PC';

        if (stripos($ua, 'iPad') !== false) {
            $deviceType = 'Tablet';
            $deviceName = 'Apple iPad';
        } elseif (stripos($ua, 'iPhone') !== false) {
            $deviceType = 'Mobile';
            $deviceName = 'Apple iPhone';
        } elseif (stripos($ua, 'Android') !== false) {
            $deviceType = stripos($ua, 'Mobile') !== false ? 'Mobile' : 'Tablet';

            // Try to extract Android manufacturer/model
            $deviceName = self::parseAndroidModel($ua, $deviceType);
        } elseif ($platform === 'Windows 10/11' || $platform === 'Windows') {
            $deviceType = 'Desktop';
            $deviceName = 'Windows PC';
        } elseif ($platform === 'macOS') {
            $deviceType = 'Desktop';
            $deviceName = 'Apple Mac';
        } elseif ($platform === 'Linux') {
            $deviceType = 'Desktop';
            $deviceName = 'Linux PC';
        }

        // 4. Detect Browser
        $browser = 'Unknown';
        if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) {
            $browser = 'Microsoft Edge';
        } elseif (stripos($ua, 'SamsungBrowser') !== false) {
            $browser = 'Samsung Internet';
        } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Safari') !== false) {
            $browser = 'Google Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (stripos($ua, 'Safari') !== false) {
            $browser = 'Apple Safari';
        }

        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    private static function parseAndroidModel(string $ua, string $fallbackType): string
    {
        $model = null;

        if (preg_match('/;\s*([A-Za-z0-9\-\_\s]+)\s+Build\//i', $ua, $matches)) {
            $model = trim($matches[1]);
        } elseif (preg_match('/Android\s+[^;)]+;\s*([^;)]{3,35})\)/i', $ua, $matches)) {
            $candidate = trim($matches[1]);
            if (!in_array(strtolower($candidate), ['k', 'wv', 'mobile', 'tablet'])) {
                $model = $candidate;
            }
        } elseif (preg_match('/\b(SM-[A-Za-z0-9]+|Pixel\s+[0-9a-zA-Z\s]+|Redmi\s+[0-9a-zA-Z\s]+|POCO\s+[0-9a-zA-Z\s]+|CPH[0-9]+|RMX[0-9]+|Infinix\s+[0-9a-zA-Z\s]+)\b/i', $ua, $matches)) {
            $model = trim($matches[1]);
        }

        if ($model) {
            // Clean common prefixes
            if (stripos($model, 'SM-') === 0 || stripos($model, 'SAMSUNG') !== false) {
                return 'Samsung Galaxy (' . str_ireplace('SAMSUNG ', '', $model) . ')';
            }
            if (stripos($model, 'Pixel') !== false) {
                return stripos($model, 'Google') === false ? 'Google ' . $model : $model;
            }
            if (stripos($model, 'Redmi') !== false || stripos($model, 'POCO') !== false || stripos($model, 'Mi ') === 0) {
                return stripos($model, 'Xiaomi') === false ? 'Xiaomi ' . $model : $model;
            }
            if (stripos($model, 'CPH') === 0) {
                return 'OPPO (' . $model . ')';
            }
            if (stripos($model, 'V2') === 0 || stripos($model, 'vivo') !== false) {
                return 'vivo (' . $model . ')';
            }
            if (stripos($model, 'Infinix') !== false) {
                return $model;
            }
            if (stripos($model, 'RMX') === 0 || stripos($model, 'Realme') !== false) {
                return 'Realme (' . $model . ')';
            }

            if (strlen($model) >= 3 && strlen($model) <= 30) {
                return 'Android (' . $model . ')';
            }
        }

        return $fallbackType === 'Tablet' ? 'Android Tablet' : 'Android Smartphone';
    }
}
