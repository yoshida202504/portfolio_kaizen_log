<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Models\ImprovementRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $following = $user->following()
            ->with('followed')
            ->whereHas('followed')
            ->latest('created_at')
            ->get();

        $followers = $user->followers()
            ->with('follower')
            ->whereHas('follower')
            ->latest('created_at')
            ->get();

        $likedRecords = DailyRecord::query()
            ->with('user')
            ->whereHas('user')
            ->where('daily_records.user_id', '!=', $user->id)
            ->whereHas('likes', fn ($query) => $query->where('user_id', $user->id))
            ->visibleTo($user)
            ->latestRecordFirst()
            ->get();

        $currentMonthStart = now(config('app.timezone'))->startOfMonth()->toDateString();
        $currentMonthEnd = now(config('app.timezone'))->endOfMonth()->toDateString();
        $monthlyImprovementActionCount = ImprovementRecord::query()
            ->where('execution_status', ImprovementRecord::STATUS_EXECUTED)
            ->whereIn('result_evaluation', ImprovementRecord::EVALUATIONS)
            ->whereBetween('executed_at', [$currentMonthStart, $currentMonthEnd])
            ->whereHas('dailyRecord', fn ($query) => $query->where('user_id', $user->id))
            ->count();
        $pendingImprovementCount = $user->pendingImprovementRecords()->count();

        return view('mypage.index', [
            'user' => $user,
            'following' => $following,
            'followers' => $followers,
            'likedRecords' => $likedRecords,
            'monthlyImprovementActionCount' => $monthlyImprovementActionCount,
            'pendingImprovementCount' => $pendingImprovementCount,
        ]);
    }
}
