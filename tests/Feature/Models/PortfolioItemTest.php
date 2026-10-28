<?php

use App\Models\PortfolioItem;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('uses the portfolio_items table', function () {
    expect((new PortfolioItem())->getTable())->toBe('portfolio_items');
});

it('translates title, description and client name', function () {
    $item = PortfolioItem::factory()->create([
        'title' => ['en' => '2 MW Rooftop Solar Plant', 'ar' => 'محطة طاقة شمسية على الأسطح ٢ ميجاوات'],
        'client_name' => ['en' => 'Najd Logistics', 'ar' => 'نجد للخدمات اللوجستية'],
    ]);

    expect($item->getTranslation('title', 'ar'))->toBe('محطة طاقة شمسية على الأسطح ٢ ميجاوات')
        ->and($item->getTranslation('client_name', 'en'))->toBe('Najd Logistics');
});

it('casts completion date, flags and sort order', function () {
    $item = PortfolioItem::factory()->create([
        'completed_at' => '2026-03-15',
        'is_featured' => 1,
        'sort_order' => '2',
    ])->fresh();

    expect($item->completed_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($item->completed_at->toDateString())->toBe('2026-03-15')
        ->and($item->is_featured)->toBeTrue()
        ->and($item->sort_order)->toBe(2);
});

it('can be listed featured-first for the home page', function () {
    PortfolioItem::factory()->create(['is_featured' => false, 'sort_order' => 1]);
    $featured = PortfolioItem::factory()->create(['is_featured' => true, 'sort_order' => 5]);
    PortfolioItem::factory()->create(['is_active' => false, 'is_featured' => true]);

    $list = PortfolioItem::where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->get();

    expect($list)->toHaveCount(2)->and($list->first()->is($featured))->toBeTrue();
});

it('exposes cover_url and gallery', function () {
    $item = PortfolioItem::factory()->create();

    expect($item->cover_url)->toBeNull()->and($item->gallery)->toBe([]);

    $item->addMedia(UploadedFile::fake()->image('plant.jpg'))->toMediaCollection('cover');
    $item->addMedia(UploadedFile::fake()->image('panels-1.jpg'))->toMediaCollection('gallery');
    $item->addMedia(UploadedFile::fake()->image('panels-2.jpg'))->toMediaCollection('gallery');

    $fresh = $item->fresh();

    expect($fresh->cover_url)->toContain('plant')
        ->and($fresh->gallery)->toHaveCount(2)
        ->and($fresh->toArray())->toHaveKeys(['cover_url', 'gallery']);
});

it('registers a single cover and a multi-image gallery', function () {
    $item = new PortfolioItem();
    $item->registerMediaCollections();
    $collections = $item->getRegisteredMediaCollections()->keyBy('name');

    expect($collections['cover']->singleFile)->toBeTrue()
        ->and($collections['gallery']->singleFile)->toBeFalse();
});
