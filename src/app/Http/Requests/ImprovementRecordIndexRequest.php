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
}
