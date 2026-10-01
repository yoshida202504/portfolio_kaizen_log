<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyRecordRequest;
use App\Http\Requests\UpdateDailyRecordRequest;
use App\Models\DailyRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DailyRecordController extends Controller
{
    public function create(): View
    {
        return view('records.create');
    }

    public function show(Request $request, DailyRecord $record): View
    {
        $this->authorize('view', $record);

        $record->loadCount('likes')->load([
            'comments' => fn ($query) => $query->with('user')->oldest(),
        ]);

        $canInteract = auth()->id() !== $record->user_id;
        $hasLiked = $canInteract && $record->likes()
            ->where('user_id', auth()->id())
            ->exists();
        [$returnUrl, $returnLabel] = $this->returnLink($request, $record);

        return view('records.show', compact('record', 'canInteract', 'hasLiked', 'returnUrl', 'returnLabel'));
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

        return redirect()->route('home')->with('success', '日報を登録しました。');
    }

    public function update(UpdateDailyRecordRequest $request, DailyRecord $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $validated = $request->validated();

        $validated['is_public'] = $request->boolean('is_public');
        unset($validated['image'], $validated['remove_image']);

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('daily-records', 'public');

            $this->deleteImage($record);
            $validated['image_path'] = $newImagePath;
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($record);
            $validated['image_path'] = null;
        }

        $record->update($validated);

        return redirect()->route('records.show', $record)->with('success', '日報を更新しました。');
    }

    public function destroy(DailyRecord $record): RedirectResponse
    {
        $this->authorize('delete', $record);

        $this->deleteImage($record);
        $record->delete();

        return redirect()->route('home')->with('success', '日報を削除しました。');
    }

    /**
     * Build a return link from an allowlisted internal list source only.
     *
     * @return array{0: string, 1: string}
     */
    private function returnLink(Request $request, DailyRecord $record): array
    {
        $source = $request->input('source');
        $page = max(1, $request->integer('page', 1));
        $fragment = 'record-'.$record->id;

        if ($source === 'home') {
            return [route('home', ['page' => $page]).'#'.$fragment, '日報一覧へ戻る'];
        }

        if ($source === 'community') {
            return [route('community.index', ['page' => $page]).'#'.$fragment, 'Communityへ戻る'];
        }

        if ($source === 'user' && $request->integer('user') === $record->user_id) {
            return [
                route('users.show', ['user' => $record->user_id, 'page' => $page]).'#'.$fragment,
                'ユーザー詳細へ戻る',
            ];
        }

        if ($source === 'search') {
            $filters = ['page' => $page];

            foreach (['record_date', 'keyword'] as $filter) {
                $value = $request->input($filter);

                if (is_string($value) && $value !== '') {
                    $filters[$filter] = $value;
                }
            }

            return [route('records.search', $filters).'#'.$fragment, '検索結果へ戻る'];
        }

        return [route('home'), '日報一覧へ戻る'];
    }

    private function deleteImage(DailyRecord $record): void
    {
        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
        }
    }
}
