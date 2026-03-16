<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('portfolio');
});

it('pdf export streams as a downloadable attachment', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    PortfolioItem::factory()->count(3)->create([
        'portfolio_id' => $portfolio->id,
        'is_visible'   => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.export.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('pdf export does not write a file to disk', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    PortfolioItem::factory()->count(2)->create([
        'portfolio_id' => $portfolio->id,
        'is_visible'   => true,
    ]);

    $this->actingAs($user)->get(route('dashboard.export.pdf'));

    // No PDF files should be stored on the portfolio disk
    Storage::disk('portfolio')->assertMissing('export.pdf');
    Storage::disk('portfolio')->assertMissing('portfolio.pdf');
});
