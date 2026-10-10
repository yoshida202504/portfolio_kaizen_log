<?php

namespace App\Http\Requests;

use App\Models\ImprovementRecord;
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
            'evaluation' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'executed_at' => ['nullable', 'date', 'before_or_equal:today', 'required_if:evaluation,A,B,C'],
            'actual_result' => ['nullable', 'string', 'max:1000', 'required_if:evaluation,A,B,C'],
            'not_executed_reason' => ['nullable', Rule::in(ImprovementRecord::NOT_EXECUTED_REASONS), 'required_if:evaluation,D'],
            'not_executed_note' => ['nullable', 'string', 'max:1000', 'required_if:not_executed_reason,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'evaluation.required' => '評価を選択してください。',
            'evaluation.in' => '評価はA、B、C、Dから選択してください。',
            'executed_at.required_if' => '実施日を入力してください。',
            'executed_at.date' => '正しい実施日を入力してください。',
            'executed_at.before_or_equal' => '実施日は未来日にできません。',
            'actual_result.required_if' => '実際の結果を入力してください。',
            'actual_result.max' => '実際の結果は1000文字以内で入力してください。',
            'not_executed_reason.required_if' => '未実施の理由を選択してください。',
            'not_executed_reason.in' => '未実施の理由を正しく選択してください。',
            'not_executed_note.required_if' => 'その他の理由を入力してください。',
            'not_executed_note.max' => 'その他の理由は1000文字以内で入力してください。',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function improvementRecordAttributes(): array
    {
        $validated = $this->validated();

        if ($validated['evaluation'] === 'D') {
            return [
                'execution_status' => ImprovementRecord::STATUS_NOT_EXECUTED,
                'result_evaluation' => null,
                'executed_at' => null,
                'actual_result' => null,
                'not_executed_reason' => $validated['not_executed_reason'],
                'not_executed_note' => $validated['not_executed_reason'] === 'other'
                    ? $validated['not_executed_note']
                    : null,
            ];
        }

        return [
            'execution_status' => ImprovementRecord::STATUS_EXECUTED,
            'result_evaluation' => $validated['evaluation'],
            'executed_at' => $validated['executed_at'],
            'actual_result' => $validated['actual_result'],
            'not_executed_reason' => null,
            'not_executed_note' => null,
        ];
    }
}
