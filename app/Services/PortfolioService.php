<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PortfolioService
{
    public function __construct(private FileStorageService $fileStorage) {}

    public function getOrCreatePortfolio(User $user): Portfolio
    {
        return Portfolio::firstOrCreate(['user_id' => $user->id]);
    }

    public function toggleVisibility(User $user, bool $isPublic): Portfolio
    {
        $portfolio = $this->getOrCreatePortfolio($user);
        $portfolio->update(['is_public' => $isPublic]);
        app(ShowcaseService::class)->invalidateCache();
        return $portfolio->fresh();
    }

    public function createItem(User $user, array $data): PortfolioItem
    {
        $portfolio = $this->getOrCreatePortfolio($user);
        $maxOrder = $portfolio->items()->max('display_order') ?? 0;
        $item = $portfolio->items()->create(array_merge($data, ['display_order' => $maxOrder + 1]));
        app(ShowcaseService::class)->invalidateCache();
        return $item;
    }

    public function updateItem(PortfolioItem $item, array $data): PortfolioItem
    {
        $item->update($data);
        return $item->fresh();
    }

    public function deleteItem(PortfolioItem $item): void
    {
        $this->fileStorage->deleteForItem($item);
        $item->delete();
        app(ShowcaseService::class)->invalidateCache();
    }

    public function reorderItems(Portfolio $portfolio, array $orderedIds): void
    {
        // Verify all IDs belong to this portfolio
        $portfolioItemIds = $portfolio->items()->pluck('id')->toArray();
        foreach ($orderedIds as $id) {
            if (!in_array($id, $portfolioItemIds)) {
                throw new \InvalidArgumentException("Item {$id} does not belong to this portfolio.");
            }
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $order => $id) {
                PortfolioItem::where('id', $id)->update(['display_order' => $order + 1]);
            }
        });
    }

    public function toggleItemVisibility(PortfolioItem $item): PortfolioItem
    {
        $item->update(['is_visible' => !$item->is_visible]);
        return $item->fresh();
    }

    public function incrementViewCount(Portfolio $portfolio): void
    {
        $portfolio->increment('view_count');
    }
}
