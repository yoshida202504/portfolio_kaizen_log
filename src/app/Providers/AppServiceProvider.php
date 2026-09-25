<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Policies\CommentPolicy;
use App\Policies\DailyRecordPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(DailyRecord::class, DailyRecordPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}
