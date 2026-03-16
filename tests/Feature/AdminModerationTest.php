<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\AdminAction;
use App\Models\FlaggedContent;

it('admin can hide a portfolio item', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true]);

    $response = $this->actingAs($admin)
        ->put(route('admin.items.hide', $item));

    $response->assertRedirect();
    expect($item->fresh()->is_visible)->toBeFalse();
});

it('admin can unhide a portfolio item', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => false]);

    $response = $this->actingAs($admin)
        ->put(route('admin.items.unhide', $item));

    $response->assertRedirect();
    expect($item->fresh()->is_visible)->toBeTrue();
});

it('hide action creates an audit log entry', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id]);

    $this->actingAs($admin)->put(route('admin.items.hide', $item));

    expect(AdminAction::where([
        'admin_id'    => $admin->id,
        'action_type' => 'hide',
        'target_type' => 'portfolio_item',
        'target_id'   => $item->id,
    ])->exists())->toBeTrue();
});

it('unhide action creates an audit log entry', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => false]);

    $this->actingAs($admin)->put(route('admin.items.unhide', $item));

    expect(AdminAction::where([
        'admin_id'    => $admin->id,
        'action_type' => 'unhide',
        'target_type' => 'portfolio_item',
        'target_id'   => $item->id,
    ])->exists())->toBeTrue();
});

it('non-admin cannot access admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id]);

    $response = $this->actingAs($user)->put(route('admin.items.hide', $item));
    $response->assertStatus(403);
});
