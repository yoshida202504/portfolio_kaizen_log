<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImprovementRecordIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'end_month' => ['nullable', 'integer', 'between:1,12', 'gte:start_month'],
            'evaluation' => ['nullable', 'in:all,executed,above_or_expected,above,below,not_executed'],
        ];
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.*' => '表示年を正しく選んでください。',
            'start_month.*' => '開始月を正しく選んでください。',
            'end_month.gte' => '終了月は開始月と同じか、それより後の月を選んでください。',
            'end_month.*' => '終了月を正しく選んでください。',
            'evaluation.*' => '評価を正しく選んでください。',
        ];
    }
}
