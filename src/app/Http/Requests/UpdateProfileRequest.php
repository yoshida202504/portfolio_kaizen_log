<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', Rule::unique('users', 'name')->ignore($this->user()->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'age' => ['nullable', 'integer', 'between:15,99'],
            'gender' => ['required', 'in:男性,女性,回答しない'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ユーザー名を入力してください。',
            'name.max' => 'ユーザー名は30文字以内で入力してください。',
            'name.unique' => 'このユーザー名はすでに使用されています。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスはすでに使用されています。',
            'age.integer' => '年齢は整数で入力してください。',
            'age.between' => '年齢は15歳から99歳の間で入力してください。',
            'gender.required' => '性別を選択してください。',
            'gender.in' => '正しい性別を選択してください。',
        ];
    }
}
