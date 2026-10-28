<?php

use App\Enums\Background3dModel;
use App\Enums\HomeSectionType;
use App\Filament\Resources\HomePageSections\HomePageSectionResource;
use App\Filament\Resources\HomePageSections\Pages\CreateHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\EditHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\ListHomePageSections;
use App\Filament\Resources\HomePageSections\Pages\ViewHomePageSection;
use App\Models\HomePageSection;
use App\Support\Model3dChoices;
use App\Support\ModelPreview;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(adminUser());
});

it('has no 3D model unless one is chosen', function () {
    $section = HomePageSection::factory()->create();

    expect($section->model_3d)->toBeNull()
        ->and($section->model3dPayload())->toBeNull();
});

it('exposes the chosen model to the storefront', function () {
    HomePageSection::factory()->ofType(HomeSectionType::Services)->create(['model_3d' => 'inverter', 'model_3d_side' => 'start']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('sections.0.model_3d.model', 'inverter')
        ->where('sections.0.model_3d.side', 'start')
        ->where('sections.0.model_3d.url', null));
});

it('treats "none" and blank as no model', function (?string $value) {
    $section = HomePageSection::factory()->create(['model_3d' => $value]);

    expect($section->fresh()->model_3d)->toBeNull();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('sections.0.model_3d', null));
})->with(['none', '', null]);

it('ignores a model key it does not know', function () {
    $section = HomePageSection::factory()->create(['model_3d' => 'teapot']);

    expect($section->model3dPayload())->toBeNull();
});

it('needs an uploaded file for "my own model"', function () {
    $section = HomePageSection::factory()->create(['model_3d' => 'custom']);

    expect($section->model3dPayload())->toBeNull();

    $section->update(['model_3d_file' => 'home/models/pump.glb']);

    expect($section->fresh()->model3dPayload())
        ->toMatchArray(['model' => 'custom', 'side' => 'end'])
        ->and($section->fresh()->model3dPayload()['url'])->toEndWith('/storage/home/models/pump.glb');
});

it('falls back to the end side for an invalid side', function () {
    $section = HomePageSection::factory()->create(['model_3d' => 'battery', 'model_3d_side' => 'up']);

    expect($section->model3dPayload()['side'])->toBe('end');
});

it('offers "no model" first and every built-in model with a picture', function () {
    $options = Model3dChoices::options();

    expect(array_key_first($options))->toBe('none')
        ->and(array_keys($options))->toBe(['none', ...Background3dModel::values()]);

    foreach ($options as $key => $option) {
        $file = public_path(parse_url($option['image'], PHP_URL_PATH));
        expect(is_file($file))->toBeTrue("picture for {$key} is missing — run php artisan design:previews");
    }
});

