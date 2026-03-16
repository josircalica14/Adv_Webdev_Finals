<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShowcaseService
{
    public function getPublicPortfolios(array $criteria, int $page = 1): LengthAwarePaginator
    {
        $cacheKey = 'showcase:' . md5(serialize($criteria)) . ':page:' . $page;

        return Cache::remember($cacheKey, 300, function () use ($criteria, $page) {
            $query = Portfolio::publicWithItems()->with(['user', 'customization']);

            if (!empty($criteria['query'])) {
                $query->search($criteria['query']);
            }

            if (!empty($criteria['program'])) {
                $query->program($criteria['program']);
            }

            if (!empty($criteria['sort'])) {
                if ($criteria['sort'] === 'name') {
                    $query->join('users', 'users.id', '=', 'portfolios.user_id')
                          ->orderBy('users.full_name');
                } else {
                    $query->orderByDesc('portfolios.updated_at');
                }
            } else {
                $query->orderByDesc('portfolios.updated_at');
            }

            return $query->paginate(20, ['portfolios.*'], 'page', $page);
        });
    }

    public function getStats(): array
    {
        return Cache::remember('showcase:stats', 600, function () {
            $publicPortfolios = Portfolio::publicWithItems();
            return [
                'students'   => User::count(),
                'portfolios' => Portfolio::where('is_public', true)->count(),
                'skills'     => PortfolioItem::whereNotNull('tags')
                                    ->get()
                                    ->flatMap(fn($i) => (array) ($i->tags ?? []))
                                    ->unique()
                                    ->count(),
                'views'      => Portfolio::sum('view_count'),
            ];
        });
    }

    public function getFeatured(int $limit = 3): Collection
    {
        return Cache::remember('showcase:featured', 600, function () use ($limit) {
            return Portfolio::publicWithItems()
                ->with(['user', 'items'])
                ->orderByDesc('view_count')
                ->limit($limit)
                ->get();
        });
    }

    public function getTopSkills(int $limit = 16): Collection
    {
        return Cache::remember('showcase:top_skills', 600, function () use ($limit) {
            return PortfolioItem::whereNotNull('tags')
                ->get()
                ->flatMap(fn($i) => (array) ($i->tags ?? []))
                ->map(fn($t) => strtolower(trim($t)))
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take($limit)
                ->keys();
        });
    }

    public function getRecentPortfolios(int $limit = 6): Collection
    {
        return Cache::remember('showcase:recent', 300, function () use ($limit) {
            return Portfolio::publicWithItems()
                ->with(['user', 'items'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('showcase_version');
        Cache::put('showcase_version', now()->timestamp);
    }
}
