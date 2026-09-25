<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateImprovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'improvement_result' => ['nullable', 'string', 'max:1000'],
            'improvement_rate' => ['nullable', 'integer', Rule::in([0, 20, 40, 60, 80, 100])],
        ];
    }

    public function messages(): array
    {
        return [
            'improvement_result.max' => '改善結果は1000文字以内で入力してください。',
            'improvement_rate.integer' => '改善率は整数で選択してください。',
            'improvement_rate.in' => '改善率は0%、20%、40%、60%、80%、100%から選択してください。',
        ];
    }
}
