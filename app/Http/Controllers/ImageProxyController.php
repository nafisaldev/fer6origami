<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ImageProxyController extends Controller
{
    private const ALLOWED_HOST_KEYWORDS = [
        'otakudesu',
        'candid.asia',
        'wp.com',
        'blogspot.com',
        'googleusercontent.com',
    ];

    public function __invoke(Request $request): Response
    {
        $url = trim((string) $request->query('url', ''));

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            abort(404);
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        $isAllowed = false;
        foreach (self::ALLOWED_HOST_KEYWORDS as $keyword) {
            if (str_contains($host, $keyword)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            abort(404);
        }

        $etag = '"' . md5($url) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, [
                'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400, immutable',
                'ETag' => $etag,
            ]);
        }

        $payload = Cache::remember('image-proxy:v2:' . md5($url), now()->addHours(24), function () use ($url) {
            return $this->fetchImagePayload($url);
        });

        return response(base64_decode((string) ($payload['body'] ?? ''), true) ?: '', 200, [
            'Content-Type' => (string) ($payload['content_type'] ?? 'image/jpeg'),
            'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400, immutable',
            'ETag' => $etag,
        ]);
    }

    private function fetchImagePayload(string $url): array
    {
        $lastResponse = null;

        foreach ($this->candidateImageUrls($url) as $candidateUrl) {
            try {
                $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                        'Referer' => $this->origin($candidateUrl) . '/',
                    ])
                    ->withOptions([
                        'proxy' => '',
                        'verify' => false,
                    ])
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->get($candidateUrl);

                $contentType = strtolower((string) $response->header('Content-Type', ''));

                if (! $response->successful() || ($contentType !== '' && ! str_starts_with($contentType, 'image/'))) {
                    $lastResponse = $response;
                    continue;
                }

                return [
                    'body' => base64_encode($response->body()),
                    'content_type' => $response->header('Content-Type', 'image/jpeg'),
                ];
            } catch (\Throwable $e) {
                continue;
            }
        }

        if ($lastResponse) {
            throw new RequestException($lastResponse);
        }

        abort(404);
    }

    private function candidateImageUrls(string $url): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $query = parse_url($url, PHP_URL_QUERY);

        if ($path === '') {
            return [$url];
        }

        $urls = [];
        $configuredHost = strtolower((string) parse_url((string) config('services.otakudesu.source_url', 'https://otakudesu.blog'), PHP_URL_HOST));

        $candidateHosts = array_values(array_unique(array_filter([
            $configuredHost ?: 'otakudesu.blog',
            'otakudesu.blog',
            $host,
            'otakudesu.candid.asia',
            'otakudesu.cc',
        ])));

        foreach ($candidateHosts as $candidateHost) {
            $urls[] = $scheme . '://' . $candidateHost . $path . ($query ? '?' . $query : '');
        }

        $urls[] = $url;

        return array_values(array_unique($urls));
    }

    private function origin(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $port = parse_url($url, PHP_URL_PORT);

        return $scheme . '://' . $host . ($port ? ':' . $port : '');
    }
}
