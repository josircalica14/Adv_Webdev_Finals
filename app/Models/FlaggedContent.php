<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlaggedContent extends Model
{
    use HasFactory;

    protected $table = 'flagged_content';

    protected $fillable = [
        'portfolio_item_id', 'flagged_by', 'reason', 'status', 'is_hidden', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden'   => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function portfolioItem(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class);
    }

    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }
}
