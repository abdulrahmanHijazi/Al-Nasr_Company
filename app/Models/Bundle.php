<?php

namespace App\Models;

use App\Models\Concerns\GeneratesTranslatedSlug;
use App\Models\Concerns\ResolvesTranslatedSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Bundle extends Model implements HasMedia
{
    use GeneratesTranslatedSlug, HasFactory, HasTranslations, InteractsWithMedia, ResolvesTranslatedSlug;

    protected $guarded = ['id'];
    public array $translatable = ['name', 'description', 'slug'];
    protected $casts = [
        'bundle_price' => 'decimal:2', 'is_active' => 'bool',
        'starts_at' => 'datetime', 'ends_at' => 'datetime', 'sort_order' => 'int',
    ];

    protected $appends = ['cover_url', 'original_total', 'savings'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'bundle_product')
            ->withPivot('qty', 'price_override');
    }

    public function getOriginalTotalAttribute(): float
    {
        return (float) $this->products->sum(fn ($p) => (float) $p->price * (int) ($p->pivot->qty ?? 1));
    }

    public function getSavingsAttribute(): float
    {
        return max(0, $this->original_total - (float) $this->bundle_price);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover') ?: null;
    }

    /** Public page of this record on the storefront (null while it has no slug yet). */
    public function storefrontUrl(): ?string
    {
        $slug = $this->slug;

        return filled($slug) ? url('/offers/'.rawurlencode($slug)) : null;
    }
}
