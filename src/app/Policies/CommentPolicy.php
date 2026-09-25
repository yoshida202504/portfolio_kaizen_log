<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $this->canManage($user, $comment);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->canManage($user, $comment);
    }

    private function canManage(User $user, Comment $comment): bool
    {
        if ($user->id !== $comment->user_id || ! $comment->dailyRecord) {
            return false;
        }

        return Gate::forUser($user)->allows('view', $comment->dailyRecord);
    }
}
