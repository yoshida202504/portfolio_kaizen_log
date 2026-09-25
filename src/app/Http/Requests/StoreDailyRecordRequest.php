<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'actions' => ['required', 'string', 'max:1000'],
            'good_points' => ['required', 'string', 'max:1000'],
            'improvement_points' => ['required', 'string', 'max:1000'],
            'improvement_strategy' => ['required', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('record_date')) {
                return;
            }

            $recordCount = $this->user()->dailyRecords()
                ->whereDate('record_date', $this->input('record_date'))
                ->count();

            if ($recordCount >= 3) {
                $validator->errors()->add('record_date', '同じ日付の日報は1日3件までです。');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'record_date.required' => '日付を入力してください。',
            'record_date.date' => '正しい日付を入力してください。',
            'record_date.before_or_equal' => '未来の日付は登録できません。',
            'actions.required' => '今日やったことを入力してください。',
            'actions.max' => '今日やったことは1000文字以内で入力してください。',
            'good_points.required' => '良かったことを入力してください。',
            'good_points.max' => '良かったことは1000文字以内で入力してください。',
            'improvement_points.required' => '改善点を入力してください。',
            'improvement_points.max' => '改善点は1000文字以内で入力してください。',
            'improvement_strategy.required' => '改善策を入力してください。',
            'improvement_strategy.max' => '改善策は1000文字以内で入力してください。',
            'is_public.boolean' => '公開設定は公開または非公開を選択してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像はJPEG、PNG、WebP形式でアップロードしてください。',
            'image.max' => '画像は5MB以下でアップロードしてください。',
        ];
    }
}
