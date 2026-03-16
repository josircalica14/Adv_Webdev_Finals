<?php

namespace App\Services;

use App\Mail\AdminNotificationMail;
use App\Models\AdminAction;
use App\Models\FlaggedContent;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class AdminService
{
    public function getAllPortfolios(int $page = 1): LengthAwarePaginator
    {
        return Portfolio::with('user')
            ->orderByDesc('updated_at')
            ->paginate(20, ['*'], 'page', $page);
    }

    public function flagItem(PortfolioItem $item, User $admin, string $reason): FlaggedContent
    {
        $flag = FlaggedContent::create([
            'portfolio_item_id' => $item->id,
            'flagged_by'        => $admin->id,
            'reason'            => $reason,
            'status'            => 'pending',
        ]);

        $this->logAction($admin, 'flag', 'portfolio_item', $item->id, ['reason' => $reason]);

        return $flag;
    }

    public function hideItem(PortfolioItem $item, User $admin): void
    {
        $item->update(['is_visible' => false]);

        FlaggedContent::where('portfolio_item_id', $item->id)
            ->where('status', 'pending')
            ->update(['status' => 'reviewed', 'reviewed_at' => now()]);

        $this->logAction($admin, 'hide', 'portfolio_item', $item->id);
    }

    public function unhideItem(PortfolioItem $item, User $admin): void
    {
        $item->update(['is_visible' => true]);

        FlaggedContent::where('portfolio_item_id', $item->id)
            ->update(['status' => 'resolved', 'reviewed_at' => now()]);

        $this->logAction($admin, 'unhide', 'portfolio_item', $item->id);
    }

    public function sendNotification(User $target, User $admin, string $subject, string $message): void
    {
        Mail::to($target->email)->queue(new AdminNotificationMail($target, $subject, $message));
        $this->logAction($admin, 'notify', 'user', $target->id, ['subject' => $subject]);
    }

    public function getFlaggedContent(string $status = 'all'): Collection
    {
        $query = FlaggedContent::with(['portfolioItem', 'flaggedBy']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function logAction(User $admin, string $actionType, string $targetType, int $targetId, array $details = []): AdminAction
    {
        return AdminAction::create([
            'admin_id'    => $admin->id,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details ?: null,
        ]);
    }
}
