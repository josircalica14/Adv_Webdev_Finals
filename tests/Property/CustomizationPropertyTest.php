<?php

// Properties 26–27: Customization

use App\Models\User;
use App\Models\Portfolio;
use App\Models\CustomizationSettings;

// Property 26: Customization settings round-trip (save then retrieve)
it('P26: customization settings survive save and retrieve', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    $settings = [
        'theme'         => 'dark',
        'layout'        => 'grid',
        'primary_color' => '#3498db',
        'accent_color'  => '#e74c3c',
        'heading_font'  => 'Roboto',
        'body_font'     => 'Open Sans',
    ];

    $response = $this->actingAs($user)
        ->put(route('dashboard.customize.save'), $settings);

    $response->assertRedirect();

    $saved = CustomizationSettings::where('portfolio_id', $portfolio->id)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->theme)->toBe('dark')
        ->and($saved->layout)->toBe('grid')
        ->and($saved->primary_color)->toBe('#3498db')
        ->and($saved->accent_color)->toBe('#e74c3c');
});

// Property 27: Reset restores default values
it('P27: reset restores default customization values', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    // First save custom settings
    CustomizationSettings::create([
        'portfolio_id'  => $portfolio->id,
        'theme'         => 'custom',
        'layout'        => 'list',
        'primary_color' => '#000000',
        'accent_color'  => '#ffffff',
        'heading_font'  => 'Arial',
        'body_font'     => 'Times',
    ]);

    $response = $this->actingAs($user)
        ->post(route('dashboard.customize.reset'));

    $response->assertRedirect();

    $settings = CustomizationSettings::where('portfolio_id', $portfolio->id)->first();
    expect($settings->theme)->toBe('default')
        ->and($settings->layout)->toBe('grid')
        ->and($settings->primary_color)->toBe('#3498db')
        ->and($settings->accent_color)->toBe('#e74c3c')
        ->and($settings->heading_font)->toBe('Roboto')
        ->and($settings->body_font)->toBe('Open Sans');
});
