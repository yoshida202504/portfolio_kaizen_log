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
        $canFollow = ! $currentUser->isGuest() && ! $user->isGuest();

        $dailyRecords = $isOwnProfile
            ? collect()
            : $user->dailyRecords()
                ->visibleTo($currentUser)
                ->withCount('likes')
                ->latestRecordFirst()
                ->paginate(10);

        // フォロー中・フォロワーの一覧は、相互フォローのときだけ取り出す
        $isMutual = ! $isOwnProfile && $currentUser->isMutuallyFollowing($user);

        $following = $isMutual
            ? $user->following()->with('followed')->whereHas('followed')->latest('created_at')->get()
            : collect();

        $followers = $isMutual
            ? $user->followers()->with('follower')->whereHas('follower')->latest('created_at')->get()
            : collect();

        return view('users.show', compact('user', 'isOwnProfile', 'isFollowing', 'canFollow', 'isMutual', 'following', 'followers', 'dailyRecords'));
    }
}
