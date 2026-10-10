<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            ->paginate(10);

        return view('home.index', [
            'dailyRecords' => $dailyRecords,
            'pendingImprovementRecords' => $this->pendingImprovementRecords($user),
            'monthlyRecordCount' => $this->monthlyRecordCount($user),
        ]);
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
            ->paginate(10)
            ->withQueryString();

        return view('home.index', [
            'dailyRecords' => $dailyRecords,
            'pendingImprovementRecords' => $this->pendingImprovementRecords($user),
            'monthlyRecordCount' => $this->monthlyRecordCount($user),
        ]);
    }

    private function monthlyRecordCount(User $user): int
    {
        $now = now(config('app.timezone'));

        return $user->dailyRecords()
            ->whereBetween('record_date', [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()])
            ->count();
    }

    private function pendingImprovementRecords(User $user)
    {
        return $user->pendingImprovementRecords()
            ->latest('created_at')
            ->get();
    }
}
