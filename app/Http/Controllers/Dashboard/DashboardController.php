<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AiScoreService;
use App\Services\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private AiScoreService $aiScoreService,
    ) {}

    public function index(Request $request): View
    {
        $user      = $request->user();
        $portfolio = $this->portfolioService->getOrCreatePortfolio($user);
        $portfolio->load('items');

        $stats = [
            'total_items'   => $portfolio->items->count(),
            'projects'      => $portfolio->items->where('item_type', 'project')->count(),
            'achievements'  => $portfolio->items->where('item_type', 'achievement')->count(),
            'view_count'    => $portfolio->view_count,
        ];

        $score = $this->calcScore($user, $portfolio);

        return view('dashboard.index', compact('user', 'portfolio', 'stats', 'score'));
    }

    public function aiFeedback(Request $request): JsonResponse
    {
        $user      = $request->user();
        $portfolio = $this->portfolioService->getOrCreatePortfolio($user);
        $portfolio->load('items');

        $tags = $portfolio->items->flatMap(fn($i) => $i->tags ?? [])->unique()->values()->join(', ');

        $data = [
            'name'       => $user->full_name,
            'program'    => $user->program ?? 'Not set',
            'bio'        => $user->bio ?? 'Not set',
            'skills'     => $tags ?: 'None listed',
            'item_count' => $portfolio->items->count(),
            'items'      => $portfolio->items->map(fn($i) => [
                'type'        => $i->item_type,
                'title'       => $i->title,
                'description' => \Str::limit($i->description, 120),
            ])->toArray(),
        ];

        $feedback = $this->aiScoreService->getFeedback($data);

        return response()->json($feedback);
    }

    private function calcScore($user, $portfolio): array
    {
        $checks = [
            ['label' => 'Profile photo uploaded',    'points' => 15, 'done' => (bool) $user->profile_photo_path],
            ['label' => 'Bio written',               'points' => 15, 'done' => !empty($user->bio)],
            ['label' => 'Username set',              'points' => 10, 'done' => !empty($user->username)],
            ['label' => 'Program / course set',      'points' => 10, 'done' => !empty($user->program)],
            ['label' => '3 or more portfolio items', 'points' => 20, 'done' => $portfolio->items->count() >= 3],
            ['label' => 'Items have tags',           'points' => 10, 'done' => $portfolio->items->contains(fn($i) => !empty($i->tags))],
            ['label' => 'Items have links',          'points' => 10, 'done' => $portfolio->items->contains(fn($i) => !empty($i->links))],
            ['label' => 'Portfolio is public',       'points' => 10, 'done' => (bool) $portfolio->is_public],
        ];

        $total = collect($checks)->where('done', true)->sum('points');

        return ['total' => $total, 'checks' => $checks];
    }
}
