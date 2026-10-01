<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Services\ImprovementTrendChart;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyPageController extends Controller
{
    public function __construct(
        private readonly ImprovementTrendChart $improvementTrendChart,
    ) {}

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

        $improvementRecords = $user->dailyRecords()
            ->whereNotNull('improvement_rate')
            ->orderBy('record_date')
            ->orderBy('created_at')
            ->get();
        $chart = $this->improvementTrendChart->build($improvementRecords);

        return view('mypage.index', [
            'user' => $user,
            'following' => $following,
            'followers' => $followers,
            'likedRecords' => $likedRecords,
            'improvementRecords' => $improvementRecords,
            'chartPoints' => $chart['points'],
            'chartLabelY' => $chart['labelY'],
            'chartWidth' => $chart['width'],
            'chartHeight' => $chart['height'],
            'chartPadding' => $chart['padding'],
        ]);
    }
}
