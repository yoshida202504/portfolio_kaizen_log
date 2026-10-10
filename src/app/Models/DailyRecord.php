<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DailyRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'record_date',
        'actions',
        'good_points',
        'improvement_points',
        'improvement_strategy',
        'expected_result',
        'improvement_result',
        'improvement_rate',
        'is_public',
        'image_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function improvementRecord(): HasOne
    {
        return $this->hasOne(ImprovementRecord::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (DailyRecord $record): void {
            if (! $record->isForceDeleting()) {
                $record->improvementRecord()->delete();
            }
        });
    }

    public function improvementDeadline(): Carbon
    {
        return $this->created_at
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->startOfDay()
            ->addDays(6)
            ->endOfDay();
    }

    public function isImprovementInputAvailable(): bool
    {
        return now(config('app.timezone'))->lessThanOrEqualTo($this->improvementDeadline());
    }

    public function isImprovementReflectionDue(): bool
    {
        $today = now(config('app.timezone'))->startOfDay();
        $notificationStartsAt = $this->created_at
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->startOfDay()
            ->addDays(5);

        if ($today->lessThan($notificationStartsAt) || ! $this->isImprovementInputAvailable()) {
            return false;
        }

        return $this->relationLoaded('improvementRecord')
            ? is_null($this->getRelation('improvementRecord'))
            : ! $this->improvementRecord()->exists();
    }

    public function scopeImprovementReflectionDue(Builder $query): Builder
    {
        $now = now(config('app.timezone'));

        return $query
            ->whereBetween('created_at', [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->subDays(5)->endOfDay(),
            ])
            ->whereDoesntHave('improvementRecord');
    }

    /**
     * Search body fields by keyword, treating % and _ as literal characters.
     * The escape character is "!" because MySQL and SQLite handle a backslash differently.
     */
    public function scopeKeywordSearch(Builder $query, string $keyword): Builder
    {
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $keyword).'%';

        return $query->where(function (Builder $query) use ($pattern): void {
            foreach (['actions', 'good_points', 'improvement_points', 'improvement_strategy'] as $column) {
                $query->orWhereRaw("{$column} like ? escape '!'", [$pattern]);
            }
        });
    }

    public function scopeLatestRecordFirst(Builder $query): Builder
    {
        return $query->orderByDesc('record_date')->orderByDesc('created_at');
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        $mutuallyFollowedUserIds = $viewer->mutuallyFollowingUserIds();

        return $query->where(function (Builder $query) use ($mutuallyFollowedUserIds) {
            $query->where('is_public', true);

            if ($mutuallyFollowedUserIds->isNotEmpty()) {
                $query->orWhereIn('user_id', $mutuallyFollowedUserIds);
            }
        });
    }
}
