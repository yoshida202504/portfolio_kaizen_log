<?php

namespace App\Policies;

use App\Models\DailyRecord;
use App\Models\User;

class DailyRecordPolicy
{
    public function view(User $user, DailyRecord $record): bool
    {
        $recordOwner = $record->user;

        if (! $recordOwner) {
            return false;
        }

        if ($user->id === $record->user_id) {
            return true;
        }

        if ($record->is_public) {
            return true;
        }

        return $user->isMutuallyFollowing($recordOwner);
    }

    public function update(User $user, DailyRecord $record): bool
    {
        return $user->id === $record->user_id;
    }

    public function delete(User $user, DailyRecord $record): bool
    {
        return $user->id === $record->user_id;
    }
}
