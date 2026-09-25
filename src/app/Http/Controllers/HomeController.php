<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $dailyRecords = $user->dailyRecords()
            ->latestRecordFirst()
            ->get();

        return view('home.index', compact('dailyRecords'));
    }

    public function search(Request $request): View
    {
        $user = Auth::user();

        $dailyRecords = $user->dailyRecords()
            ->when($request->filled('record_date'), function ($query) use ($request) {
                $query->whereDate('record_date', $request->input('record_date'));
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->input('keyword');

                $query->where(function ($query) use ($keyword) {
                    $query->where('actions', 'like', "%{$keyword}%")
                        ->orWhere('good_points', 'like', "%{$keyword}%")
                        ->orWhere('improvement_points', 'like', "%{$keyword}%")
                        ->orWhere('improvement_strategy', 'like', "%{$keyword}%");
                });
            })
            ->latestRecordFirst()
            ->get();

        return view('home.index', compact('dailyRecords'));
    }
}
