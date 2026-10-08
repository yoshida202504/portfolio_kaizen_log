<?php

namespace App\Http\Requests;

use App\Services\CommentSafetyChecker;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class CommentBodyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'コメントを入力してください。',
            'body.max' => 'コメントは1000文字以内で入力してください。',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $body = $this->input('body');

            if (! is_string($body) || $validator->errors()->has('body')) {
                return;
            }

            if (app(CommentSafetyChecker::class)->mayHarmUsers($body)) {
                $validator->errors()->add(
                    'body',
                    'このコメントは、ユーザーを傷つける可能性があるため投稿できません。',
                );
            }
        }];
    }

    protected function getRedirectUrl()
    {
        $record = $this->route('record') ?? $this->route('comment')->dailyRecord;

        return route('records.show', $record).'#comments';
    }
}
