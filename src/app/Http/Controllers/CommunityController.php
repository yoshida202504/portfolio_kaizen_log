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

        // Community は他のユーザーの「公開」日報だけ。相互フォローの非公開日報はユーザー詳細で見る
        $dailyRecords = DailyRecord::query()
            ->with('user')
            ->withCount('likes')
            ->whereHas('user')
            ->where('user_id', '!=', $user->id)
            ->where('is_public', true)
            ->when($request->filled('record_date'), function ($query) use ($request) {
                $query->whereDate('record_date', $request->input('record_date'));
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->keywordSearch((string) $request->input('keyword'));
            })
            ->latestRecordFirst()
            ->paginate(10)
            ->withQueryString();

        return view('community.index', compact('dailyRecords'));
    }
}
