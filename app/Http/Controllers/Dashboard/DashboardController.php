<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

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

        return view('dashboard.index', compact('user', 'portfolio', 'stats'));
    }
}
