<?php

namespace App\Console\Commands;

use App\Models\MembershipNotification;
use App\Models\UserMembership;
use App\Models\WebsiteSetting;
use App\Services\MembershipNotificationService;
use App\Services\MembershipService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class MembershipSyncCommand extends Command
{
    protected $signature = 'membership:sync';

    protected $description = 'Menurunkan membership kedaluwarsa, mengirim pengingat, dan menutup invoice lama';

    public function handle(
        MembershipService $membership,
        MembershipNotificationService $notifications,
        PaymentService $payments
    ): int {
        $expiredCount = 0;

        foreach ($membership->expiredMemberships() as $current) {
            $previous = $current->load('plan');
            $membership->expire($previous->user);
            $notifications->expired($previous);
            $expiredCount++;
        }

        $warningDays = $this->warningDays();
        $warningCount = 0;

        foreach ($warningDays as $days) {
            foreach ($membership->expiringMemberships($days) as $current) {
                $daysLeft = (int) ceil(now()->diffInMinutes($current->expires_at) / 1440);

                if ($daysLeft !== $days) {
                    continue;
                }

                $alreadySent = MembershipNotification::query()
                    ->where('user_id', $current->user_id)
                    ->where('type', 'expiry_warning')
                    ->where('metadata->days_left', $days)
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $notifications->warning($current->load('plan'), $days);
                $warningCount++;
            }
        }

        $expiredPayments = $payments->expirePending();

        $this->info("Membership kedaluwarsa: {$expiredCount}. Pengingat: {$warningCount}. Invoice kedaluwarsa: {$expiredPayments}.");

        return self::SUCCESS;
    }

    private function warningDays(): array
    {
        $value = WebsiteSetting::get(
            'membership_expiry_warning_days',
            config('membership.expiry_warning_days', '7,3,1')
        );

        $days = array_values(array_filter(array_map('intval', explode(',', (string) $value))));

        return $days ?: [7, 3, 1];
    }
}
