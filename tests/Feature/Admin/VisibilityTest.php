<?php

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Product;
use Filament\Facades\Filament;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(adminUser());
    $this->withoutVite();
});

it('shows a product on the storefront as soon as "Visible on the storefront" is on', function () {
    // The admin edits content in Arabic first, so only the Arabic text exists.
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'بطارية ليثيوم ٤٨ فولت',
            'sku' => 'VIS-001',
            'price' => 500,
            'stock_qty' => 5,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'VIS-001')->firstOrFail();

    foreach (['en', 'ar'] as $locale) {
        app()->setLocale($locale);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.total', 1)
                ->where('products.data.0.sku', 'VIS-001')
                // a readable name and a working link in every storefront language
                ->where('products.data.0.name', fn ($name) => filled($name))
                ->where('products.data.0.slug', fn ($slug) => filled($slug)));

        $slug = $product->fresh()->slug;
        $this->get('/products/'.rawurlencode($slug))->assertOk();
    }
});

it('keeps a product off the storefront while the toggle is off and shows it once switched on', function () {
    $product = Product::factory()->create(['is_active' => false]);

    $this->get('/shop')->assertInertia(fn (Assert $page) => $page->where('products.total', 0));
    $this->get('/products/'.$product->getTranslation('slug', 'en'))->assertNotFound();

    // flip the switch the way the admin list does
    Livewire::test(ListProducts::class)
        ->call('updateTableColumnState', 'is_active', $product->getKey(), true);

    expect($product->fresh()->is_active)->toBeTrue();

    $this->get('/shop')->assertInertia(fn (Assert $page) => $page->where('products.total', 1));
    $this->get('/products/'.$product->getTranslation('slug', 'en'))->assertOk();
});

it('shows a brand created in Arabic only on the storefront brand pages', function () {
    Livewire::test(CreateBrand::class)
        ->fillForm(['name' => 'سن فولت', 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $brand = Brand::firstOrFail();

    app()->setLocale('en');

    $this->get('/brands')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('brands', 1)
        ->where('brands.0.name', fn ($name) => filled($name)));

    $this->get('/brands/'.rawurlencode($brand->slug))->assertOk();
});

it('hides a brand when its toggle is switched off', function () {
    $brand = Brand::factory()->create(['is_active' => true]);

    Livewire::test(ListBrands::class)
        ->call('updateTableColumnState', 'is_active', $brand->getKey(), false);

    $this->get('/brands')->assertInertia(fn (Assert $page) => $page->has('brands', 0));
});

it('offers a "View on storefront" shortcut that points at the live page', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $brand = Brand::factory()->create(['is_active' => true]);

    expect($product->storefrontUrl())->toEndWith('/products/'.rawurlencode($product->slug))
        ->and($brand->storefrontUrl())->toEndWith('/brands/'.rawurlencode($brand->slug));

    Livewire::test(App\Filament\Resources\Products\Pages\ViewProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionVisible('viewOnStorefront');

    // the link really resolves on the storefront
    $path = parse_url($product->storefrontUrl(), PHP_URL_PATH);
    $this->get(rawurldecode($path))->assertOk();
});

it('hides the shortcut while the item is not visible on the storefront', function () {
    $product = Product::factory()->create(['is_active' => false]);

    Livewire::test(App\Filament\Resources\Products\Pages\ViewProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionHidden('viewOnStorefront');
});

it('shows a newly enabled, non-featured product in the home page "Popular" block', function () {
    $product = Product::factory()->create(['is_active' => true, 'is_featured' => false]);

    // no sections saved yet → the built-in layout, whose products block is "featured first"
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('sections', fn ($sections) => collect($sections)
            ->where('type', 'products')->flatMap(fn ($s) => collect($s['items'])->pluck('id'))
            ->contains($product->id)));
});

it('shows a home section on the storefront as soon as its toggle is switched on', function () {
    $section = App\Models\HomePageSection::factory()
        ->ofType(App\Enums\HomeSectionType::Brands)
        ->create(['is_active' => false, 'title' => ['en' => 'Our brands', 'ar' => 'علاماتنا']]);

    // sections exist but none is visible → an empty home page, not the built-in layout
    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('sections', 0));

    Livewire::test(App\Filament\Resources\HomePageSections\Pages\ListHomePageSections::class)
        ->call('updateTableColumnState', 'is_active', $section->getKey(), true);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('sections', 1)
        ->where('sections.0.type', 'brands'));
});

it('uses the built-in home layout only until the first section is created', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('sections.0.type', 'hero'));

    App\Models\HomePageSection::factory()->ofType(App\Enums\HomeSectionType::Cta)->create();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('sections', 1)
        ->where('sections.0.type', 'cta'));
});
