<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.image' => 'Bukti transfer harus berupa gambar.',
            'proof.max' => 'Ukuran bukti transfer maksimal 2 MB.',
        ];
    }
}
