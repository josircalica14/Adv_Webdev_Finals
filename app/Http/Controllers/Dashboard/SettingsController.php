<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function show(Request $request): View
    {
        $portfolio = $this->portfolioService->getOrCreatePortfolio($request->user());
        return view('dashboard.settings', compact('portfolio'));
    }

    public function updateVisibility(Request $request): RedirectResponse
    {
        $request->validate(['is_public' => 'nullable|boolean']);
        $this->portfolioService->toggleVisibility($request->user(), $request->boolean('is_public'));
        return back()->with('status', 'Visibility updated.');
    }
}
