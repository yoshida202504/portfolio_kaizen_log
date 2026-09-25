<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyRecordRequest;
use App\Http\Requests\UpdateDailyRecordRequest;
use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DailyRecordController extends Controller
{
    public function create(): View
    {
        return view('records.create');
    }

    public function show(DailyRecord $record): View
    {
        $this->authorize('view', $record);

        $record->loadCount('likes')->load([
            'comments' => fn ($query) => $query->with('user')->oldest(),
        ]);

        $canInteract = auth()->id() !== $record->user_id;
        $hasLiked = $canInteract && $record->likes()
            ->where('user_id', auth()->id())
            ->exists();

        return view('records.show', compact('record', 'canInteract', 'hasLiked'));
    }

    public function edit(DailyRecord $record): View
    {
        $this->authorize('update', $record);

        return view('records.edit', compact('record'));
    }

    public function store(StoreDailyRecordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['is_public'] = $request->boolean('is_public');
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('daily-records', 'public');
        }

        $request->user()->dailyRecords()->create($validated);

        return redirect()->route('home');
    }

    public function update(UpdateDailyRecordRequest $request, DailyRecord $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $validated = $request->validated();

        $validated['is_public'] = $request->boolean('is_public');
        unset($validated['image'], $validated['remove_image']);

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('daily-records', 'public');

            if ($record->image_path) {
                Storage::disk('public')->delete($record->image_path);
            }

            $validated['image_path'] = $newImagePath;
        } elseif ($request->boolean('remove_image')) {
            if ($record->image_path) {
                Storage::disk('public')->delete($record->image_path);
            }

            $validated['image_path'] = null;
        }

        $record->update($validated);

        return redirect()->route('records.show', $record);
    }

    public function destroy(DailyRecord $record): RedirectResponse
    {
        $this->authorize('delete', $record);

        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
        }

        $record->delete();

        return redirect()->route('home');
    }
}
