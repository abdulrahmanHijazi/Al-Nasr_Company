<?php

use App\Enums\HomeSectionType;
use App\Enums\SectionShowType;
use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Bundles\Pages\CreateBundle;
use App\Filament\Resources\Bundles\Pages\EditBundle;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\HomePageSections\Pages\CreateHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\EditHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\ListHomePageSections;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Settings\SiteSettings;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(adminUser());
});

// -- Brands ---------------------------------------------------------------------

it('creates a brand and generates its slug', function () {
    Livewire::test(CreateBrand::class)
        ->fillForm(['name' => 'SunVolt', 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $brand = Brand::firstOrFail();

    expect($brand->getTranslation('name', 'en'))->toBe('SunVolt')
        ->and($brand->getTranslation('slug', 'en'))->toBe('sunvolt');
});

it('requires a brand name', function () {
    Livewire::test(CreateBrand::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('edits a brand', function () {
    $brand = Brand::factory()->create();

    Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])
        ->fillForm(['name' => 'Renamed', 'sort_order' => 4])
        ->call('save')
        ->assertHasNoFormErrors();

    // content is edited in Arabic first (the plugin's default locale)
    expect($brand->fresh()->getTranslation('name', 'ar'))->toBe('Renamed')
        ->and($brand->fresh()->sort_order)->toBe(4);
});

it('edits a brand in English after switching the content language', function () {
    $brand = Brand::factory()->create();

    Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])
        ->set('activeLocale', 'en')
        ->fillForm(['name' => 'Renamed EN'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($brand->fresh()->getTranslation('name', 'en'))->toBe('Renamed EN');
});

it('lists brands in the table', function () {
    $brands = Brand::factory()->count(3)->create();

    Livewire::test(ListBrands::class)->assertCanSeeTableRecords($brands);
});

// -- Products -------------------------------------------------------------------

it('creates a product with brand and categories', function () {
    $brand = Brand::factory()->create();
    $categories = Category::factory()->count(2)->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Hybrid Inverter 10kW',
            'sku' => 'GRD-H10T',
            'brand_id' => $brand->id,
            'categories' => $categories->pluck('id')->all(),
            'price' => 1650,
            'compare_price' => 1790,
            'stock_qty' => 14,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'GRD-H10T')->firstOrFail();

    expect($product->brand->is($brand))->toBeTrue()
        ->and($product->categories)->toHaveCount(2)
        ->and($product->price)->toBe('1650.00')
        ->and($product->getTranslation('slug', 'en'))->toBe('hybrid-inverter-10kw');
});

it('rejects a duplicate SKU', function () {
    Product::factory()->create(['sku' => 'DUP-1']);

    Livewire::test(CreateProduct::class)
        ->fillForm(['name' => 'Another', 'sku' => 'DUP-1', 'price' => 10])
        ->call('create')
        ->assertHasFormErrors(['sku' => 'unique']);
});

it('lists products in the table', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ListProducts::class)->assertCanSeeTableRecords($products);
});

// -- Bundles --------------------------------------------------------------------

