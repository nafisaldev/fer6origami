<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required_unless:action,revoke', 'integer', 'exists:membership_plans,id'],
            'action' => ['required', 'in:grant,renew,change,revoke'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
