<?php

// Properties 18–20: Portfolio Visibility

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;

// Property 18: Private portfolio returns 404 for unauthenticated visitors
it('P18: private portfolio returns 404 for guests', function () {
    $user = User::factory()->create(['username' => 'privateuser']);
    Portfolio::factory()->create(['user_id' => $user->id, 'is_public' => false]);

    $response = $this->get('/portfolio/privateuser');
    $response->assertStatus(404);
});

// Property 19: Public portfolio only shows visible items
it('P19: public portfolio only shows visible items', function () {
    $user = User::factory()->create(['username' => 'publicuser']);
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'is_public' => true]);

    PortfolioItem::factory()->create([
        'portfolio_id' => $portfolio->id,
        'title'        => 'Visible Item',
        'is_visible'   => true,
    ]);
    PortfolioItem::factory()->create([
        'portfolio_id' => $portfolio->id,
        'title'        => 'Hidden Item',
        'is_visible'   => false,
    ]);

    $response = $this->get('/portfolio/publicuser');
    $response->assertStatus(200);
    $response->assertSee('Visible Item');
    $response->assertDontSee('Hidden Item');
});

// Property 20: View count increments for unauthenticated visitors
it('P20: view count increments for unauthenticated visitors', function () {
    $user = User::factory()->create(['username' => 'viewcountuser']);
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'is_public' => true, 'view_count' => 0]);

    $this->get('/portfolio/viewcountuser');

    expect($portfolio->fresh()->view_count)->toBe(1);
});
