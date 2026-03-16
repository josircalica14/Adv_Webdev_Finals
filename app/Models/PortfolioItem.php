<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'item_type', 'title', 'description',
        'item_date', 'tags', 'links', 'is_visible', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'tags'       => 'array',
            'links'      => 'array',
            'is_visible' => 'boolean',
            'item_date'  => 'date',
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function flaggedContent(): HasMany
    {
        return $this->hasMany(FlaggedContent::class);
    }
}
