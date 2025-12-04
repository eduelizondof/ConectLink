<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'title',
        'url',
        'description',
        'icon',
        'thumbnail',
        'button_color',
        'text_color',
        'is_highlighted',
        'sort_order',
        'is_active',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
        ];
    }

    /**
     * Get the profile this custom link belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * Scope to filter active custom links.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to get highlighted links.
     */
    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }
}

