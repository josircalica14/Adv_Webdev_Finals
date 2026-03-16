<?php

// Properties 33–38: Admin

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\AdminAction;
use App\Models\FlaggedContent;

// Property 33: Admin index paginates at 20 per page
it('P33: admin index paginates at 20 per page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    User::factory()->count(25)->create()->each(function ($user) {
        Portfolio::factory()->create(['user_id' => $user->id]);
    });

    $response = $this->actingAs($admin)->get('/admin');
    $response->assertStatus(200);
    $response->assertSee('page=2');
});

// Property 34: Flagging creates a pending FlaggedContent record
it('P34: flagging creates a pending flagged_content record', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id]);

    $this->actingAs($admin)->post(route('admin.items.flag', $item), [
        'reason' => 'Inappropriate content',
    ]);

    expect(FlaggedContent::where([
        'portfolio_item_id' => $item->id,
        'status'            => 'pending',
    ])->exists())->toBeTrue();
});

// Property 35: Hide then unhide returns item to visible state (round-trip)
it('P35: hide then unhide returns item to visible state', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true]);

    $this->actingAs($admin)->put(route('admin.items.hide', $item));
    expect($item->fresh()->is_visible)->toBeFalse();

    $this->actingAs($admin)->put(route('admin.items.unhide', $item));
    expect($item->fresh()->is_visible)->toBeTrue();
});

// Property 36: Every admin action creates an audit log entry
it('P36: every admin action creates an audit log entry', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id]);

    $countBefore = AdminAction::count();

    $this->actingAs($admin)->put(route('admin.items.hide', $item));

    expect(AdminAction::count())->toBe($countBefore + 1);
});

// Property 37: Non-admin gets 403 on admin routes
it('P37: non-admin receives 403 on admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(403);
});

// Property 38: Unauthenticated user is redirected from admin routes
it('P38: unauthenticated user is redirected from admin routes', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});
