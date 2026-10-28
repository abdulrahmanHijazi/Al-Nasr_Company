<?php

use App\Models\Bundle;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

function bundleWith(array $lines, float $price): Bundle
{
    $bundle = Bundle::factory()->create(['bundle_price' => $price]);

    foreach ($lines as [$unitPrice, $qty]) {
        $bundle->products()->attach(Product::factory()->create(['price' => $unitPrice]), ['qty' => $qty]);
    }

    return $bundle->fresh('products');
}

it('computes the original total from product prices and quantities', function () {
    // 2 × 500 (panels) + 1 × 1,200 (inverter) + 3 × 150 (cameras) = 2,650
    $bundle = bundleWith([[500, 2], [1200, 1], [150, 3]], 2300);

    expect($bundle->original_total)->toBe(2650.0);
});

it('computes savings as original total minus bundle price', function () {
    $bundle = bundleWith([[500, 2], [1200, 1], [150, 3]], 2300);

    expect($bundle->savings)->toBe(350.0);
});

it('never reports negative savings', function () {
    $bundle = bundleWith([[100, 1]], 250);

    expect($bundle->savings)->toBe(0.0);
});

it('has zero totals when it contains no products', function () {
    $bundle = Bundle::factory()->create(['bundle_price' => 100])->fresh('products');

    expect($bundle->original_total)->toBe(0.0)->and($bundle->savings)->toBe(0.0);
});

it('defaults pivot quantity to 1 and keeps the override price', function () {
    $bundle = Bundle::factory()->create();
    $bundle->products()->attach(Product::factory()->create(), ['price_override' => 49.9]);

    $pivot = $bundle->fresh()->products->first()->pivot;

    expect($pivot->qty)->toBe(1)->and((float) $pivot->price_override)->toBe(49.9);
});

it('casts price, dates and flags', function () {
    $bundle = Bundle::factory()->create([
        'bundle_price' => 1999,
        'starts_at' => '2026-11-01 08:00:00',
        'ends_at' => '2026-12-01 23:59:00',
    ])->fresh();

    expect($bundle->bundle_price)->toBe('1999.00')
        ->and($bundle->starts_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($bundle->ends_at->isAfter($bundle->starts_at))->toBeTrue()
        ->and($bundle->is_active)->toBeTrue();
});

it('translates its name', function () {
    $bundle = Bundle::factory()->create(['name' => ['en' => '5kW Solar Kit', 'ar' => 'طقم طاقة شمسية ٥ كيلو']]);

    expect($bundle->getTranslation('name', 'ar'))->toBe('طقم طاقة شمسية ٥ كيلو');
});

it('serialises savings, original_total and cover_url for the storefront', function () {
    $bundle = bundleWith([[500, 2]], 900);

    $bundle->addMedia(UploadedFile::fake()->image('kit.jpg'))->toMediaCollection('cover');
    $array = $bundle->fresh('products')->toArray();

    expect($array)->toHaveKeys(['cover_url', 'original_total', 'savings'])
        ->and($array['savings'])->toBe(100.0)
        ->and($array['cover_url'])->toContain('kit');
});

it('deletes its pivot rows with the bundle but keeps the products', function () {
    $bundle = bundleWith([[100, 1]], 90);
    $productId = $bundle->products->first()->id;

    $bundle->delete();

    expect(Product::find($productId))->not->toBeNull()
        ->and(DB::table('bundle_product')->count())->toBe(0);
});
