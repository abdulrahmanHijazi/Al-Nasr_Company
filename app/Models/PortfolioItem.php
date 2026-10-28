<?php

namespace App\Models;

use App\Models\Concerns\GeneratesTranslatedSlug;
use App\Models\Concerns\ResolvesTranslatedSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PortfolioItem extends Model implements HasMedia
{
    use GeneratesTranslatedSlug, HasFactory, HasTranslations, InteractsWithMedia, ResolvesTranslatedSlug;

    protected $table = 'portfolio_items';
    protected $guarded = ['id'];
    public array $translatable = ['title', 'description', 'slug', 'client_name'];
    protected $casts = ['is_active' => 'bool', 'is_featured' => 'bool', 'completed_at' => 'date', 'sort_order' => 'int'];

    public function slugSourceAttribute(): string
    {
        return 'title';
    }

    protected $appends = ['cover_url', 'gallery'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

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

        return filled($slug) ? url('/portfolio/'.rawurlencode($slug)) : null;
    }
}
