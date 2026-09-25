<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirm_withdrawal' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_withdrawal.accepted' => '退会することへの同意が必要です。',
        ];
    }
}
