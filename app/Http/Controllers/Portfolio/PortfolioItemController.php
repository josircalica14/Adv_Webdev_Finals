<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portfolio\ReorderItemsRequest;
use App\Http\Requests\Portfolio\StorePortfolioItemRequest;
use App\Http\Requests\Portfolio\UpdatePortfolioItemRequest;
use App\Models\PortfolioItem;
use App\Services\FileStorageService;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioItemController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private FileStorageService $fileStorage,
    ) {}

    public function create(): View
    {
        return view('portfolio.add-item');
    }

    public function store(StorePortfolioItemRequest $request): RedirectResponse
    {
        $item = $this->portfolioService->createItem($request->user(), $request->validated());

        if ($request->hasFile('image')) {
            try {
                $this->fileStorage->upload($request->file('image'), $request->user(), $item);
            } catch (\Throwable $e) {
                return redirect()->route('dashboard.index')->with('status', 'Item added but image upload failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard.index')->with('status', 'Item added.');
    }

    public function edit(PortfolioItem $item): View
    {
        $this->authorize('update', $item);
        $item->load('files');
        return view('portfolio.edit-item', compact('item'));
    }

    public function update(UpdatePortfolioItemRequest $request, PortfolioItem $item): RedirectResponse
    {
        $this->authorize('update', $item);
        $this->portfolioService->updateItem($item, $request->validated());

        if ($request->hasFile('image')) {
            try {
                $this->fileStorage->upload($request->file('image'), $request->user(), $item);
            } catch (\Throwable $e) {
                return redirect()->route('dashboard.index')->with('status', 'Item updated but image upload failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard.index')->with('status', 'Item updated.');
    }

    public function destroy(PortfolioItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);
        $this->portfolioService->deleteItem($item);
        return redirect()->route('dashboard.index')->with('status', 'Item deleted.');
    }

    public function reorder(ReorderItemsRequest $request): RedirectResponse
    {
        $portfolio = $this->portfolioService->getOrCreatePortfolio($request->user());
        try {
            $this->portfolioService->reorderItems($portfolio, $request->validated('item_ids'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['item_ids' => $e->getMessage()]);
        }
        return back()->with('status', 'Items reordered.');
    }

    public function toggleVisibility(PortfolioItem $item): RedirectResponse
    {
        $this->authorize('toggleVisibility', $item);
        $this->portfolioService->toggleItemVisibility($item);
        return back()->with('status', 'Visibility toggled.');
    }
}
