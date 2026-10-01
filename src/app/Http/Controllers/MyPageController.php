<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
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

        $improvementRecords = $user->dailyRecords()
            ->whereNotNull('improvement_rate')
            ->orderBy('record_date')
            ->orderBy('created_at')
            ->get();

        $chartWidth = 600;
        $chartHeight = 260;
        $chartPadding = 44;
        $chartLabelY = $chartHeight - 12;
        $chartPointCount = $improvementRecords->count();
        $chartLabelInterval = max(1, (int) ceil($chartPointCount / 6));
        $chartPoints = $improvementRecords->values()->map(function ($record, int $index) use ($chartPointCount, $chartWidth, $chartHeight, $chartPadding, $chartLabelInterval) {
            $x = $chartPointCount === 1
                ? $chartWidth / 2
                : $chartPadding + ($index * (($chartWidth - ($chartPadding * 2)) / ($chartPointCount - 1)));
            $y = $chartPadding + ((100 - (int) $record->improvement_rate) / 100 * ($chartHeight - ($chartPadding * 2)));

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'rate' => (int) $record->improvement_rate,
                'record_date' => $record->record_date,
                'show_date_label' => $index === 0 || $index === $chartPointCount - 1 || $index % $chartLabelInterval === 0,
            ];
        });

        return view('mypage.index', compact(
            'user',
            'following',
            'followers',
            'likedRecords',
            'improvementRecords',
            'chartPoints',
            'chartLabelY',
            'chartWidth',
            'chartHeight',
            'chartPadding',
        ));
    }
}
