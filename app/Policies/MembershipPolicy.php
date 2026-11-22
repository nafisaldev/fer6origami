<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Database\Eloquent\Model;

class MembershipPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('membership.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasPermission('membership.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('membership.manage');
    }

    public function managePlans(User $user): bool
    {
        return $user->hasPermission('membership.plans');
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->hasPermission('membership.payments');
    }
}
