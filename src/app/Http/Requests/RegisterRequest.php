<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', 'unique:users,name'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'age' => ['nullable', 'integer', 'between:15,99'],
            'gender' => ['required', 'in:男性,女性,回答しない'],
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
            'name.required' => 'ユーザー名を入力してください。',
            'name.string' => 'ユーザー名は文字列で入力してください。',
            'name.max' => 'ユーザー名は30文字以内で入力してください。',
            'name.unique' => 'このユーザー名はすでに使用されています。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスはすでに使用されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワード確認が一致しません。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.password.mixed' => 'パスワードには英大文字と英小文字をそれぞれ1文字以上含めてください。',
            'password.password.numbers' => 'パスワードには数字を1文字以上含めてください。',
            'age.integer' => '年齢は整数で入力してください。',
            'age.between' => '年齢は15歳から99歳の間で入力してください。',
            'gender.required' => '性別を選択してください。',
            'gender.in' => '正しい性別を選択してください。',
        ];
    }
}
