<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_price_id' => ['required', 'integer', 'exists:membership_plan_prices,id'],
            'gateway' => ['required', 'in:midtrans,manual'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_price_id.required' => 'Pilih durasi paket terlebih dahulu.',
            'plan_price_id.exists' => 'Durasi paket tidak valid.',
            'gateway.in' => 'Metode pembayaran tidak didukung.',
        ];
    }
}
