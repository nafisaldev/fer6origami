<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    public function test_missing_avatar_uses_local_initial_placeholder(): void
    {
        $user = new User(['name' => 'Nafisa Dev']);

        $this->assertStringStartsWith('data:image/svg+xml', $user->avatar_url);
        $this->assertStringContainsString('N', rawurldecode($user->avatar_url));
        $this->assertStringNotContainsString('ui-avatars.com', $user->avatar_url);
    }

    public function test_existing_public_avatar_uses_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/test.jpg', 'fake-image');

        $user = new User([
            'name' => 'Nafisa',
            'avatar_path' => 'avatars/test.jpg',
        ]);

        $this->assertStringContainsString('/storage/avatars/test.jpg', $user->avatar_url);
    }

    public function test_stale_avatar_path_falls_back_to_placeholder(): void
    {
        Storage::fake('public');

        $user = new User([
            'name' => 'Nafisa',
            'avatar_path' => 'avatars/missing.jpg',
        ]);

        $this->assertStringStartsWith('data:image/svg+xml', $user->avatar_url);
    }

    public function test_database_avatar_uses_persistent_route(): void
    {
        $user = new User(['name' => 'Nafisa', 'avatar_path' => 'db://avatars/avatar.jpg']);
        $user->id = 12;

        $this->assertStringContainsString('/avatar/12', $user->avatar_url);
    }

    public function test_absolute_avatar_url_is_preserved(): void
    {
        $user = new User([
            'name' => 'Nafisa',
            'avatar_path' => 'https://example.com/avatar.jpg',
        ]);

        $this->assertSame('https://example.com/avatar.jpg', $user->avatar_url);
    }
}
