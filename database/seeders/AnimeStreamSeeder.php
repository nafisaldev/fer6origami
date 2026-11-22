<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Banner;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnimeStreamSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@animestream.test'],
            [
                'name' => 'AnimeStream Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        $member = User::query()->updateOrCreate(
            ['email' => 'user@animestream.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );




        unset($member, $admin);
    }
}
