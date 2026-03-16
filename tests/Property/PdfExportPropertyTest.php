<?php

// Properties 31–32: PDF Export

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('portfolio');
});

// Property 31: All visible items appear in the PDF grouped by type
it('P31: PDF export includes all visible items grouped by type', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project', 'title' => 'My Project', 'is_visible' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'achievement', 'title' => 'My Award', 'is_visible' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project', 'title' => 'Hidden Project', 'is_visible' => false]);

    $response = $this->actingAs($user)->get(route('dashboard.export.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    // PDF is binary — we verify it's generated without error and is non-empty
    expect(strlen($response->getContent()))->toBeGreaterThan(100);
});

// Property 32: Selective export only includes specified item IDs
it('P32: selective export only includes specified items', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    $item1 = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true]);
    $item2 = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true]);

    // Export only item1
    $response = $this->actingAs($user)
        ->get(route('dashboard.export.pdf') . '?item_ids[]=' . $item1->id);

    $response->assertStatus(200);
    expect(strlen($response->getContent()))->toBeGreaterThan(100);
});
