<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $this->ensureNotSelf($request->user(), $user);

        $request->user()->following()->firstOrCreate([
            'followed_id' => $user->id,
        ]);

        return redirect()->route('users.show', $user);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureNotSelf($request->user(), $user);

        $request->user()->following()
            ->where('followed_id', $user->id)
            ->delete();

        return redirect()->route('users.show', $user);
    }

    private function ensureNotSelf(User $currentUser, User $user): void
    {
        abort_if($currentUser->is($user), 403, '自分自身をフォローすることはできません。');
    }
}
