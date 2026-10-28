<?php

namespace App\Models;

use App\Models\Concerns\GeneratesTranslatedSlug;
use App\Models\Concerns\ResolvesTranslatedSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Brand extends Model implements HasMedia
{
    use GeneratesTranslatedSlug, HasFactory, HasTranslations, InteractsWithMedia, ResolvesTranslatedSlug;

    protected $guarded = ['id'];
    public array $translatable = ['name', 'description', 'slug'];
    protected $casts = ['is_active' => 'bool', 'sort_order' => 'int'];

    protected $appends = ['logo_url', 'cover_url'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover') ?: null;
    }

    /** Public page of this record on the storefront (null while it has no slug yet). */
    public function storefrontUrl(): ?string
    {
        $slug = $this->slug;

        return filled($slug) ? url('/brands/'.rawurlencode($slug)) : null;
    }
}
