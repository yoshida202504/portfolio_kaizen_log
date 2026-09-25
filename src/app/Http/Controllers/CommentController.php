<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, DailyRecord $record): RedirectResponse
    {
        $this->authorizeOtherUserRecordInteraction($request, $record);

        $request->user()->comments()->create([
            'daily_record_id' => $record->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()->route('records.show', $record);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return redirect()->route('records.show', $comment->dailyRecord);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $record = $comment->dailyRecord;
        $comment->delete();

        return redirect()->route('records.show', $record);
    }
}
