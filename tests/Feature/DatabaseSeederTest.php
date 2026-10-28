<?php

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\PortfolioItem;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;

it('seeds the Al-Nasr starter catalogue', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Brand::count())->toBe(6)
        ->and(Category::count())->toBe(4)
        ->and(Product::count())->toBe(12)
        ->and(Bundle::count())->toBe(1)
        ->and(PortfolioItem::count())->toBe(1);

    $bundle = Bundle::with('products')->first();

    // 690 + 2 × 1,090 + 210 + 2 × 14 + 2 × 9 = 3,126 → saves 236 against the 2,890 bundle price
    expect($bundle->original_total)->toBe(3126.0)
        ->and($bundle->savings)->toBe(236.0);
});

it('seeds an editable home page layout that matches the built-in one', function () {
    $this->seed(DatabaseSeeder::class);

    $stored = App\Models\HomePageSection::ordered()->get();

    expect($stored)->toHaveCount(count(App\Models\HomePageSection::defaults()))
        ->and($stored->first()->type)->toBe(App\Enums\HomeSectionType::Hero)
        ->and($stored->last()->type)->toBe(App\Enums\HomeSectionType::Cta);

    $this->withoutVite()->get('/')->assertOk();
});
