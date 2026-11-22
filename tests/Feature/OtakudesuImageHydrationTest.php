<?php

namespace Tests\Feature;

use App\Services\OtakudesuApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtakudesuImageHydrationTest extends TestCase
{
    public function test_catalog_hydrates_missing_images_from_source_when_api_is_unavailable(): void
    {
        Cache::flush();

        config([
            'services.otakudesu.base_url' => 'http://127.0.0.1:3000/api',
            'services.otakudesu.proxy_images' => true,
            'services.otakudesu.source_url' => 'https://otakudesu.best',
        ]);

        Http::fake([
            'http://127.0.0.1:3000/api/anime-list' => Http::response([], 500),
            'https://otakudesu.best/anime-list/' => Http::response($this->animeListHtml(), 200),
            'https://otakudesu.best/anime/one-piece/' => Http::response($this->animeDetailHtml(), 200),
        ]);

        $catalog = app(OtakudesuApiService::class)->catalog([], 1);
        $anime = $catalog->items->first();

        $this->assertSame('one-piece', $anime->id);
        $this->assertStringNotContainsString('placehold.co', $anime->poster_image);
        $this->assertStringContainsString('/image-proxy?url=', $anime->poster_image);
        $this->assertStringContainsString('One-Piece.jpg', rawurldecode($anime->poster_image));
    }

    private function animeListHtml(): string
    {
        return <<<'HTML'
            <html>
                <body>
                    <a href="https://otakudesu.best/anime/one-piece/">One Piece</a>
                </body>
            </html>
        HTML;
    }

    private function animeDetailHtml(): string
    {
        return <<<'HTML'
            <html>
                <body>
                    <div class="fotoanime">
                        <img src="https://otakudesu.best/wp-content/uploads/One-Piece.jpg">
                    </div>
                    <div class="infozingle">
                        <p><b>Judul</b>: One Piece</p>
                        <p><b>Skor</b>: 9.1</p>
                    </div>
                    <div class="sinopc">Sinopsis One Piece.</div>
                    <div class="episodelist">
                        <li>
                            <a href="https://otakudesu.best/episode/one-piece-episode-1/">Episode 1</a>
                            <span class="zeebr">Hari ini</span>
                        </li>
                    </div>
                </body>
            </html>
        HTML;
    }
}
