<?php

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('inserts the demo catalogue, offers, projects, messages and home layout', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    expect(Brand::count())->toBe(11)
        ->and(Category::count())->toBe(15)
        ->and(Product::count())->toBe(35)
        ->and(Bundle::count())->toBe(4)
        ->and(PortfolioItem::count())->toBe(5)
        ->and(ContactMessage::count())->toBe(12)
        ->and(HomePageSection::count())->toBe(count(HomePageSection::defaults()));
});

it('creates a category tree, brand links and bundle quantities', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    $inverters = Category::where('slug->en', 'inverters')->firstOrFail();
    $kit = Bundle::where('slug->en', 'hybrid-backup-kit-5kw')->with('products')->firstOrFail();
    $inverter = Product::where('sku', 'VLT-AXK5')->firstOrFail();

    expect($inverters->children)->toHaveCount(3)
        ->and($inverter->brand->getTranslation('name', 'en'))->toBe('Voltronic')
        ->and($inverter->price)->toBe('690.00')
        ->and($kit->products)->toHaveCount(5)
        ->and($kit->products->firstWhere('sku', 'TBC-48100')->pivot->qty)->toBe(2)
        ->and($kit->savings)->toBeGreaterThan(0.0);
});

it('is safe to run twice without duplicating anything', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();
    $first = [Brand::count(), Category::count(), Product::count(), Bundle::count(), PortfolioItem::count(), ContactMessage::count(), HomePageSection::count()];

    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    expect([Brand::count(), Category::count(), Product::count(), Bundle::count(), PortfolioItem::count(), ContactMessage::count(), HomePageSection::count()])
        ->toBe($first);
});

it('keeps admin edits to demo products on re-run only for fields it does not manage', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    Product::where('sku', 'VLT-AXK5')->update(['stock_qty' => 3]);
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    // the demo data is the source of truth for the products it ships
    expect(Product::where('sku', 'VLT-AXK5')->value('stock_qty'))->toBe(24);
});

it('adds filler products on request', function () {
    $this->artisan('demo:seed', ['--no-images' => true, '--extra' => 30])->assertSuccessful();

    expect(Product::count())->toBe(65);
});

it('limits the number of contact messages', function () {
    $this->artisan('demo:seed', ['--no-images' => true, '--messages' => 3])->assertSuccessful();

    expect(ContactMessage::count())->toBe(3);
});

it('attaches generated images to products, brands, categories, bundles and projects', function () {
    $this->artisan('demo:seed')->assertSuccessful();

    $product = Product::where('sku', 'TBC-48100')->firstOrFail();
    $brand = Brand::where('slug->en', 'tobac')->firstOrFail();
    $category = Category::where('slug->en', 'rack-batteries')->firstOrFail();
    $bundle = Bundle::where('slug->en', 'protection-panel-pack')->firstOrFail();
    $project = PortfolioItem::where('slug->en', 'rooftop-solar-logistics-hub')->firstOrFail();

    expect($product->cover_url)->toBeString()
        ->and($brand->logo_url)->toBeString()
        ->and($category->image_url)->toBeString()
        ->and($category->banner_url)->toBeString()
        ->and($bundle->cover_url)->toBeString()
        ->and($project->cover_url)->toBeString()
        ->and($project->gallery)->toHaveCount(2);

    Storage::disk('public')->assertExists($product->getFirstMedia('cover')->getPathRelativeToRoot());
});

it('does not attach images a second time', function () {
    $this->artisan('demo:seed')->assertSuccessful();
    $media = Spatie\MediaLibrary\MediaCollections\Models\Media::count();

    $this->artisan('demo:seed')->assertSuccessful();

    expect(Spatie\MediaLibrary\MediaCollections\Models\Media::count())->toBe($media);
});

