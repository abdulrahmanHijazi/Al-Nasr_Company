<?php

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Bundles\Pages\CreateBundle;
use App\Filament\Resources\Bundles\Pages\EditBundle;
use App\Filament\Resources\Bundles\Pages\ListBundles;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\HomePageSections\Pages\CreateHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\EditHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\ListHomePageSections;
use App\Filament\Resources\PortfolioItems\Pages\CreatePortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\EditPortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\ListPortfolioItems;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Widgets\LatestMessages;
use App\Filament\Widgets\ProductsByCategoryChart;
use App\Filament\Widgets\StoreStatsOverview;
use App\Enums\HomeSectionType;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use Filament\Facades\Filament;
use Livewire\Livewire;

/**
 * Every string the admin shows must have an Arabic translation. The test renders
 * every admin page in Arabic while recording translation keys that fall through
 * to the key itself, then fails listing them.
 */
beforeEach(function () {
    config(['app.locale' => 'ar']);
    app()->setLocale('ar');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function arabicMissingKeys(Closure $visit): array
{
    $missing = [];

    app('translator')->handleMissingKeysUsing(function (string $key) use (&$missing) {
        // Already-Arabic text passed through a second __() is fine; so are vendor namespaces.
        if (! preg_match('/\p{Arabic}/u', $key) && ! str_contains($key, '::')) {
            $missing[$key] = true;
        }
    });

    $visit();

    app('translator')->handleMissingKeysUsing(null);

    ksort($missing);

    return array_keys($missing);
}

it('has Arabic translations for every admin string', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $bundle = Bundle::factory()->create();
    $bundle->products()->attach($product, ['qty' => 2]);
    $project = PortfolioItem::factory()->create();
    $message = ContactMessage::factory()->create();
    $section = HomePageSection::factory()->ofType(HomeSectionType::Products)->create(['model_3d' => 'library:'.($library = App\Models\ThreeDModel::factory()->create())->slug]);

    $missing = arabicMissingKeys(function () use ($brand, $category, $product, $bundle, $project, $message, $section, $library) {
        $this->actingAs(adminUser());

        // full-page requests: navigation, dashboard, shield
        foreach (['/admin', '/admin/shield/roles', '/admin/shield/roles/create'] as $url) {
            $this->get($url)->assertOk();
        }

        // dashboard widgets
        foreach ([StoreStatsOverview::class, LatestMessages::class, ProductsByCategoryChart::class] as $widget) {
            Livewire::test($widget)->assertOk();
        }

        // lists
        foreach ([ListBrands::class, ListCategories::class, ListProducts::class, ListPortfolioItems::class,
            ListBundles::class, ListContactMessages::class, ListHomePageSections::class,
            App\Filament\Resources\ThreeDModels\Pages\ListThreeDModels::class] as $page) {
            Livewire::test($page)->assertOk();
        }

        // create / edit forms (every field, section, helper text)
        foreach ([CreateBrand::class, CreateCategory::class, CreateProduct::class, CreatePortfolioItem::class,
            CreateBundle::class, CreateHomePageSection::class,
            App\Filament\Resources\ThreeDModels\Pages\CreateThreeDModel::class] as $page) {
            Livewire::test($page)->assertOk();
        }

        Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])->assertOk();
        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])->assertOk();
        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])->assertOk();
        Livewire::test(EditBundle::class, ['record' => $bundle->getRouteKey()])->assertOk();
        Livewire::test(EditPortfolioItem::class, ['record' => $project->getRouteKey()])->assertOk();
        Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])->assertOk();
        Livewire::test(App\Filament\Resources\ThreeDModels\Pages\EditThreeDModel::class, ['record' => $library->getRouteKey()])->assertOk();

        // read-only detail pages (infolists)
        foreach ([
            App\Filament\Resources\Brands\Pages\ViewBrand::class => $brand,
            App\Filament\Resources\Categories\Pages\ViewCategory::class => $category,
            App\Filament\Resources\Products\Pages\ViewProduct::class => $product,
            App\Filament\Resources\Bundles\Pages\ViewBundle::class => $bundle,
            App\Filament\Resources\PortfolioItems\Pages\ViewPortfolioItem::class => $project,
            App\Filament\Resources\HomePageSections\Pages\ViewHomePageSection::class => $section,
        ] as $page => $record) {
            Livewire::test($page, ['record' => $record->getRouteKey()])->assertOk();
        }

        // every section type shows its own content group
        foreach (HomeSectionType::cases() as $type) {
            $typed = HomePageSection::factory()->ofType($type)->create();
            Livewire::test(EditHomePageSection::class, ['record' => $typed->getRouteKey()])->assertOk();
        }
        Livewire::test(EditHomePageSection::class, ['record' => $section->getRouteKey()])->assertOk();

        Livewire::test(ManageSiteSettings::class)->assertOk();
    });

    expect($missing)->toBe([], "Untranslated admin strings:\n".implode("\n", $missing));
});

it('translates the enum labels used in section forms', function () {
    foreach (HomeSectionType::cases() as $type) {
        expect($type->label())->toMatch('/\p{Arabic}/u');

        foreach ($type->designs() as $label) {
            expect($label)->toMatch('/\p{Arabic}/u');
        }
    }

    foreach (App\Enums\SectionShowType::cases() as $showType) {
        expect($showType->label())->toMatch('/\p{Arabic}/u');
    }
});

it('serves the admin in Arabic, right-to-left, with the language switcher', function () {
    $html = $this->actingAs(adminUser())->get('/admin')->assertOk()->getContent();

    expect($html)->toContain('dir="rtl"')->toContain('lang="ar"');
});

it('renders the language switcher in the panel', function () {
    $html = $this->actingAs(adminUser())->get('/admin')->assertOk()->getContent();

    expect($html)->toContain('language-switch');
});

it('lets the administrator switch to English', function () {
    $this->actingAs(adminUser())
        ->get('/admin?locale=en')
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false);
});

it('shows validation errors in Arabic instead of raw keys', function () {
    $this->actingAs(adminUser());

    $component = Livewire::test(CreateBrand::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);

    $message = collect($component->errors()->all())->implode(' ');

    expect($message)->toMatch('/\p{Arabic}/u')->not->toContain('validation.');
});

it('translates every Laravel validation rule to Arabic', function () {
    $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $arabic = require lang_path('ar/validation.php');

    $missing = array_diff(array_keys($english), array_keys($arabic));

    expect($missing)->toBe([], 'Missing validation keys: '.implode(', ', $missing));
});
