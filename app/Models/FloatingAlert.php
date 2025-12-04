<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloatingAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'title',
        'message',
        'type',
        'icon',
        'link_url',
        'link_text',
        'position',
        'animation',
        'background_color',
        'text_color',
        'is_dismissible',
        'show_on_load',
        'delay_seconds',
        'start_date',
        'end_date',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_dismissible' => 'boolean',
            'show_on_load' => 'boolean',
            'delay_seconds' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Alert type configurations.
     */
    public const TYPES = [
        'info' => ['icon' => 'info', 'color' => '#3B82F6'],
        'promo' => ['icon' => 'tag', 'color' => '#10B981'],
        'warning' => ['icon' => 'alert-triangle', 'color' => '#F59E0B'],
        'success' => ['icon' => 'check-circle', 'color' => '#10B981'],
        'announcement' => ['icon' => 'megaphone', 'color' => '#8B5CF6'],
    ];

    /**
     * Get the profile this alert belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the type configuration.
     */
    public function getTypeConfigAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['info'];
    }

    /**
     * Get the default icon for this alert type.
     */
    public function getDefaultIconAttribute(): string
    {
        return $this->icon ?? $this->type_config['icon'];
    }

    /**
     * Get the default color for this alert type.
     */
    public function getDefaultColorAttribute(): string
    {
        return $this->background_color ?? $this->type_config['color'];
    }

    /**
     * Check if the alert is currently visible (within date range).
     */
    public function isCurrentlyVisible(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Scope to filter active alerts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter currently visible alerts.
     */
    public function scopeCurrentlyVisible($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