it('fills the contact details only when they are still empty', function () {
    $settings = app(SiteSettings::class);
    $settings->contact_phone = '+966 55 555 5555';
    $settings->save();

    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    $fresh = app(SiteSettings::class);

    expect($fresh->contact_phone)->toBe('+966 55 555 5555')
        ->and($fresh->contact_email)->toBe('sales@alnasr.example')
        ->and($fresh->latitude)->toBe(24.7136);
});

it('wipes existing data with --fresh --force', function () {
    Product::factory()->count(3)->create();
    Brand::factory()->count(2)->create();
    ContactMessage::factory()->count(4)->create();

    $this->artisan('demo:seed', ['--no-images' => true, '--fresh' => true, '--force' => true, '--messages' => 2])
        ->assertSuccessful();

    expect(Product::count())->toBe(35)
        ->and(Brand::count())->toBe(11)
        ->and(ContactMessage::count())->toBe(2);
});

it('refuses --fresh non-interactively without --force', function () {
    Product::factory()->count(2)->create();

    $this->artisan('demo:seed', ['--no-images' => true, '--fresh' => true, '--no-interaction' => true])
        ->assertFailed();

    expect(Product::count())->toBe(2);
});

it('refuses to run in production without --force', function () {
    $this->app['env'] = 'production';

    $this->artisan('demo:seed', ['--no-images' => true])->assertFailed();

    expect(Product::count())->toBe(0);
});

it('renders the storefront with demo data', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();
    $this->withoutVite();

    $this->get('/')->assertOk();
    $this->get('/shop')->assertOk()->assertInertia(fn ($page) => $page->where('products.total', 35));
    $this->get('/products/'.Product::where('sku', 'VLT-AXK5')->first()->getTranslation('slug', 'en'))->assertOk();
    $this->get('/offers/hybrid-backup-kit-5kw')->assertOk();
    $this->get('/brands/voltronic')->assertOk();
    $this->get('/portfolio/rooftop-solar-logistics-hub')->assertOk();
});

it('stocks inverters, lithium batteries, charge controllers and every breaker type from the requested brands', function () {
    $this->artisan('demo:seed', ['--no-images' => true])->assertSuccessful();

    $brands = Brand::all()->map(fn ($b) => $b->getTranslation('name', 'en'))->all();

    expect($brands)->toContain('Voltronic', 'Felicity', 'Solis', 'Tobac', 'EPever', 'Victron Energy');

    $inCategory = fn (string $slug) => Product::whereHas('categories', fn ($q) => $q->where('slug->en', $slug))->with('brand')->get();

    expect($inCategory('hybrid-inverters')->pluck('brand.name')->map(fn ($n) => $n)->unique()->count())->toBeGreaterThanOrEqual(3)
        ->and($inCategory('rack-batteries')->contains(fn ($p) => $p->brand->getTranslation('name', 'en') === 'Tobac'))->toBeTrue()
        ->and($inCategory('wall-batteries')->contains(fn ($p) => $p->brand->getTranslation('name', 'en') === 'Felicity'))->toBeTrue()
        ->and($inCategory('mppt-controllers'))->not->toBeEmpty()
        ->and($inCategory('pwm-controllers'))->not->toBeEmpty();

    // breakers: single to four pole MCBs, DC, MCCB and shunt-trip
    foreach (['mcb-breakers' => 4, 'dc-breakers' => 2, 'mccb-breakers' => 2, 'shunt-trip-breakers' => 3] as $slug => $min) {
        expect($inCategory($slug)->count())->toBeGreaterThanOrEqual($min, $slug);
    }

    expect(Product::where('name->en', 'like', '%1-Pole%')->exists())->toBeTrue()
        ->and(Product::where('name->en', 'like', '%4-Pole%')->exists())->toBeTrue();
});

it('has a 3D library model for every product family it sells', function () {
    $families = collect(App\Support\Glb\Library::entries())->pluck('type')->map->value->unique()->all();

    expect($families)->toContain('inverter', 'battery', 'charge-controller', 'breaker');
});
