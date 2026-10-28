<?php

use App\Enums\HomeSectionType;
use App\Enums\SectionShowType;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

it('casts type, show type, content and flags', function () {
    $section = HomePageSection::factory()->create([
        'type' => HomeSectionType::Services,
        'show_type' => SectionShowType::Custom,
        'content' => ['items' => [['icon' => 'sun']]],
        'limit' => '4',
    ])->fresh();

    expect($section->type)->toBe(HomeSectionType::Services)
        ->and($section->show_type)->toBe(SectionShowType::Custom)
        ->and($section->content['items'][0]['icon'])->toBe('sun')
        ->and($section->limit)->toBe(4)
        ->and($section->is_active)->toBeTrue();
});

it('translates title and subtitle', function () {
    $section = HomePageSection::factory()->create([
        'title' => ['en' => 'Popular right now', 'ar' => 'الأكثر طلبًا الآن'],
        'subtitle' => ['en' => 'Hand-picked', 'ar' => 'مختارة'],
    ]);

    expect($section->getTranslation('title', 'ar'))->toBe('الأكثر طلبًا الآن')
        ->and($section->getTranslations('subtitle'))->toHaveKeys(['en', 'ar']);
});

it('gives every section type at least one design and a default', function () {
    foreach (HomeSectionType::cases() as $type) {
        expect($type->designs())->not->toBeEmpty()
            ->and($type->defaultDesign())->toBe(array_key_first($type->designs()));
    }
});

it('offers several designs for the visual section types', function () {
    expect(HomeSectionType::Hero->designs())->toHaveCount(3)
        ->and(HomeSectionType::Products->designs())->toHaveKeys(['grid', 'carousel'])
        ->and(HomeSectionType::Brands->designs())->toHaveKeys(['logos', 'marquee']);
});

it('falls back to the default design when the stored one is unknown', function () {
    $section = HomePageSection::factory()->ofType(HomeSectionType::Brands)->make(['design' => 'does-not-exist']);

    expect($section->designKey())->toBe('logos');
});

it('lists active sections in order', function () {
    HomePageSection::factory()->create(['sort_order' => 2, 'title' => ['en' => 'C']]);
    HomePageSection::factory()->create(['sort_order' => 1, 'title' => ['en' => 'B']]);
    HomePageSection::factory()->create(['sort_order' => 0, 'title' => ['en' => 'A'], 'is_active' => false]);

    $titles = HomePageSection::active()->ordered()->get()->map(fn ($s) => $s->getTranslation('title', 'en'));

    expect($titles->all())->toBe(['B', 'C']);
});

it('fills a section with the latest active products up to the limit', function () {
    Product::factory()->count(5)->create();
    Product::factory()->create(['is_active' => false]);

    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)->create(['limit' => 3]);

    expect($section->resolveItems())->toHaveCount(3);
});

it('puts featured items first and tops up with the latest visible ones', function () {
    $featured = Product::factory()->count(2)->create(['is_featured' => true]);
    $latest = Product::factory()->count(3)->create(['is_featured' => false]);
    Product::factory()->create(['is_featured' => false, 'is_active' => false]);

    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)
        ->create(['show_type' => SectionShowType::Featured, 'limit' => 4]);

    $ids = $section->resolveItems()->pluck('id');

    expect($ids)->toHaveCount(4)
        ->and($ids->take(2)->all())->toEqualCanonicalizing($featured->pluck('id')->all())
        // newest non-featured first, inactive never
        ->and($ids->slice(2)->values()->all())->toBe([$latest[2]->id, $latest[1]->id]);
});

it('shows a newly enabled product in a featured block even when nothing is featured', function () {
    $product = Product::factory()->create(['is_featured' => false, 'is_active' => true]);

    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)
        ->create(['show_type' => SectionShowType::Featured, 'limit' => 8]);

    expect($section->resolveItems()->pluck('id')->all())->toBe([$product->id]);
});

it('does not repeat a featured item when topping up', function () {
    Product::factory()->count(2)->create(['is_featured' => true]);
    Product::factory()->count(2)->create(['is_featured' => false]);

    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)
        ->create(['show_type' => SectionShowType::Featured, 'limit' => 10]);

    $ids = $section->resolveItems()->pluck('id');

    expect($ids)->toHaveCount(4)->and($ids->unique())->toHaveCount(4);
});