it('lets the admin pick a model when creating a section', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm([
            'type' => HomeSectionType::Portfolio->value,
            'design' => 'grid',
            'model_3d' => 'wind-turbine',
            'model_3d_side' => 'start',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $section = HomePageSection::firstOrFail();

    expect($section->model_3d)->toBe('wind-turbine')
        ->and($section->model_3d_side)->toBe('start');
});

it('saves "no 3D model" as null and pre-selects it when editing', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm(['type' => HomeSectionType::Brands->value, 'design' => 'logos', 'model_3d' => 'none', 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $section = HomePageSection::firstOrFail();

    expect($section->getRawOriginal('model_3d'))->toBeNull();

    Livewire::test(EditHomePageSection::class, ['record' => $section->getRouteKey()])
        ->assertFormSet(['model_3d' => 'none']);
});

it('changes the model of an existing section', function () {
    $section = HomePageSection::factory()->create(['model_3d' => 'battery']);

    Livewire::test(EditHomePageSection::class, ['record' => $section->getRouteKey()])
        ->assertFormSet(['model_3d' => 'battery'])
        ->fillForm(['model_3d' => 'cctv-camera'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($section->fresh()->model_3d)->toBe('cctv-camera');
});

it('rejects an unknown model and requires a file for "my own model"', function () {
    Livewire::test(CreateHomePageSection::class)
        ->fillForm(['type' => HomeSectionType::Brands->value, 'design' => 'logos', 'model_3d' => 'teapot'])
        ->call('create')
        ->assertHasFormErrors(['model_3d']);

    Livewire::test(CreateHomePageSection::class)
        ->fillForm(['type' => HomeSectionType::Brands->value, 'design' => 'logos', 'model_3d' => 'custom'])
        ->call('create')
        ->assertHasFormErrors(['model_3d_file' => 'required']);
});

it('shows and filters the model in the sections list and on the detail page', function () {
    $withModel = HomePageSection::factory()->create(['model_3d' => 'solar-panel']);
    $flat = HomePageSection::factory()->create();

    Livewire::test(ListHomePageSections::class)
        ->assertCanSeeTableRecords([$withModel, $flat])
        ->filterTable('model_3d', 'solar-panel')
        ->assertCanSeeTableRecords([$withModel])
        ->assertCanNotSeeTableRecords([$flat]);

    Livewire::test(ViewHomePageSection::class, ['record' => $withModel->getRouteKey()])->assertOk();
    Livewire::test(ViewHomePageSection::class, ['record' => $flat->getRouteKey()])->assertOk();

    expect(HomePageSectionResource::getUrl('view', ['record' => $withModel]))->toBeString();
});

// -- seeded data ----------------------------------------------------------------

it('seeds every section type with content and a valid default model', function () {
    $this->seed(DatabaseSeeder::class);

    $stored = HomePageSection::all();

    // every section type the storefront can render is present in the DB
    expect($stored->pluck('type')->map->value->unique()->sort()->values()->all())
        ->toBe(collect(HomeSectionType::cases())->map->value->sort()->values()->all());

    foreach ($stored as $section) {
        if ($section->model_3d) {
            expect(Background3dModel::tryFrom($section->model_3d))->not->toBeNull();
        }

        if (in_array($section->type, [HomeSectionType::Banners, HomeSectionType::Counters, HomeSectionType::Testimonials, HomeSectionType::Features, HomeSectionType::Services, HomeSectionType::Process], true)) {
            expect($section->content['items'] ?? [])->not->toBeEmpty("{$section->type->value} has no sample content");
        }
    }

    expect($stored->whereNotNull('model_3d')->count())->toBeGreaterThanOrEqual(8);
});

it('can seed the sections twice without duplicating them', function () {
    expect(HomePageSection::seedDefaults())->toBe(count(HomePageSection::defaults()));

    $count = HomePageSection::count();

    expect(HomePageSection::seedDefaults())->toBe(0)
        ->and(HomePageSection::count())->toBe($count);
});

it('adds missing section types and sample models to an older layout', function () {
    $old = HomePageSection::factory()->ofType(HomeSectionType::Products)->create(['sort_order' => 4]);
    $edited = HomePageSection::factory()->ofType(HomeSectionType::Brands)->create(['model_3d' => null]);
    $this->travel(1)->minute();
    $edited->touch(); // an admin saved it later → leave it alone

    $created = HomePageSection::seedDefaults();

    expect($created)->toBe(count(HomePageSection::defaults()) - 2)
        ->and($old->fresh()->model_3d)->toBe('battery')
        ->and($edited->fresh()->model_3d)->toBeNull()
        ->and(HomePageSection::query()->max('sort_order'))->toBeGreaterThan(4);
});

it('renders the seeded layout with the models', function () {
    $this->seed(DatabaseSeeder::class);

    $this->withoutVite()->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sections.0.type', 'hero')
            ->where('sections.0.model_3d.model', 'solar-panel'));
});

it('draws a picture for the "no model" card', function () {
    expect(ModelPreview::noneSvg())->toContain('<svg');
});
