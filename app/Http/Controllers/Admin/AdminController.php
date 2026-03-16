<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminNotificationRequest;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private AdminService $adminService) {}

    public function index(Request $request): View
    {
        $portfolios = $this->adminService->getAllPortfolios($request->integer('page', 1));
        return view('admin.index', compact('portfolios'));
    }

    public function flagItem(Request $request, PortfolioItem $item): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $this->adminService->flagItem($item, $request->user(), $request->input('reason'));
        return back()->with('status', 'Item flagged.');
    }

    public function hideItem(PortfolioItem $item, Request $request): RedirectResponse
    {
        $this->adminService->hideItem($item, $request->user());
        return back()->with('status', 'Item hidden.');
    }

    public function unhideItem(PortfolioItem $item, Request $request): RedirectResponse
    {
        $this->adminService->unhideItem($item, $request->user());
        return back()->with('status', 'Item unhidden.');
    }

    public function notify(AdminNotificationRequest $request, User $user): RedirectResponse
    {
        $this->adminService->sendNotification($user, $request->user(), $request->input('subject'), $request->input('message'));
        return back()->with('status', 'Notification sent.');
    }

    public function flaggedContent(Request $request): View
    {
        $status  = $request->input('status', 'all');
        $flagged = $this->adminService->getFlaggedContent($status);
        return view('admin.flagged', compact('flagged', 'status'));
    }
}
