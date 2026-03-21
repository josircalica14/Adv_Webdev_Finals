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
        'font_size', 'spacing', 'visible_sections', 'section_order',
        'show_email', 'show_username', 'show_bio', 'header_style',
    ];

    protected function casts(): array
    {
        return [
            'visible_sections' => 'array',
            'section_order'    => 'array',
            'show_email'       => 'boolean',
            'show_username'    => 'boolean',
            'show_bio'         => 'boolean',
        ];
    }

    public static array $defaults = [
        'theme'            => 'default',
        'layout'           => 'grid',
        'primary_color'    => '#3498db',
        'accent_color'     => '#e74c3c',
        'heading_font'     => 'Roboto',
        'body_font'        => 'Open Sans',
        'font_size'        => 'medium',
        'spacing'          => 'normal',
        'visible_sections' => ['project','experience','education','achievement','milestone','skill'],
        'section_order'    => ['project','experience','education','achievement','milestone','skill'],
        'show_email'       => true,
        'show_username'    => true,
        'show_bio'         => true,
        'header_style'     => 'dark',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
