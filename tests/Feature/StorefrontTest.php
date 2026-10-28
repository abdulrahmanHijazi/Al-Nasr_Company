<?php

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Enums\HomeSectionType;
use App\Models\ContactMessage;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

it('renders the built-in home layout until sections are configured', function () {
    Product::factory()->create(['is_featured' => true]);
    Category::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Home')
            ->has('settings.site_name_en')
            ->has('sections', fn (Assert $sections) => $sections->etc())
            ->where('sections.0.type', 'hero')
            ->where('sections.0.stats.products', 1));
});

it('renders configured home sections in order and skips inactive ones', function () {
    HomePageSection::factory()->ofType(HomeSectionType::Brands, 'marquee')->create(['sort_order' => 2]);
    HomePageSection::factory()->ofType(HomeSectionType::Products, 'carousel')->create(['sort_order' => 1]);
    HomePageSection::factory()->ofType(HomeSectionType::Cta)->create(['sort_order' => 0, 'is_active' => false]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->has('sections', 2)
            ->where('sections.0.type', 'products')
            ->where('sections.0.design', 'carousel')
            ->where('sections.1.type', 'brands')
            ->where('sections.1.design', 'marquee'));
});

it('feeds a configured bundles section only with active bundles inside their date window', function () {
    HomePageSection::factory()->ofType(HomeSectionType::Bundles)->create();
    Bundle::factory()->create(['is_active' => true]);
    Bundle::factory()->create(['is_active' => false]);
    Bundle::factory()->create(['is_active' => true, 'ends_at' => now()->subDay()]);
    Bundle::factory()->create(['is_active' => true, 'starts_at' => now()->addWeek()]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('sections.0.items', 1));
});

it('lists active products with brand and category facets', function () {
    Product::factory()->count(3)->create();
    Product::factory()->create(['is_active' => false]);
    Brand::factory()->create();
    Category::factory()->create();

    $this->get('/shop')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Index')
            ->where('products.total', 3)
            ->has('brands', 1)
            ->has('categories', 1));
});

it('filters the shop by price, stock and search term', function () {
    Product::factory()->create(['name' => ['en' => 'Solar Panel 550W', 'ar' => 'لوح شمسي'], 'price' => 120, 'stock_qty' => 10]);
    Product::factory()->create(['name' => ['en' => 'Lithium Battery', 'ar' => 'بطارية'], 'price' => 900, 'stock_qty' => 0]);
    Product::factory()->create(['name' => ['en' => 'IP Camera', 'ar' => 'كاميرا'], 'price' => 60, 'stock_qty' => 5]);

    $total = fn (string $query) => $this->get("/shop?{$query}")->viewData('page')['props']['products']['total'];

    expect($total('q=solar'))->toBe(1)
        ->and($total('min_price=100'))->toBe(2)
        ->and($total('max_price=100'))->toBe(1)
        ->and($total('in_stock=1'))->toBe(2)
        ->and($total('min_price=50&max_price=200&in_stock=1'))->toBe(2);
});

it('sorts the shop by price', function () {
    Product::factory()->create(['price' => 300]);
    Product::factory()->create(['price' => 100]);
    Product::factory()->create(['price' => 200]);

    $prices = fn (string $sort) => collect($this->get("/shop?sort={$sort}")->viewData('page')['props']['products']['data'])
        ->pluck('price')->map(fn ($p) => (float) $p)->all();

    expect($prices('price_asc'))->toBe([100.0, 200.0, 300.0])
        ->and($prices('price_desc'))->toBe([300.0, 200.0, 100.0]);
});

it('filters the shop by brand slug', function () {
    $brand = Brand::factory()->create(['slug' => ['en' => 'sunvolt', 'ar' => 'sunvolt']]);
    Product::factory()->count(2)->create(['brand_id' => $brand->id]);
    Product::factory()->create();

    $this->get('/shop?brand[]=sunvolt')
        ->assertInertia(fn (Assert $page) => $page->where('products.total', 2));
});

it('rejects an invalid sort option', function () {
    $this->get('/shop?sort=bogus')->assertSessionHasErrors('sort');
});

it('shows a product with its brand, categories and related products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['slug' => ['en' => 'hybrid-inverter-5kw', 'ar' => 'hybrid-inverter-5kw']]);
    $product->categories()->attach($category);
    Product::factory()->count(2)->create()->each(fn ($p) => $p->categories()->attach($category));

    $this->get('/products/hybrid-inverter-5kw')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Show')
            ->where('product.sku', $product->sku)
            ->has('product.categories', 1)
            ->has('related', 2));
});

it('returns 404 for an inactive or unknown product', function () {
    Product::factory()->create(['slug' => ['en' => 'hidden', 'ar' => 'hidden'], 'is_active' => false]);

    $this->get('/products/hidden')->assertNotFound();
    $this->get('/products/does-not-exist')->assertNotFound();
});

it('shows brand, category, portfolio and offer detail pages', function () {
    $brand = Brand::factory()->create(['slug' => ['en' => 'sunvolt', 'ar' => 'sunvolt']]);
    $category = Category::factory()->create(['slug' => ['en' => 'solar-panels', 'ar' => 'solar-panels']]);
    PortfolioItem::factory()->create(['slug' => ['en' => 'rooftop-plant', 'ar' => 'rooftop-plant']]);
    Bundle::factory()->create(['slug' => ['en' => 'starter-kit', 'ar' => 'starter-kit']]);

    $this->get('/brands')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Brands/Index'));
    $this->get('/brands/sunvolt')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Brands/Show')->where('brand.id', $brand->id));
    $this->get('/categories/solar-panels')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Categories/Show')->where('category.id', $category->id));
    $this->get('/portfolio')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Portfolio/Index'));
    $this->get('/portfolio/rooftop-plant')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Portfolio/Show'));
    $this->get('/offers')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Offers/Index'));
    $this->get('/offers/starter-kit')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Offers/Show'));
});

it('renders the contact page', function () {
    $this->get('/contact')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Contact/Index'));
});

it('stores a contact message and flashes a success notice', function () {
    $this->from('/contact')
        ->post('/contact', [
            'name' => 'Khalid',
            'email' => 'khalid@example.com',
            'phone' => '+966 50 000 0000',
            'subject' => 'Quote: 10 kW solar system',
            'message' => 'Please send a quote for a 10 kW system.',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(1)
        ->and(ContactMessage::first()->is_read)->toBeFalse();
});

it('validates the contact form', function () {
    $this->from('/contact')
        ->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => 'hi'])
        ->assertRedirect('/contact')
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactMessage::count())->toBe(0);
});

it('resolves a translated slug in any locale', function () {
    Product::factory()->create(['slug' => ['en' => 'solar-panel-550w', 'ar' => 'لوح-شمسي-550']]);

    $this->get('/products/solar-panel-550w')->assertOk();
    $this->get('/products/'.rawurlencode('لوح-شمسي-550'))->assertOk();
});
