<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\MembershipPlanPrice;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\MembershipService;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            MembershipPlan::FREE => [
                'name' => 'Free',
                'tagline' => 'Nonton anime favorit tanpa biaya',
                'description' => 'Akses katalog dasar, watchlist, riwayat tontonan, dan misi mingguan.',
                'benefits' => [
                    'Akses episode reguler',
                    'Watchlist dan riwayat tontonan',
                    'Misi mingguan dan level',
                    'Komentar dan review',
                ],
                'sort_order' => 1,
                'is_default' => true,
                'prices' => [],
            ],
            MembershipPlan::PREMIUM => [
                'name' => 'Premium',
                'tagline' => 'Lebih banyak anime, lebih cepat',
                'description' => 'Episode premium, akses awal, badge khusus, dan XP 1,25x.',
                'benefits' => [
                    'Semua fitur Free',
                    'Episode premium',
                    'Akses awal episode',
                    'Badge Premium',
                    'XP 1,25x',
                    'Ekspor riwayat CSV',
                ],
                'sort_order' => 2,
                'is_default' => false,
                'prices' => [
                    7 => 15000,
                    30 => 49000,
                    90 => 129000,
                    365 => 449000,
                ],
            ],
            MembershipPlan::VIP => [
                'name' => 'VIP',
                'tagline' => 'Akses penuh untuk penggemar berat',
                'description' => 'Semua fitur Premium ditambah episode VIP dan XP 1,5x.',
                'benefits' => [
                    'Semua fitur Premium',
                    'Episode VIP',
                    'XP 1,5x',
                    'Prioritas notifikasi pembaharuan',
                ],
                'sort_order' => 3,
                'is_default' => false,
                'prices' => [
                    7 => 25000,
                    30 => 79000,
                    90 => 219000,
                    365 => 749000,
                ],
            ],
        ];

        foreach ($plans as $slug => $data) {
            $plan = MembershipPlan::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'tagline' => $data['tagline'],
                    'description' => $data['description'],
                    'benefits' => $data['benefits'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                    'is_default' => $data['is_default'],
                ]
            );

            foreach ($data['prices'] as $days => $price) {
                MembershipPlanPrice::query()->updateOrCreate(
                    ['plan_id' => $plan->id, 'duration_days' => $days],
                    [
                        'label' => $this->durationLabel($days),
                        'price' => $price,
                        'currency' => 'IDR',
                        'is_active' => true,
                        'sort_order' => $days,
                    ]
                );
            }
        }

        WebsiteSetting::set('membership_xp_boost_premium', config('membership.xp_boost.premium'), 'membership');
        WebsiteSetting::set('membership_xp_boost_vip', config('membership.xp_boost.vip'), 'membership');
        WebsiteSetting::set('membership_expiry_warning_days', config('membership.expiry_warning_days'), 'membership');

        $service = app(MembershipService::class);
        foreach (User::query()->pluck('id') as $userId) {
            $user = User::find($userId);

            if ($user) {
                $service->currentMembership($user);
            }
        }
    }

    private function durationLabel(int $days): string
    {
        return match ($days) {
            7 => '7 hari',
            30 => '30 hari',
            90 => '90 hari',
            365 => '12 bulan',
            default => $days . ' hari',
        };
    }
}
