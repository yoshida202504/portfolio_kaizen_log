<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $dailyRecords = DailyRecord::query()
            ->with('user')
            ->withCount('likes')
            ->whereHas('user')
            ->where('user_id', '!=', $user->id)
            ->visibleTo($user)
            ->latestRecordFirst()
            ->paginate(10);

        return view('community.index', compact('dailyRecords'));
    }
}
