<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\PortfolioItem;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(private FileStorageService $fileStorage) {}

    public function store(Request $request, PortfolioItem $item): RedirectResponse
    {
        $this->authorize('update', $item);
        $request->validate(['file' => 'required|file|max:10240']);

        if ($item->files()->count() >= 10) {
            return back()->withErrors(['file' => 'Maximum of 10 files per item.']);
        }

        try {
            $this->fileStorage->upload($request->file('file'), $request->user(), $item);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('status', 'File uploaded.');
    }

    public function destroy(File $file): RedirectResponse
    {
        $this->authorize('delete', $file->portfolioItem);
        $this->fileStorage->delete($file);
        return back()->with('status', 'File deleted.');
    }
}