it('uses hand-picked items in the pivot order and skips inactive ones', function () {
    [$a, $b, $c, $inactive] = [
        Product::factory()->create(),
        Product::factory()->create(),
        Product::factory()->create(),
        Product::factory()->create(['is_active' => false]),
    ];
    Product::factory()->create(); // not picked

    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)
        ->create(['show_type' => SectionShowType::Custom, 'limit' => 10]);

    $section->products()->attach([
        $c->id => ['sort_order' => 1],
        $a->id => ['sort_order' => 2],
        $inactive->id => ['sort_order' => 3],
        $b->id => ['sort_order' => 4],
    ]);

    expect($section->resolveItems()->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);
});

it('supports hand-picked categories, brands, bundles and portfolio items', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $bundle = Bundle::factory()->create();
    $project = PortfolioItem::factory()->create();

    $cases = [
        [HomeSectionType::Categories, 'categories', $category],
        [HomeSectionType::Brands, 'brands', $brand],
        [HomeSectionType::Bundles, 'bundles', $bundle],
        [HomeSectionType::Portfolio, 'portfolioItems', $project],
    ];

    foreach ($cases as [$type, $relation, $record]) {
        $section = HomePageSection::factory()->ofType($type)->create(['show_type' => SectionShowType::Custom]);
        $section->{$relation}()->attach($record);

        expect($section->resolveItems()->pluck('id')->all())->toBe([$record->id]);
    }
});

it('shows only top-level categories and bundles inside their date window when latest', function () {
    $root = Category::factory()->create();
    Category::factory()->create(['parent_id' => $root->id]);

    Bundle::factory()->create();
    Bundle::factory()->create(['ends_at' => now()->subDay()]);
    Bundle::factory()->create(['starts_at' => now()->addWeek()]);

    $categories = HomePageSection::factory()->ofType(HomeSectionType::Categories)->create();
    $bundles = HomePageSection::factory()->ofType(HomeSectionType::Bundles)->create();

    expect($categories->resolveItems())->toHaveCount(1)
        ->and($bundles->resolveItems())->toHaveCount(1);
});

it('builds the storefront payload', function () {
    Product::factory()->count(2)->create(['is_featured' => true]);
    Brand::factory()->create();

    $section = HomePageSection::factory()->ofType(HomeSectionType::Hero, 'slider')->create([
        'show_type' => SectionShowType::Featured,
        'limit' => 3,
        'background_color' => '#0b1f3a',
        'content' => ['headline' => ['en' => 'Hello', 'ar' => 'مرحبا']],
    ]);

    $payload = $section->toStorefront();

    expect($payload)->toHaveKeys(['id', 'type', 'design', 'title', 'subtitle', 'background_color', 'content', 'items', 'stats'])
        ->and($payload['type'])->toBe('hero')
        ->and($payload['design'])->toBe('slider')
        ->and($payload['items'])->toHaveCount(2)
        ->and($payload['stats'])->toMatchArray(['products' => 2, 'brands' => 1, 'projects' => 0])
        ->and($payload['title'])->toHaveKeys(['en', 'ar']);
});

it('turns uploaded image paths into public URLs but leaves absolute URLs alone', function () {
    $content = HomePageSection::publicUrls([
        'slides' => [
            ['image' => 'home/slide-1.jpg', 'title' => 'One'],
            ['image' => 'https://cdn.example.com/slide-2.jpg'],
            ['image' => '/images/local.jpg'],
        ],
        'other' => 'home/not-an-image.txt',
    ]);

    expect($content['slides'][0]['image'])->toEndWith('/storage/home/slide-1.jpg')
        ->and($content['slides'][1]['image'])->toBe('https://cdn.example.com/slide-2.jpg')
        ->and($content['slides'][2]['image'])->toBe('/images/local.jpg')
        ->and($content['other'])->toBe('home/not-an-image.txt');
});

it('removes hand-picked rows when the section is deleted but keeps the products', function () {
    $product = Product::factory()->create();
    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)->create(['show_type' => SectionShowType::Custom]);
    $section->products()->attach($product);

    $section->delete();

    expect(DB::table('home_page_section_items')->count())->toBe(0)
        ->and(Product::find($product->id))->not->toBeNull();
});

it('ships a complete built-in layout that resolves on an empty database', function () {
    $sections = HomePageSection::builtIn();

    expect($sections->count())->toBeGreaterThanOrEqual(8)
        ->and($sections->first()->type)->toBe(HomeSectionType::Hero)
        ->and($sections->last()->type)->toBe(HomeSectionType::Cta);

    $sections->each(function (HomePageSection $section) {
        $payload = $section->toStorefront();

        expect($payload['type'])->toBe($section->type->value)
            ->and(array_key_exists($payload['design'], $section->type->designs()))->toBeTrue();
    });
});
