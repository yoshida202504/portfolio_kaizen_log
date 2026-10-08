<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $currentUser = $request->user();
        $isOwnProfile = $currentUser->is($user);
        $isFollowing = ! $isOwnProfile && $currentUser->isFollowing($user);

        $dailyRecords = $isOwnProfile
            ? collect()
            : $user->dailyRecords()
                ->visibleTo($currentUser)
                ->withCount('likes')
                ->latestRecordFirst()
                ->paginate(10);

        return view('users.show', compact('user', 'isOwnProfile', 'isFollowing', 'dailyRecords'));
    }
}
