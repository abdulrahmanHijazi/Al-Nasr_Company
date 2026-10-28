<?php

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('translates name, slug and descriptions', function () {
    $product = Product::factory()->create([
        'name' => ['en' => 'Lithium Battery 48V 100Ah', 'ar' => 'بطارية ليثيوم ٤٨ فولت ١٠٠ أمبير'],
        'short_description' => ['en' => 'Rack-mount LiFePO4', 'ar' => 'ليثيوم فوسفات الحديد'],
    ]);

    expect($product->getTranslation('name', 'ar'))->toBe('بطارية ليثيوم ٤٨ فولت ١٠٠ أمبير')
        ->and($product->getTranslation('short_description', 'en'))->toBe('Rack-mount LiFePO4');
});

it('casts prices to 2-decimal strings and flags/stock to native types', function () {
    $product = Product::factory()->create([
        'price' => 1499.5,
        'compare_price' => 1799,
        'cost_price' => 1100,
        'stock_qty' => '12',
        'is_featured' => 1,
    ])->fresh();

    expect($product->price)->toBe('1499.50')
        ->and($product->compare_price)->toBe('1799.00')
        ->and($product->cost_price)->toBe('1100.00')
        ->and($product->stock_qty)->toBe(12)
        ->and($product->is_featured)->toBeTrue();
});

it('enforces a unique SKU', function () {
    Product::factory()->create(['sku' => 'SOL-550W']);

    expect(fn () => Product::factory()->create(['sku' => 'SOL-550W']))->toThrow(QueryException::class);
});

it('belongs to a brand', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);

    expect($product->brand->is($brand))->toBeTrue();
});

it('can have no brand', function () {
    expect(Product::factory()->create()->brand)->toBeNull();
});

it('belongs to many categories', function () {
    $product = Product::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $product->categories()->sync($categories->pluck('id'));

    expect($product->fresh()->categories)->toHaveCount(2);
});

it('belongs to bundles with qty and price override on the pivot', function () {
    $product = Product::factory()->create();
    $bundle = Bundle::factory()->create();
    $bundle->products()->attach($product, ['qty' => 4, 'price_override' => 99.5]);

    $pivot = $product->bundles->first()->pivot;

    expect($product->bundles)->toHaveCount(1)
        ->and($pivot->qty)->toBe(4)
        ->and((float) $pivot->price_override)->toBe(99.5);
});

it('registers a single cover and a multi-image gallery collection', function () {
    $product = new Product();
    $product->registerMediaCollections();

    $collections = $product->getRegisteredMediaCollections()->keyBy('name');

    expect($collections['cover']->singleFile)->toBeTrue()
        ->and($collections['gallery']->singleFile)->toBeFalse();
});

it('exposes cover_url and gallery for the storefront', function () {
    $product = Product::factory()->create();

    expect($product->cover_url)->toBeNull()->and($product->gallery)->toBe([]);

    $product->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('cover');
    $product->addMedia(UploadedFile::fake()->image('side-1.jpg'))->toMediaCollection('gallery');
    $product->addMedia(UploadedFile::fake()->image('side-2.jpg'))->toMediaCollection('gallery');

    $fresh = $product->fresh();

    expect($fresh->cover_url)->toContain('front')
        ->and($fresh->gallery)->toHaveCount(2)
        ->and($fresh->toArray())->toHaveKeys(['cover_url', 'gallery']);
});

it('is excluded from the storefront when inactive', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    expect(Product::where('is_active', true)->count())->toBe(1);
});
