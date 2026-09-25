<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, DailyRecord $record): RedirectResponse
    {
        $this->authorizeInteraction($request, $record);

        $request->user()->likes()->firstOrCreate([
            'daily_record_id' => $record->id,
        ]);

        return redirect()->route('records.show', $record);
    }

    public function destroy(Request $request, DailyRecord $record): RedirectResponse
    {
        $this->authorizeInteraction($request, $record);

        $request->user()->likes()
            ->where('daily_record_id', $record->id)
            ->delete();

        return redirect()->route('records.show', $record);
    }

    private function authorizeInteraction(Request $request, DailyRecord $record): void
    {
        $this->authorize('view', $record);

        abort_if($request->user()->id === $record->user_id, 403);
    }
}
