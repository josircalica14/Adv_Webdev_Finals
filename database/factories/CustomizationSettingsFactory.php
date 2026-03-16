<?php

namespace Database\Factories;

use App\Models\CustomizationSettings;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomizationSettingsFactory extends Factory
{
    protected $model = CustomizationSettings::class;

    public function definition(): array
    {
        return [
            'portfolio_id'  => Portfolio::factory(),
            'theme'         => 'default',
            'layout'        => 'grid',
            'primary_color' => '#3498db',
            'accent_color'  => '#e74c3c',
            'heading_font'  => 'Roboto',
            'body_font'     => 'Open Sans',
        ];
    }
}
