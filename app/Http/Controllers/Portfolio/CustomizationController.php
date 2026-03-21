<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customization\SaveCustomizationRequest;
use App\Models\CustomizationSettings;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function preview(Request $request): Response
    {
        $user      = $request->user();
        $portfolio = $this->portfolioService->getOrCreatePortfolio($user);

        $saved = $portfolio->customization ?? new CustomizationSettings(CustomizationSettings::$defaults);

        $customization = new CustomizationSettings([
            'primary_color'    => $request->get('primary_color',    $saved->primary_color),
            'accent_color'     => $request->get('accent_color',     $saved->accent_color),
            'heading_font'     => $request->get('heading_font',     $saved->heading_font),
            'body_font'        => $request->get('body_font',        $saved->body_font),
            'theme'            => $request->get('theme',            $saved->theme),
            'layout'           => $request->get('layout',           $saved->layout),
            'font_size'        => $request->get('font_size',        $saved->font_size        ?? 'medium'),
            'spacing'          => $request->get('spacing',          $saved->spacing          ?? 'normal'),
            'header_style'     => $request->get('header_style',     $saved->header_style     ?? 'dark'),
            'show_email'       => $request->has('show_email')
                ? filter_var($request->get('show_email'), FILTER_VALIDATE_BOOLEAN)
                : false,
            'show_username'    => $request->has('show_username')
                ? filter_var($request->get('show_username'), FILTER_VALIDATE_BOOLEAN)
                : false,
            'show_bio'         => $request->has('show_bio')
                ? filter_var($request->get('show_bio'), FILTER_VALIDATE_BOOLEAN)
                : false,
            'visible_sections' => $request->has('visible_sections')
                ? $request->get('visible_sections')
                : ($saved->visible_sections ?? CustomizationSettings::$defaults['visible_sections']),
            'section_order'    => $request->has('section_order')
                ? (is_array($request->get('section_order'))
                    ? $request->get('section_order')
                    : explode(',', $request->get('section_order')))
                : ($saved->section_order ?? CustomizationSettings::$defaults['section_order']),
        ]);

        $items   = $portfolio->items()->where('is_visible', true)->with('files')->get();
        $grouped = $items->groupBy('item_type')->map(fn($g) => $g->toArray())->toArray();

        $html = view('pdf.portfolio', compact('user', 'portfolio', 'customization', 'grouped'))->render();

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
