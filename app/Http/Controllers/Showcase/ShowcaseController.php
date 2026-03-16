<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\Controller;
use App\Services\ShowcaseService;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function __construct(private ShowcaseService $showcaseService) {}

    public function index(Request $request): mixed
    {
        $criteria = [
            'query'   => $request->input('q'),
            'program' => $request->input('program'),
            'sort'    => $request->input('sort', 'updated'),
        ];

        $portfolios = $this->showcaseService->getPublicPortfolios($criteria, $request->integer('page', 1));

        if ($request->header('X-Showcase-Fetch')) {
            return view('showcase.partials.results', compact('portfolios', 'criteria'));
        }

        $stats    = $this->showcaseService->getStats();
        $featured = $this->showcaseService->getFeatured(3);
        $skills   = $this->showcaseService->getTopSkills(16);
        $recent   = $this->showcaseService->getRecentPortfolios(6);

        return view('showcase.index', compact('portfolios', 'criteria', 'stats', 'featured', 'skills', 'recent'));
    }
}
