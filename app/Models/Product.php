<?php

namespace App\Models;

use App\Models\Concerns\GeneratesTranslatedSlug;
use App\Models\Concerns\ResolvesTranslatedSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use GeneratesTranslatedSlug, HasFactory, HasTranslations, InteractsWithMedia, ResolvesTranslatedSlug;

    protected $guarded = ['id'];
    public array $translatable = ['name', 'description', 'short_description', 'slug'];
    protected $casts = [
        'price' => 'decimal:2', 'compare_price' => 'decimal:2', 'cost_price' => 'decimal:2',
        'is_active' => 'bool', 'is_featured' => 'bool', 'stock_qty' => 'int', 'sort_order' => 'int',
    ];

    protected $appends = ['cover_url', 'gallery'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('cover')->singleFile();
    }

    public function brand() { return $this->belongsTo(\App\Models\Brand::class); }
    public function categories() { return $this->belongsToMany(\App\Models\Category::class, 'category_product'); }
    public function bundles() { return $this->belongsToMany(\App\Models\Bundle::class, 'bundle_product')->withPivot('qty', 'price_override'); }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover') ?: null;
    }

    public function getGalleryAttribute(): array
    {
        return $this->getMedia('gallery')->map(fn ($m) => $m->getUrl())->all();
    }

    /** Public page of this record on the storefront (null while it has no slug yet). */
    public function storefrontUrl(): ?string
    {
        $slug = $this->slug;

        return filled($slug) ? url('/products/'.rawurlencode($slug)) : null;
    }
}
