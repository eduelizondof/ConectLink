<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'sale_price',
        'currency',
        'image',
        'gallery',
        'external_url',
        'whatsapp_message',
        'is_featured',
        'is_available',
        'sort_order',
        'is_active',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    /**
     * Get the organization this product belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the category this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Check if product is on sale.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get the current price (sale price if available).
     */
    public function getCurrentPriceAttribute(): ?float
    {
        return $this->is_on_sale ? $this->sale_price : $this->price;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return '$' . number_format($this->current_price, 2) . ' ' . $this->currency;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_on_sale || $this->price == 0) {
            return null;
        }

        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Get WhatsApp URL with pre-filled message.
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (!$this->whatsapp_message) {
            return null;
        }

        // Get WhatsApp number from organization's primary profile
        $profile = $this->organization->primaryProfile;
        $whatsappLink = $profile?->socialLinks()->where('platform', 'whatsapp')->first();

        if (!$whatsappLink) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $whatsappLink->url);
        $message = urlencode($this->whatsapp_message);

        return "https://wa.me/{$phone}?text={$message}";
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Scope to filter active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter available products.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

