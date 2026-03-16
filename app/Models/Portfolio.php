<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'is_public', 'view_count'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PortfolioItem::class)->orderBy('display_order');
    }

    public function customization(): HasOne
    {
        return $this->hasOne(CustomizationSettings::class);
    }

    public function scopePublicWithItems(Builder $query): Builder
    {
        return $query->where('is_public', true)->whereHas('items', fn($q) => $q->where('is_visible', true));
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereHas('user', function ($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('bio', 'like', "%{$term}%");
        })->orWhereHas('items', function ($q) use ($term) {
            $q->whereJsonContains('tags', $term);
        });
    }

    public function scopeProgram(Builder $query, string $program): Builder
    {
        return $query->whereHas('user', fn($q) => $q->where('program', $program));
    }
}
