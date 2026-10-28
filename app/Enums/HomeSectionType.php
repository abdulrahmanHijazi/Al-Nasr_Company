<?php

namespace App\Enums;

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\PortfolioItem;
use App\Models\Product;

/**
 * Every kind of block an admin can add to the storefront home page. Each type
 * ships with several visual designs (see designs()) — the same idea as the
 * elearning dashboard's HomePageSectionTypeEnum, trimmed to what Al-Nasr sells.
 */
enum HomeSectionType: string
{
    case Hero = 'hero';
    case Slider = 'slider';
    case Features = 'features';
    case Categories = 'categories';
    case Products = 'products';
    case Services = 'services';
    case Process = 'process';
    case Bundles = 'bundles';
    case Banners = 'banners';
    case Brands = 'brands';
    case Portfolio = 'portfolio';
    case Counters = 'counters';
    case Testimonials = 'testimonials';
    case Cta = 'cta';

    public function label(): string
    {
        return match ($this) {
            self::Hero => __('Hero'),
            self::Slider => __('Slider'),
            self::Features => __('Features strip'),
            self::Categories => __('Categories'),
            self::Products => __('Products'),
            self::Services => __('Installation services'),
            self::Process => __('Process / steps'),
            self::Bundles => __('Special offers (bundles)'),
            self::Banners => __('Promo banners'),
            self::Brands => __('Brands'),
            self::Portfolio => __('Portfolio'),
            self::Counters => __('Counters'),
            self::Testimonials => __('Testimonials'),
            self::Cta => __('Call to action'),
        };
    }

    /** @return array<string, string> design key => label; the first one is the default */
    public function designs(): array
    {
        return match ($this) {
            self::Hero => ['split' => __('Split with featured products'), 'slider' => __('Full-width slider'), 'centered' => __('Centered')],
            self::Slider => ['full' => __('Full-width slider'), 'product' => __('Product showcase'), 'cards' => __('Rounded card')],
            self::Features => ['row' => __('Row'), 'cards' => __('Cards')],
            self::Categories => ['tiles' => __('Image tiles'), 'pills' => __('Pills')],
            self::Products => ['grid' => __('Grid'), 'carousel' => __('Carousel')],
            self::Services => ['cards' => __('Cards'), 'list' => __('Numbered list')],
            self::Process => ['grid' => __('Grid'), 'timeline' => __('Timeline')],
            self::Bundles => ['band' => __('Dark band'), 'cards' => __('Light cards')],
            self::Banners => ['wide' => __('Wide (1 column)'), 'split' => __('Split (2 columns)'), 'triple' => __('Triple (3 columns)')],
            self::Brands => ['logos' => __('Logo grid'), 'marquee' => __('Scrolling strip')],
            self::Portfolio => ['grid' => __('Grid'), 'featured' => __('Featured first')],
            self::Counters => ['band' => __('Colour band'), 'cards' => __('Cards')],
            self::Testimonials => ['cards' => __('Cards'), 'quote' => __('Large quote')],
            self::Cta => ['primary' => __('Primary colour'), 'split' => __('Dark split'), 'minimal' => __('Minimal')],
        };
    }

    public function defaultDesign(): string
    {
        return array_key_first($this->designs());
    }

    /** Eloquent model whose records fill this section, if any. */
    public function itemModel(): ?string
    {
        return match ($this) {
            self::Hero, self::Products => Product::class,
            self::Categories => Category::class,
            self::Bundles => Bundle::class,
            self::Brands => Brand::class,
            self::Portfolio => PortfolioItem::class,
            default => null,
        };
    }

    /** Name of the HomePageSection relation used for hand-picked ("custom") items. */
    public function relation(): ?string
    {
        return match ($this) {
            self::Hero, self::Products => __('products'),
            self::Categories => __('categories'),
            self::Bundles => __('bundles'),
            self::Brands => __('brands'),
            self::Portfolio => __('portfolioItems'),
            default => null,
        };
    }

    public function usesItems(): bool
    {
        return $this->itemModel() !== null;
    }

    /** Whether "featured" is a meaningful filter (the model has is_featured). */
    public function supportsFeatured(): bool
    {
        return in_array($this, [self::Hero, self::Products, self::Portfolio], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