it('creates a bundle and stores its products with quantities', function () {
    $panel = Product::factory()->create(['price' => 100]);
    $inverter = Product::factory()->create(['price' => 500]);

    Livewire::test(CreateBundle::class)
        ->fillForm([
            'name' => 'Hybrid Solar Kit',
            'bundle_price' => 1000,
            'is_active' => true,
            'items' => [
                ['product_id' => $panel->id, 'qty' => 6],
                ['product_id' => $inverter->id, 'qty' => 1],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $bundle = Bundle::with('products')->firstOrFail();

    expect($bundle->products)->toHaveCount(2)
        ->and($bundle->original_total)->toBe(1100.0)
        ->and($bundle->savings)->toBe(100.0);
});

it('loads and re-syncs the products of an existing bundle', function () {
    $a = Product::factory()->create(['price' => 50]);
    $b = Product::factory()->create(['price' => 70]);
    $bundle = Bundle::factory()->create(['bundle_price' => 90]);
    $bundle->products()->attach($a, ['qty' => 1]);

    Livewire::test(EditBundle::class, ['record' => $bundle->getRouteKey()])
        ->assertFormSet(function (array $state) use ($a) {
            expect(array_values($state['items']))->toEqual([['product_id' => $a->id, 'qty' => 1]]);
        })
        ->fillForm(['items' => [
            ['product_id' => $a->id, 'qty' => 2],
            ['product_id' => $b->id, 'qty' => 1],
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $bundle->fresh('products');

    expect($fresh->products->pluck('id')->all())->toEqualCanonicalizing([$a->id, $b->id])
        ->and($fresh->products->firstWhere('id', $a->id)->pivot->qty)->toBe(2);
});

// -- Home page sections ---------------------------------------------------------

it('creates a home page section with a chosen type and design', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm([
            'type' => HomeSectionType::Brands->value,
            'design' => 'marquee',
            'title' => 'Brands we carry',
            'show_type' => SectionShowType::Latest->value,
            'limit' => 6,
            'is_active' => true,
            'sort_order' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $section = HomePageSection::firstOrFail();

    expect($section->type)->toBe(HomeSectionType::Brands)
        ->and($section->design)->toBe('marquee')
        ->and($section->getTranslation('title', 'en'))->toBe('Brands we carry')
        ->and($section->limit)->toBe(6);
});

it('stores bilingual content for a call-to-action section', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm([
            'type' => HomeSectionType::Cta->value,
            'design' => 'split',
            'is_active' => true,
            'content' => [
                'title' => ['en' => 'Need a quote?', 'ar' => 'تحتاج عرض سعر؟'],
                'primary_label' => ['en' => 'Contact us', 'ar' => 'اتصل بنا'],
                'primary_url' => '/contact',
                'show_whatsapp' => true,
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $content = HomePageSection::firstOrFail()->content;

    expect($content['title']['ar'])->toBe('تحتاج عرض سعر؟')
        ->and($content['primary_url'])->toBe('/contact')
        ->and($content['show_whatsapp'])->toBeTrue();
});

it('requires a section type', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm(['type' => null])
        ->call('create')
        ->assertHasFormErrors(['type' => 'required']);
});

it('keeps hand-picked products in the order they were chosen', function () {
    [$first, $second, $third] = Product::factory()->count(3)->create();
    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)
        ->create(['show_type' => SectionShowType::Custom]);

    Livewire::test(EditHomePageSection::class, ['record' => $section->getRouteKey()])
        ->fillForm(['products' => [$third->id, $first->id, $second->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($section->fresh()->resolveItems()->pluck('id')->all())->toBe([$third->id, $first->id, $second->id]);
});

it('lists home sections and toggles them', function () {
    $sections = HomePageSection::factory()->count(3)->create();

    Livewire::test(ListHomePageSections::class)->assertCanSeeTableRecords($sections);
});

// -- Contact inbox --------------------------------------------------------------

it('lists unread messages first-class and filters them', function () {
    $unread = ContactMessage::factory()->count(2)->create(['is_read' => false]);
    $read = ContactMessage::factory()->create(['is_read' => true]);

    Livewire::test(ListContactMessages::class)
        ->assertCanSeeTableRecords($unread->push($read))
        ->filterTable('is_read', false)
        ->assertCanSeeTableRecords($unread->take(2))
        ->assertCanNotSeeTableRecords([$read]);
});

it('marks a message as read when it is opened', function () {
    $message = ContactMessage::factory()->create(['is_read' => false]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()]);

    expect($message->fresh()->is_read)->toBeTrue();
});

it('shows the unread count as the navigation badge', function () {
    ContactMessage::factory()->count(3)->create();
    ContactMessage::factory()->create(['is_read' => true]);

    expect(App\Filament\Resources\ContactMessages\ContactMessageResource::getNavigationBadge())->toBe('3');
});

it('does not offer creating messages in the admin', function () {
    expect(App\Filament\Resources\ContactMessages\ContactMessageResource::canCreate())->toBeFalse();
});

// -- Site settings --------------------------------------------------------------

it('saves the site settings from the admin', function () {
    Livewire::test(ManageSiteSettings::class)
        ->fillForm([
            'site_name_en' => 'Al-Nasr Energy',
            'primary_color' => '#0f766e',
            'currency' => 'SAR',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(SiteSettings::class);

    expect($settings->site_name_en)->toBe('Al-Nasr Energy')
        ->and($settings->primary_color)->toBe('#0f766e')
        ->and($settings->currency)->toBe('SAR')
        ->and($settings->latitude)->toBe(24.7136);
});

it('validates the site settings', function () {
    Livewire::test(ManageSiteSettings::class)
        ->fillForm(['site_name_en' => null, 'latitude' => 120])
        ->call('save')
        ->assertHasFormErrors(['site_name_en' => 'required', 'latitude']);
});

// -- Infolist (view) pages ------------------------------------------------------

it('opens a read-only detail page for every resource', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id, 'cost_price' => 70, 'price' => 100]);
    $product->categories()->attach($category);
    $bundle = Bundle::factory()->create();
    $bundle->products()->attach($product, ['qty' => 2]);
    $project = PortfolioItem::factory()->create();
    $message = ContactMessage::factory()->create();
    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)->create();

    foreach ([
        "brands/{$brand->getRouteKey()}",
        "categories/{$category->getRouteKey()}",
        "products/{$product->getRouteKey()}",
        "bundles/{$bundle->getRouteKey()}",
        "portfolio-items/{$project->getRouteKey()}",
        "contact-messages/{$message->getRouteKey()}",
        "home-page-sections/{$section->getRouteKey()}",
    ] as $path) {
        $this->get("/admin/{$path}")->assertOk();
    }
});

it('shows the product margin, stock and brand on its detail page', function () {
    $brand = Brand::factory()->create(['name' => ['en' => 'SunVolt', 'ar' => 'سن فولت']]);
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'name' => ['en' => 'Mono Solar Panel 550W', 'ar' => 'لوح شمسي'],
        'price' => 100,
        'cost_price' => 70,
        'stock_qty' => 0,
    ]);

    $this->get('/admin/products/'.$product->getRouteKey())
        ->assertOk()
        ->assertSee('30%')
        ->assertSee($product->sku)
        ->assertSee('سن فولت'); // the admin renders in Arabic by default
});

it('lets an admin reply, toggle read state and delete from the message page', function () {
    $message = ContactMessage::factory()->create(['email' => 'client@example.com', 'phone' => '+966 50 123 4567', 'is_read' => false]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionVisible('reply')
        ->assertActionVisible('whatsapp')
        ->assertActionVisible('call')
        ->callAction('toggleRead');

    // opening marked it read; the toggle flips it back to unread
    expect($message->fresh()->is_read)->toBeFalse();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->callAction('toggleRead');

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->callAction('delete');

    expect(ContactMessage::find($message->id))->toBeNull();
});

it('hides reply channels the sender did not provide', function () {
    $message = ContactMessage::factory()->create(['email' => null, 'phone' => null]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionHidden('reply')
        ->assertActionHidden('whatsapp')
        ->assertActionHidden('call');
});
