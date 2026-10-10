<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestLoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $guest = User::query()->where('email', User::GUEST_EMAIL)->first();

        if (! $guest) {
            return redirect()->route('login')->withErrors([
                'guest' => 'ゲスト用のアカウントがまだ作成されていません。php artisan db:seed を実行してください。',
            ]);
        }

        Auth::login($guest);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'ゲストユーザーとしてログインしました。プロフィールの変更と退会はできません。');
    }
}
