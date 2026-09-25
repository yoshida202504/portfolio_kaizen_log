<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateImprovementRequest;
use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImprovementController extends Controller
{
    public function edit(DailyRecord $record): View
    {
        $this->authorize('update', $record);
        $this->ensureImprovementInputIsAvailable($record);

        return view('records.improvement', compact('record'));
    }

    public function update(UpdateImprovementRequest $request, DailyRecord $record): RedirectResponse
    {
        $this->authorize('update', $record);
        $this->ensureImprovementInputIsAvailable($record);

        $record->update($request->validated());

        return redirect()->route('records.show', $record);
    }

    private function ensureImprovementInputIsAvailable(DailyRecord $record): void
    {
        abort_unless(
            $record->isImprovementInputAvailable(),
            403,
            '改善結果の入力期限を過ぎています。',
        );
    }
}
