<?php

namespace App\Services;

use App\Models\MembershipNotification;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserMembership;

class MembershipNotificationService
{
    public function warning(UserMembership $membership, int $days): MembershipNotification
    {
        return MembershipNotification::create([
            'user_id' => $membership->user_id,
            'membership_id' => $membership->id,
            'type' => 'expiry_warning',
            'title' => 'Membership segera berakhir',
            'message' => 'Paket ' . $membership->plan->name . ' Anda berakhir dalam ' . $days . ' hari. Perpanjang sekarang untuk mempertahankan akses.',
            'action_url' => route('membership.pricing'),
            'channel' => 'in_app',
            'metadata' => ['days_left' => $days],
            'sent_at' => now(),
        ]);
    }

    public function expired(UserMembership $membership): MembershipNotification
    {
        return MembershipNotification::create([
            'user_id' => $membership->user_id,
            'membership_id' => $membership->id,
            'type' => 'expired',
            'title' => 'Membership berakhir',
            'message' => 'Akses ' . $membership->plan->name . ' Anda telah berakhir. Akun Anda kembali ke paket Free.',
            'action_url' => route('membership.pricing'),
            'channel' => 'in_app',
            'metadata' => ['plan' => $membership->plan->slug],
            'sent_at' => now(),
        ]);
    }

    public function paymentSuccess(Payment $payment, UserMembership $membership): MembershipNotification
    {
        return MembershipNotification::create([
            'user_id' => $payment->user_id,
            'membership_id' => $membership->id,
            'type' => 'payment_success',
            'title' => 'Pembayaran berhasil',
            'message' => 'Invoice ' . $payment->invoice_number . ' lunas. Paket ' . $membership->plan->name . ' aktif sampai ' . optional($membership->expires_at)->translatedFormat('d M Y H:i') . '.',
            'action_url' => route('membership.show'),
            'channel' => 'in_app',
            'metadata' => ['payment_id' => $payment->id],
            'sent_at' => now(),
        ]);
    }

    public function manualVerified(Payment $payment, UserMembership $membership): MembershipNotification
    {
        return MembershipNotification::create([
            'user_id' => $payment->user_id,
            'membership_id' => $membership->id,
            'type' => 'manual_verified',
            'title' => 'Pembayaran manual disetujui',
            'message' => 'Administrator telah menyetujui invoice ' . $payment->invoice_number . '. Paket Anda sudah aktif.',
            'action_url' => route('membership.show'),
            'channel' => 'in_app',
            'metadata' => ['payment_id' => $payment->id],
            'sent_at' => now(),
        ]);
    }
}
