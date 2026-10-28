<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('supports nested categories through parent_id', function () {
    $solar = Category::factory()->create(['name' => ['en' => 'Solar', 'ar' => 'الطاقة الشمسية']]);
    $panels = Category::factory()->create(['parent_id' => $solar->id]);
    $inverters = Category::factory()->create(['parent_id' => $solar->id]);

    expect($solar->children->pluck('id')->all())->toEqualCanonicalizing([$panels->id, $inverters->id])
        ->and($panels->parent->is($solar))->toBeTrue()
        ->and($solar->parent)->toBeNull();
});

it('keeps children as root categories when the parent is deleted', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    $parent->delete();

    expect($child->fresh()->parent_id)->toBeNull();
});

it('relates to products many-to-many', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(3)->create();
    $category->products()->attach($products->pluck('id'));

    expect($category->products)->toHaveCount(3)
        ->and($products->first()->categories->first()->is($category))->toBeTrue();
});

it('detaches products from the pivot when the category is deleted', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $category->products()->attach($product);

    $category->delete();

    expect($product->categories()->count())->toBe(0)
        ->and(Product::find($product->id))->not->toBeNull();
});

it('translates name and description', function () {
    $category = Category::factory()->create([
        'name' => ['en' => 'Surveillance Cameras', 'ar' => 'كاميرات المراقبة'],
    ]);

    expect($category->getTranslation('name', 'ar'))->toBe('كاميرات المراقبة')
        ->and($category->getTranslations('name'))->toHaveKeys(['en', 'ar']);
});

it('exposes image_url and banner_url from single-file collections', function () {
    $category = Category::factory()->create();

    expect($category->image_url)->toBeNull()->and($category->banner_url)->toBeNull();

    $category->addMedia(UploadedFile::fake()->image('tile.jpg'))->toMediaCollection('image');
    $category->addMedia(UploadedFile::fake()->image('hero.jpg'))->toMediaCollection('banner');

    $fresh = $category->fresh();
    expect($fresh->image_url)->toContain('tile')
        ->and($fresh->banner_url)->toContain('hero')
        ->and($fresh->toArray())->toHaveKeys(['image_url', 'banner_url']);
});

it('casts is_active and sort_order', function () {
    $category = Category::factory()->create(['is_active' => 0, 'sort_order' => '3'])->fresh();

    expect($category->is_active)->toBeFalse()->and($category->sort_order)->toBe(3);
});
