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
            ->whereHas('user')
            ->where('user_id', '!=', $user->id)
            ->visibleTo($user)
            ->latestRecordFirst()
            ->get();

        return view('community.index', compact('dailyRecords'));
    }
}
