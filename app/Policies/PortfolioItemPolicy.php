<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

class PortfolioItemPolicy
{
    public function update(User $user, PortfolioItem $item): bool
    {
        return $user->id === $item->portfolio->user_id;
    }

    public function delete(User $user, PortfolioItem $item): bool
    {
        return $user->id === $item->portfolio->user_id;
    }

    public function toggleVisibility(User $user, PortfolioItem $item): bool
    {
        return $user->id === $item->portfolio->user_id;
    }
}
