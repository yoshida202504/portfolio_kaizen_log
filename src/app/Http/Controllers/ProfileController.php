<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\WithdrawAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->ensureNotGuest($request);

        $request->user()->update($request->validated());

        return redirect()->route('mypage')->with('success', 'プロフィールを更新しました。');
    }

    public function destroy(WithdrawAccountRequest $request): RedirectResponse
    {
        $this->ensureNotGuest($request);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * The guest account is shared by every reviewer, so its profile must stay intact.
     */
    private function ensureNotGuest(Request $request): void
    {
        abort_if($request->user()->isGuest(), 403, 'ゲストユーザーはプロフィールの変更と退会ができません。');
    }
}
