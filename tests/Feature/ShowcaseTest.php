<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;

it('showcase page loads successfully', function () {
    $response = $this->get('/showcase');
    $response->assertStatus(200);
});

it('only shows public portfolios', function () {
    $publicUser = User::factory()->create(['full_name' => 'Public User']);
    $pub = Portfolio::factory()->create(['user_id' => $publicUser->id, 'is_public' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $pub->id, 'is_visible' => true, 'item_type' => 'project']);

    $privateUser = User::factory()->create(['full_name' => 'Private User']);
    $priv = Portfolio::factory()->create(['user_id' => $privateUser->id, 'is_public' => false]);
    PortfolioItem::factory()->create(['portfolio_id' => $priv->id, 'is_visible' => true, 'item_type' => 'project']);

    $response = $this->get('/showcase');
    $response->assertSee('Public User');
    $response->assertDontSee('Private User');
});

it('filters by program', function () {
    $bsitUser = User::factory()->create(['program' => 'BSIT', 'full_name' => 'BSIT Student']);
    $bsitPortfolio = Portfolio::factory()->create(['user_id' => $bsitUser->id, 'is_public' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $bsitPortfolio->id, 'is_visible' => true, 'item_type' => 'project']);

    $cseUser = User::factory()->create(['program' => 'CSE', 'full_name' => 'CSE Student']);
    $csePortfolio = Portfolio::factory()->create(['user_id' => $cseUser->id, 'is_public' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $csePortfolio->id, 'is_visible' => true, 'item_type' => 'project']);

    $response = $this->get('/showcase?program=BSIT');
    $response->assertSee('BSIT Student');
    $response->assertDontSee('CSE Student');
});

it('searches by name', function () {
    $user = User::factory()->create(['full_name' => 'Unique Name XYZ']);
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'is_public' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true, 'item_type' => 'project']);

    $other = User::factory()->create(['full_name' => 'Other Person']);
    $otherPortfolio = Portfolio::factory()->create(['user_id' => $other->id, 'is_public' => true]);
    PortfolioItem::factory()->create(['portfolio_id' => $otherPortfolio->id, 'is_visible' => true, 'item_type' => 'project']);

    $response = $this->get('/showcase?q=Unique+Name+XYZ');
    $response->assertSee('Unique Name XYZ');
    $response->assertDontSee('Other Person');
});

it('paginates at 20 per page', function () {
    User::factory()->count(25)->create()->each(function ($user) {
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'is_public' => true]);
        PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'is_visible' => true, 'item_type' => 'project']);
    });

    $response = $this->get('/showcase');
    $response->assertStatus(200);
    $response->assertSee('page=2');
});
