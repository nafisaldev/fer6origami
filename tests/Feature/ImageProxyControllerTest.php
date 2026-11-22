<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImageProxyControllerTest extends TestCase
{
    public function test_image_proxy_tries_another_otakudesu_domain_when_original_image_fails(): void
    {
        Cache::flush();

        Http::fake([
            'https://otakudesu.best/wp-content/uploads/poster.jpg' => Http::response('missing', 404, [
                'Content-Type' => 'text/html',
            ]),
            'https://otakudesu.blog/wp-content/uploads/poster.jpg' => Http::response('image-body', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $response = $this->get('/image-proxy?url=' . rawurlencode('https://otakudesu.best/wp-content/uploads/poster.jpg'));

        $response->assertOk();
        $response->assertContent('image-body');
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
    }

    public function test_image_proxy_redirects_to_source_when_all_upstreams_fail(): void
    {
        Cache::flush();

        Http::fake(fn () => Http::response('blocked', 403, [
            'Content-Type' => 'text/html',
        ]));

        $source = 'https://otakudesu.best/wp-content/uploads/poster.jpg';
        $response = $this->get('/image-proxy?url=' . rawurlencode($source));

        $response->assertRedirect($source);
        $this->assertStringContainsString('max-age=300', (string) $response->headers->get('Cache-Control'));
    }
}
