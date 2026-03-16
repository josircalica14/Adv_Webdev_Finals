<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class ProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        $user->update(array_merge($data, ['name' => $data['full_name']]));
        return $user->fresh();
    }

    public function changeUsername(User $user, string $newUsername): User
    {
        $days = $this->getDaysUntilUsernameChange($user);
        if ($days > 0) {
            throw new \RuntimeException("You must wait {$days} more day(s) before changing your username.");
        }

        $user->update([
            'username'             => $newUsername,
            'last_username_change' => now(),
        ]);

        return $user->fresh();
    }

    public function getDaysUntilUsernameChange(User $user): int
    {
        if (!$user->last_username_change) {
            return 0;
        }

        $nextAllowed = $user->last_username_change->addDays(30);
        if (now()->greaterThanOrEqualTo($nextAllowed)) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($nextAllowed, false));
    }
}
