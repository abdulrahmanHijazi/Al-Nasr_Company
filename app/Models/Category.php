<?php

namespace App\Models;

use App\Models\Concerns\GeneratesTranslatedSlug;
use App\Models\Concerns\ResolvesTranslatedSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use GeneratesTranslatedSlug, HasFactory, HasTranslations, InteractsWithMedia, ResolvesTranslatedSlug;

    protected $guarded = ['id'];
    public array $translatable = ['name', 'description', 'slug'];
    protected $casts = ['is_active' => 'bool', 'sort_order' => 'int'];

    protected $appends = ['image_url', 'banner_url'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('banner')->singleFile();
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function products() { return $this->belongsToMany(\App\Models\Product::class, 'category_product'); }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image') ?: null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner') ?: null;
    }

    /** Public page of this record on the storefront (null while it has no slug yet). */
    public function storefrontUrl(): ?string
    {
        $slug = $this->slug;

        return filled($slug) ? url('/categories/'.rawurlencode($slug)) : null;
    }
}
