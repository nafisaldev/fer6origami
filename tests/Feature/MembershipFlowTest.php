<?php

namespace Tests\Feature;

use App\Models\MembershipHistory;
use App\Models\MembershipPlan;
use App\Models\MembershipPlanPrice;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WebsiteSetting;
use App\Services\MembershipService;
use App\Services\PaymentService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MembershipFlowTest extends TestCase
{
    use DatabaseTransactions;

    private const MIDTRANS_SERVER_KEY = 'test-server-key';

    protected function setUp(): void
    {
        parent::setUp();

        WebsiteSetting::set('site_maintenance', '0');
        WebsiteSetting::set('membership_xp_boost_premium', config('membership.xp_boost.premium'));
        WebsiteSetting::set('membership_xp_boost_vip', config('membership.xp_boost.vip'));

        config()->set('services.midtrans.server_key', self::MIDTRANS_SERVER_KEY);
        config()->set('services.midtrans.client_key', 'test-client-key');

        Http::fake([
            '*' => Http::response([
                'status' => 'success',
                'token' => 'snap-test-token',
                'redirect_url' => 'https://snap.test/redirect',
                'home' => ['on_going' => [], 'complete' => []],
                'animeList' => [],
                'genreList' => [],
            ], 200),
        ]);
    }

    public function test_tier_hierarchy_and_xp_multiplier_follow_configuration(): void
    {
        $plans = $this->createPlans();
        $membership = app(MembershipService::class);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $this->assertSame([
            MembershipPlan::FREE => 0,
            MembershipPlan::PREMIUM => 1,
            MembershipPlan::VIP => 2,
        ], $membership->tiers());

        $this->assertSame(MembershipPlan::FREE, $membership->tier($user));
        $this->assertTrue($membership->hasAtLeast($user, MembershipPlan::FREE));
        $this->assertFalse($membership->hasAtLeast($user, MembershipPlan::PREMIUM));
        $this->assertFalse($membership->hasAtLeast($user, MembershipPlan::VIP));
        $this->assertEqualsWithDelta(
            (float) config('membership.xp_boost.free'),
            $membership->xpMultiplier($user),
            0.001
        );

        $membership->grant($user, $plans[MembershipPlan::PREMIUM], 30);
        $user = $user->fresh();

        $this->assertSame(MembershipPlan::PREMIUM, $membership->tier($user));
        $this->assertTrue($membership->hasAtLeast($user, MembershipPlan::PREMIUM));
        $this->assertFalse($membership->hasAtLeast($user, MembershipPlan::VIP));
        $this->assertEqualsWithDelta(
            (float) config('membership.xp_boost.premium'),
            $membership->xpMultiplier($user),
            0.001
        );

        $membership->grant($user, $plans[MembershipPlan::VIP], 30);
        $user = $user->fresh();

        $this->assertSame(MembershipPlan::VIP, $membership->tier($user));
        $this->assertTrue($membership->hasAtLeast($user, MembershipPlan::FREE));
        $this->assertTrue($membership->hasAtLeast($user, MembershipPlan::PREMIUM));
        $this->assertTrue($membership->hasAtLeast($user, MembershipPlan::VIP));
        $this->assertEqualsWithDelta(
            (float) config('membership.xp_boost.vip'),
            $membership->xpMultiplier($user),
            0.001
        );

        $user->update(['xp_manual' => true]);

        $this->assertSame(1.0, $membership->xpMultiplier($user->fresh()));
    }

    public function test_plan_helper_is_safe_with_preexisting_seeded_data(): void
    {
        // Panggilan pertama mensimulasikan database sebelum seeder, panggilan
        // kedua mensimulasikan database yang sudah berisi MembershipSeeder.
        $first = $this->createPlans();
        $second = $this->createPlans();

        foreach ($first as $slug => $plan) {
            $this->assertSame($plan->id, $second[$slug]->id);
        }

        $planIds = collect($second)->pluck('id')->all();

        $this->assertSame(3, MembershipPlan::query()->whereIn('slug', array_keys($second))->count());
        $this->assertSame(8, MembershipPlanPrice::query()
            ->whereIn('plan_id', $planIds)
            ->whereIn('duration_days', [7, 30, 90, 365])
            ->count());
        $this->assertTrue((bool) MembershipPlan::query()->whereKey($second[MembershipPlan::FREE]->id)->value('is_default'));
        $this->assertFalse((bool) MembershipPlan::query()->whereKey($second[MembershipPlan::PREMIUM]->id)->value('is_default'));
        $this->assertFalse((bool) MembershipPlan::query()->whereKey($second[MembershipPlan::VIP]->id)->value('is_default'));
    }

    public function test_admin_can_grant_premium_and_history_is_recorded(): void
    {
        $plans = $this->createPlans();

        // Role admin memakai fallback permission legacy karena role_id tidak di-set.
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $premium = $plans[MembershipPlan::PREMIUM];
        $expectedExpiry = now()->addDays(30);

        $this->actingAs($admin)
            ->post(route('admin.membership.grant', $target), [
                'action' => 'grant',
                'plan_id' => $premium->id,
                'duration_days' => 30,
                'reason' => 'Grant otomatis test',
            ])
            ->assertRedirect(route('admin.membership.show', $target));

        $membership = $target->fresh()->membership;

        $this->assertNotNull($membership);
        $this->assertSame($premium->id, $membership->plan_id);
        $this->assertSame('active', $membership->status);
        $this->assertSame($admin->id, $membership->created_by);
        $this->assertExpiresNear($membership->expires_at, $expectedExpiry);

        $this->assertDatabaseHas('membership_subscriptions', [
            'user_id' => $target->id,
            'plan_id' => $premium->id,
            'action' => 'grant',
            'duration_days' => 30,
        ]);

        $this->assertDatabaseHas('membership_histories', [
            'user_id' => $target->id,
            'to_plan_id' => $premium->id,
            'action' => 'grant',
            'duration_days' => 30,
            'reason' => 'Grant otomatis test',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_renew_extends_old_expiry_and_plan_changes_and_revoke(): void
    {
        $plans = $this->createPlans();
        $membership = app(MembershipService::class);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $premium = $plans[MembershipPlan::PREMIUM];
        $vip = $plans[MembershipPlan::VIP];

        $membership->grant($user, $premium, 30, $admin);
        $user = $user->fresh();
        $firstExpiry = $user->membership->expires_at->copy();

        $membership->renew($user, $premium, 15, $admin);
        $user = $user->fresh();
        $renewedExpiry = $user->membership->expires_at->copy();

        $this->assertExpiresNear($renewedExpiry, $firstExpiry->copy()->addDays(15));

        $membership->changePlan($user, $vip, $admin, 'Upgrade ke VIP');
        $user = $user->fresh();

        $this->assertSame(MembershipPlan::VIP, $user->membership->plan->slug);
        $this->assertExpiresNear($user->membership->expires_at, $renewedExpiry);

        $membership->changePlan($user, $premium, $admin, 'Turun ke Premium', 7);
        $user = $user->fresh();

        $this->assertSame(MembershipPlan::PREMIUM, $user->membership->plan->slug);
        $this->assertExpiresNear($user->membership->expires_at, now()->addDays(7));

        $membership->revoke($user, $admin, 'Dicabut oleh admin');
        $user = $user->fresh();

        $this->assertSame(MembershipPlan::FREE, $user->membership->plan->slug);
        $this->assertNull($user->membership->expires_at);
        $this->assertFalse($membership->hasAtLeast($user, MembershipPlan::PREMIUM));

        foreach (['grant', 'renew', 'upgrade', 'downgrade', 'revoke'] as $action) {
            $this->assertDatabaseHas('membership_histories', [
                'user_id' => $user->id,
                'action' => $action,
            ]);
        }
    }

    public function test_expiry_command_downgrades_expired_membership_only_once(): void
    {
        $plans = $this->createPlans();
        $membership = app(MembershipService::class);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $membership->activate($user, $plans[MembershipPlan::PREMIUM], [
            'duration_days' => 30,
            'action' => 'grant',
            'source' => 'admin_grant',
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($membership->hasAtLeast($user->fresh(), MembershipPlan::PREMIUM));

        Artisan::call('membership:sync');

        $user = $user->fresh();

        $this->assertSame(MembershipPlan::FREE, $user->membership->plan->slug);
        $this->assertNull($user->membership->expires_at);
        $this->assertSame(1, $this->expiryHistoryCount($user));

        Artisan::call('membership:sync');

        $this->assertSame(MembershipPlan::FREE, $user->fresh()->membership->plan->slug);
        $this->assertSame(1, $this->expiryHistoryCount($user));
    }

    public function test_manual_checkout_uses_server_price_and_voucher_without_activating_membership(): void
    {
        $plans = $this->createPlans();
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $price = $plans[MembershipPlan::PREMIUM]->activePrices()->where('duration_days', 30)->firstOrFail();
        $voucher = $this->createVoucher(20000);

        $this->actingAs($user)
            ->post(route('membership.checkout.store'), [
                'plan_price_id' => $price->id,
                'gateway' => Payment::GATEWAY_MANUAL,
                'voucher_code' => $voucher->code,
                'amount' => 1,
                'total_amount' => 1,
            ])
            ->assertStatus(302);

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame(Payment::GATEWAY_MANUAL, $payment->gateway);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertEqualsWithDelta((float) $price->price, (float) $payment->amount, 0.001);
        $this->assertEqualsWithDelta(20000.0, (float) $payment->discount_amount, 0.001);
        $this->assertEqualsWithDelta((float) $price->price - 20000, (float) $payment->total_amount, 0.001);

        $this->assertDatabaseHas('voucher_redemptions', [
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'payment_id' => $payment->id,
        ]);

        $this->assertSame(MembershipPlan::FREE, app(MembershipService::class)->tier($user->fresh()));
        $this->assertDatabaseMissing('membership_subscriptions', [
            'user_id' => $user->id,
            'action' => 'purchase',
        ]);
    }

    public function test_midtrans_webhook_valid_invalid_and_duplicate_payload(): void
    {
        $plans = $this->createPlans();
        $payments = app(PaymentService::class);
        $membership = app(MembershipService::class);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $price = $plans[MembershipPlan::PREMIUM]->activePrices()->where('duration_days', 30)->firstOrFail();

        $payment = $this->checkout($user, $price, Payment::GATEWAY_MIDTRANS);

        $this->assertSame(Payment::GATEWAY_MIDTRANS, $payment->gateway);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);

        $payload = $this->webhookPayload($payment);
        $result = $payments->handleWebhook($payload);

        $this->assertSame('success', $result['status']);
        $this->assertFalse($result['duplicate']);
        $this->assertSame(Payment::STATUS_SUCCESS, $payment->fresh()->status);

        $paidMembership = $user->fresh()->membership;

        $this->assertSame(MembershipPlan::PREMIUM, $paidMembership->plan->slug);
        $this->assertExpiresNear($paidMembership->expires_at, now()->addDays(30));
        $this->assertSame(1, $this->purchaseCount($payment));
        $this->assertDatabaseHas('membership_notifications', [
            'user_id' => $user->id,
            'type' => 'payment_success',
        ]);

        $duplicate = $payments->handleWebhook($payload);

        $this->assertSame('duplicate', $duplicate['status']);
        $this->assertTrue($duplicate['duplicate']);
        $this->assertSame(1, $this->purchaseCount($payment));

        $otherUser = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $otherPayment = $this->checkout($otherUser, $price, Payment::GATEWAY_MIDTRANS);
        $invalidPayload = $this->webhookPayload($otherPayment, [], 'signature-salah');
        $invalid = $payments->handleWebhook($invalidPayload);

        $this->assertSame('invalid_signature', $invalid['status']);
        $this->assertFalse($invalid['duplicate']);
        $this->assertSame(Payment::STATUS_PENDING, $otherPayment->fresh()->status);
        $this->assertSame(MembershipPlan::FREE, $membership->tier($otherUser->fresh()));
        $this->assertDatabaseHas('payment_webhook_events', [
            'order_id' => $otherPayment->gateway_order_id,
            'status' => 'invalid_signature',
        ]);
    }

    public function test_payment_callback_never_activates_pending_membership(): void
    {
        $plans = $this->createPlans();
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $price = $plans[MembershipPlan::PREMIUM]->activePrices()->where('duration_days', 30)->firstOrFail();
        $payment = $this->checkout($user, $price, Payment::GATEWAY_MANUAL);

        $this->actingAs($user)
            ->get(route('payments.callback', [
                'invoice' => $payment->invoice_number,
                'transaction_status' => 'settlement',
                'status_code' => '200',
            ]))
            ->assertOk()
            ->assertSee('Pembayaran masih diproses');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(MembershipPlan::FREE, app(MembershipService::class)->tier($user->fresh()));
        $this->assertDatabaseMissing('membership_subscriptions', [
            'user_id' => $user->id,
            'action' => 'purchase',
        ]);
    }

    public function test_admin_manual_verification_activates_only_pending_manual_payment(): void
    {
        $plans = $this->createPlans();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $price = $plans[MembershipPlan::PREMIUM]->activePrices()->where('duration_days', 30)->firstOrFail();
        $payment = $this->checkout($user, $price, Payment::GATEWAY_MANUAL);

        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertRedirect();

        $payment->refresh();

        $this->assertSame(Payment::STATUS_SUCCESS, $payment->status);
        $this->assertSame($admin->id, $payment->verified_by);
        $this->assertNotNull($payment->paid_at);

        $paidMembership = $user->fresh()->membership;

        $this->assertSame(MembershipPlan::PREMIUM, $paidMembership->plan->slug);
        $this->assertExpiresNear($paidMembership->expires_at, now()->addDays(30));
        $this->assertDatabaseHas('membership_notifications', [
            'user_id' => $user->id,
            'type' => 'payment_success',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertStatus(422);

        $otherUser = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $midtransPayment = $this->checkout($otherUser, $price, Payment::GATEWAY_MIDTRANS);

        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $midtransPayment))
            ->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $midtransPayment->fresh()->status);
        $this->assertSame(MembershipPlan::FREE, app(MembershipService::class)->tier($otherUser->fresh()));
    }

    public function test_membership_and_profile_pages_render_without_errors(): void
    {
        $this->createPlans();
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $this->actingAs($user)->get(route('membership.pricing'))->assertOk();
        $this->actingAs($user)->get(route('membership.show'))->assertOk();
        $this->actingAs($user)->get(route('profile.show'))->assertOk();
    }

    public function test_regular_user_cannot_access_admin_membership_panels(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $this->actingAs($user)->get(route('admin.membership.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.membership-plans.index'))->assertStatus(403);
    }

    /**
     * @return array<string, MembershipPlan>
     */
    private function createPlans(): array
    {
        MembershipPlan::query()->update(['is_default' => false]);

        $names = [
            MembershipPlan::FREE => 'Free',
            MembershipPlan::PREMIUM => 'Premium',
            MembershipPlan::VIP => 'VIP',
        ];

        $plans = [];

        foreach ($names as $slug => $name) {
            $plans[$slug] = MembershipPlan::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                    'is_default' => $slug === MembershipPlan::FREE,
                    'sort_order' => array_search($slug, array_keys($names), true) + 1,
                    'benefits' => [],
                ]
            );
        }

        $prices = [
            7 => 35000,
            30 => 100000,
            90 => 250000,
            365 => 900000,
        ];

        foreach ([MembershipPlan::PREMIUM, MembershipPlan::VIP] as $slug) {
            foreach ($prices as $days => $amount) {
                MembershipPlanPrice::query()->updateOrCreate(
                    ['plan_id' => $plans[$slug]->id, 'duration_days' => $days],
                    [
                        'label' => $days . ' hari',
                        'price' => $amount,
                        'currency' => 'IDR',
                        'is_active' => true,
                        'sort_order' => $days,
                    ]
                );
            }
        }

        return $plans;
    }

    private function createVoucher(float $value): Voucher
    {
        return Voucher::create([
            'code' => 'FLOW' . strtoupper(Str::random(6)),
            'name' => 'Voucher test membership',
            'type' => Voucher::TYPE_FIXED,
            'value' => $value,
            'usage_limit' => 10,
            'used_count' => 0,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
    }

    private function checkout(
        User $user,
        MembershipPlanPrice $price,
        string $gateway = Payment::GATEWAY_MANUAL,
        ?string $voucherCode = null
    ): Payment {
        return app(PaymentService::class)->createCheckout($user, [
            'plan_price_id' => $price->id,
            'gateway' => $gateway,
            'voucher_code' => $voucherCode,
            'amount' => 1,
            'total_amount' => 1,
        ])['payment'];
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function webhookPayload(Payment $payment, array $overrides = [], ?string $signature = null): array
    {
        $grossAmount = (string) (int) round((float) $payment->total_amount);

        $payload = array_merge([
            'order_id' => (string) $payment->gateway_order_id,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'TRX-' . strtoupper(Str::random(10)),
            'payment_type' => 'bank_transfer',
        ], $overrides);

        $source = $payload['order_id']
            . $payload['status_code']
            . $payload['gross_amount']
            . self::MIDTRANS_SERVER_KEY;

        $payload['signature_key'] = $signature ?? hash('sha512', $source);

        return $payload;
    }

    private function assertExpiresNear(mixed $expiresAt, CarbonInterface $expected, int $toleranceMinutes = 2): void
    {
        $this->assertInstanceOf(CarbonInterface::class, $expiresAt);
        $this->assertLessThanOrEqual($toleranceMinutes, $expiresAt->diffInMinutes($expected));
    }

    private function expiryHistoryCount(User $user): int
    {
        return MembershipHistory::query()
            ->where('user_id', $user->id)
            ->where('action', 'expiry')
            ->count();
    }

    private function purchaseCount(Payment $payment): int
    {
        return MembershipSubscription::query()
            ->where('payment_id', $payment->id)
            ->where('action', 'purchase')
            ->count();
    }
}
