<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'age',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dailyRecords(): HasMany
    {
        return $this->hasMany(DailyRecord::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    public function isMutuallyFollowing(User $user): bool
    {
        return $this->following()
            ->where('followed_id', $user->id)
            ->exists()
            && $user->following()
                ->where('followed_id', $this->id)
                ->exists();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()
            ->where('followed_id', $user->id)
            ->exists();
    }

    public function mutuallyFollowingUserIds(): Collection
    {
        return $this->following()
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('follows as reverse_follows')
                    ->whereColumn('reverse_follows.follower_id', 'follows.followed_id')
                    ->where('reverse_follows.followed_id', $this->id);
            })
            ->pluck('followed_id');
    }
}
