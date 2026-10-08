<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizeOtherUserRecordInteraction(Request $request, DailyRecord $record): void
    {
        $this->authorize('view', $record);

        abort_if(
            $request->user()->id === $record->user_id,
            403,
        );
    }
}
