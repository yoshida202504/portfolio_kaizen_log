<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, DailyRecord $record): RedirectResponse
    {
        $this->authorizeOtherUserRecordInteraction($request, $record);

        $like = $request->user()->likes()->withTrashed()->firstOrCreate([
            'daily_record_id' => $record->id,
        ]);

        if ($like->trashed()) {
            $like->restore();
        }

        return redirect()->route('records.show', $record)->withFragment('like-section');
    }

    public function destroy(Request $request, DailyRecord $record): RedirectResponse
    {
        $this->authorizeOtherUserRecordInteraction($request, $record);

        $request->user()->likes()
            ->where('daily_record_id', $record->id)
            ->first()?->delete();

        return redirect()->route('records.show', $record)->withFragment('like-section');
    }
}
