<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $price = $this->route('membershipPrice');
        $planId = $price?->plan_id ?: $this->route('membershipPlan')?->id;

        return [
            'duration_days' => [
                'required',
                'integer',
                'min:1',
                'max:3650',
                Rule::unique('membership_plan_prices', 'duration_days')
                    ->where(fn ($query) => $query->where('plan_id', $planId))
                    ->ignore($price?->id),
            ],
            'label' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
