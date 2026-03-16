<?php

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        return [
            'portfolio_id'  => Portfolio::factory(),
            'item_type'     => fake()->randomElement(['project', 'achievement', 'skill', 'experience', 'education']),
            'title'         => fake()->sentence(4),
            'description'   => fake()->paragraph(),
            'item_date'     => fake()->date(),
            'tags'          => ['php', 'laravel'],
            'links'         => [],
            'is_visible'    => true,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }
}
