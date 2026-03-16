<?php

// Properties 12–17: Portfolio Items

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Storage;

// Property 12: Creating an item always persists it with correct portfolio_id
it('P12: item creation persists with correct portfolio_id', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('dashboard.items.store'), [
        'item_type'   => 'project',
        'title'       => 'My Project',
        'description' => 'A description',
        'item_date'   => '2024-01-01',
        'tags_input'  => 'php,laravel',
        'links'       => [],
    ]);

    $response->assertRedirect();
    expect(PortfolioItem::where('portfolio_id', $portfolio->id)->where('title', 'My Project')->exists())->toBeTrue();
});

// Property 13: Missing required fields are rejected
it('P13: missing title is rejected', function () {
    $user = User::factory()->create();
    Portfolio::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('dashboard.items.store'), [
        'item_type' => 'project',
        // title missing
    ]);

    $response->assertSessionHasErrors('title');
});

// Property 14: Deleting an item removes it from the database
it('P14: item deletion removes it from the database', function () {
    Storage::fake('portfolio');
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id]);

    $this->actingAs($user)->delete(route('dashboard.items.destroy', $item));

    expect(PortfolioItem::find($item->id))->toBeNull();
});

// Property 15: Reorder only works for items owned by the user
it('P15: reorder rejects items from another portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    $otherUser = User::factory()->create();
    $otherPortfolio = Portfolio::factory()->create(['user_id' => $otherUser->id]);
    $otherItem = PortfolioItem::factory()->create(['portfolio_id' => $otherPortfolio->id, 'item_type' => 'project']);

    $response = $this->actingAs($user)
        ->from(route('dashboard.index'))
        ->put(route('dashboard.items.reorder'), [
            'item_ids' => [$item->id, $otherItem->id],
        ]);

    // Should redirect back with errors (item doesn't belong to portfolio)
    $response->assertRedirect();
    $response->assertSessionHasErrors('item_ids');
});

// Property 16: Toggling visibility twice returns to original state (idempotency)
it('P16: toggling visibility twice returns to original state', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true]);

    $this->actingAs($user)->put(route('dashboard.items.visibility', $item));
    expect($item->fresh()->is_visible)->toBeFalse();

    $this->actingAs($user)->put(route('dashboard.items.visibility', $item));
    expect($item->fresh()->is_visible)->toBeTrue();
});

// Property 17: Tags and links round-trip through JSON correctly
it('P17: tags and links survive JSON round-trip', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'item_type'   => 'project',
        'title'       => 'Round Trip Test',
        'description' => 'Test',
        'item_date'   => '2024-01-01',
        'tags_input'  => 'php,laravel,mysql',
        'links'       => [['url' => 'https://github.com/test', 'label' => 'GitHub']],
    ]);

    $item = PortfolioItem::where('title', 'Round Trip Test')->first();
    expect($item)->not->toBeNull()
        ->and($item->tags)->toBeArray()
        ->and($item->links)->toBeArray();
});
