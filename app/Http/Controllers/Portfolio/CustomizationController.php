<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customization\SaveCustomizationRequest;
use App\Models\CustomizationSettings;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomizationController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function show(Request $request): View
    {
        $portfolio     = $this->portfolioService->getOrCreatePortfolio($request->user());
        $customization = $portfolio->customization ?? new CustomizationSettings(CustomizationSettings::$defaults);
        $items         = $portfolio->items()->where('is_visible', true)->orderBy('display_order')->get();
        return view('dashboard.customize', compact('customization', 'items'));
    }

    public function save(SaveCustomizationRequest $request): RedirectResponse
    {
        $portfolio = $this->portfolioService->getOrCreatePortfolio($request->user());
        $portfolio->customization()->updateOrCreate(
            ['portfolio_id' => $portfolio->id],
            $request->validated()
        );
        return back()->with('status', 'Customization saved.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $portfolio = $this->portfolioService->getOrCreatePortfolio($request->user());
        $portfolio->customization()->updateOrCreate(
            ['portfolio_id' => $portfolio->id],
            CustomizationSettings::$defaults
        );
        return back()->with('status', 'Customization reset to defaults.');
    }
}
