<?php

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('stores translatable name, slug and description per locale', function () {
    $brand = Brand::factory()->create([
        'name' => ['en' => 'SunVolt', 'ar' => 'سن فولت'],
        'slug' => ['en' => 'sunvolt', 'ar' => 'sun-volt'],
    ]);

    expect($brand->getTranslation('name', 'en'))->toBe('SunVolt')
        ->and($brand->getTranslation('name', 'ar'))->toBe('سن فولت')
        ->and($brand->getTranslation('slug', 'ar'))->toBe('sun-volt');

    app()->setLocale('ar');
    expect($brand->fresh()->name)->toBe('سن فولت');
});

it('casts flags and sort order', function () {
    $brand = Brand::factory()->create(['is_active' => 1, 'sort_order' => '7'])->fresh();

    expect($brand->is_active)->toBeTrue()
        ->and($brand->sort_order)->toBeInt()->toBe(7);
});

it('has many products and nulls the foreign key when deleted', function () {
    $brand = Brand::factory()->create();
    $products = Product::factory()->count(2)->create(['brand_id' => $brand->id]);

    expect($brand->products)->toHaveCount(2)
        ->and($products->first()->brand->is($brand))->toBeTrue();

    $brand->delete();

    expect($products->first()->fresh()->brand_id)->toBeNull();
});

it('registers single-file logo and cover collections', function () {
    $brand = new Brand();
    $brand->registerMediaCollections();

    $collections = $brand->getRegisteredMediaCollections()->keyBy('name');

    expect($collections->keys()->all())->toContain('logo', 'cover')
        ->and($collections['logo']->singleFile)->toBeTrue()
        ->and($collections['cover']->singleFile)->toBeTrue();
});

it('exposes logo_url and cover_url, null until an image is uploaded', function () {
    $brand = Brand::factory()->create();

    expect($brand->logo_url)->toBeNull()
        ->and($brand->toArray())->toHaveKeys(['logo_url', 'cover_url']);

    $brand->addMedia(UploadedFile::fake()->image('logo.png', 200, 200))->toMediaCollection('logo');

    expect($brand->fresh()->logo_url)->toBeString()->toContain('logo');
});

it('replaces the logo when a new one is uploaded', function () {
    $brand = Brand::factory()->create();

    $brand->addMedia(UploadedFile::fake()->image('old.png'))->toMediaCollection('logo');
    $brand->addMedia(UploadedFile::fake()->image('new.png'))->toMediaCollection('logo');

    expect($brand->fresh()->getMedia('logo'))->toHaveCount(1)
        ->and($brand->fresh()->getFirstMedia('logo')->file_name)->toBe('new.png');
});

it('generates slugs per locale from the name when none is given', function () {
    $brand = Brand::create(['name' => ['en' => 'Sun Volt', 'ar' => 'سن فولت']]);

    expect($brand->getTranslation('slug', 'en'))->toBe('sun-volt')
        ->and($brand->getTranslation('slug', 'ar'))->toBe('سن-فولت');
});

it('keeps generated slugs unique per locale', function () {
    Brand::create(['name' => ['en' => 'Voltix']]);
    $second = Brand::create(['name' => ['en' => 'Voltix']]);

    expect($second->getTranslation('slug', 'en'))->toBe('voltix-2');
});

it('does not overwrite a slug that was set explicitly', function () {
    $brand = Brand::create(['name' => ['en' => 'Gridline'], 'slug' => ['en' => 'custom-grid']]);

    expect($brand->getTranslation('slug', 'en'))->toBe('custom-grid');
});
