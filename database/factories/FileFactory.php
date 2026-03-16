<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $name = fake()->word() . '.jpg';
        return [
            'portfolio_item_id' => PortfolioItem::factory(),
            'user_id'           => User::factory(),
            'original_filename' => $name,
            'stored_filename'   => $name,
            'file_path'         => 'uploads/' . $name,
            'file_type'         => 'image/jpeg',
            'file_size'         => fake()->numberBetween(1024, 1024 * 1024),
            'thumbnail_path'    => null,
        ];
    }
}
