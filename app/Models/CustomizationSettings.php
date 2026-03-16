<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomizationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'theme', 'layout',
        'primary_color', 'accent_color', 'heading_font', 'body_font',
    ];

    public static array $defaults = [
        'theme'         => 'default',
        'layout'        => 'grid',
        'primary_color' => '#3498db',
        'accent_color'  => '#e74c3c',
        'heading_font'  => 'Roboto',
        'body_font'     => 'Open Sans',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
