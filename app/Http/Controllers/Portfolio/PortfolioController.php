<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function publicView(string $username): View
    {
        $user      = User::where('username', $username)->firstOrFail();
        $portfolio = $user->portfolio;

        if (!$portfolio || !$portfolio->is_public) {
            // Show a friendly message if the owner is viewing their own private portfolio
            if (auth()->check() && auth()->id() === $user->id) {
                return view('portfolio.private', compact('user'));
            }
            abort(404);
        }

        if (!auth()->check()) {
            $this->portfolioService->incrementViewCount($portfolio);
        }

        $items         = $portfolio->items()->where('is_visible', true)->with('files')->get();
        $customization = $portfolio->customization;

        return view('portfolio.view', compact('user', 'portfolio', 'items', 'customization'));
    }

    public function toggleVisibility(Request $request): RedirectResponse
    {
        $request->validate(['is_public' => 'required|boolean']);
        $this->portfolioService->toggleVisibility($request->user(), $request->boolean('is_public'));
        return back()->with('status', 'Portfolio visibility updated.');
    }
}
