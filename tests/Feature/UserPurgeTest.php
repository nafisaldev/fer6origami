<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class UserPurgeTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_purges_all_users_except_specified_one()
    {
        // Create target user
        $target = User::factory()->create([
            'name' => 'Mrs. Carmela Ferry',
            'email' => 'carmela@example.com',
        ]);

        // Create other users
        $others = User::factory()->count(3)->create();

        // Run the purge command
        $this->artisan('users:purge', ['--keep' => 'carmela@example.com'])
            ->expectsOutput('Purge completed. Remaining users: 1')
            ->assertExitCode(0);

        // Only the target remains
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => 'carmela@example.com', 'name' => 'Mrs. Carmela Ferry']);
        foreach ($others as $other) {
            $this->assertDatabaseMissing('users', ['id' => $other->id]);
        }
    }
}
?>