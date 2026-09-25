<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function isImprovementInputAvailable(): bool
    {
        $today = now(config('app.timezone'))->startOfDay();
        $deadline = Carbon::parse($this->record_date, config('app.timezone'))
            ->startOfDay()
            ->addDays(7);

        return $today->lessThanOrEqualTo($deadline);
    }
}
