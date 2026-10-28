<?php

use App\Enums\HomeSectionType;
use App\Enums\ThreeDModelType;
use App\Filament\Resources\ThreeDModels\Pages\CreateThreeDModel;
use App\Filament\Resources\ThreeDModels\Pages\EditThreeDModel;
use App\Filament\Resources\ThreeDModels\Pages\ListThreeDModels;
use App\Models\HomePageSection;
use App\Models\ThreeDModel;
use App\Settings\SiteSettings;
use App\Support\Glb\GlbBuilder;
use App\Support\Glb\Library;
use App\Support\Model3dChoices;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

/** @return array{json: array<string, mixed>, bin: string} */
function readGlb(string $glb): array
{
    expect(substr($glb, 0, 4))->toBe('glTF')
        ->and(unpack('V', substr($glb, 4, 4))[1])->toBe(2)
        ->and(unpack('V', substr($glb, 8, 4))[1])->toBe(strlen($glb));

    $jsonLength = unpack('V', substr($glb, 12, 4))[1];
    $json = json_decode(substr($glb, 20, $jsonLength), true);
    $binLength = unpack('V', substr($glb, 20 + $jsonLength, 4))[1];

    return ['json' => $json, 'bin' => substr($glb, 28 + $jsonLength, $binLength)];
}

// -- the library files --------------------------------------------------------------

it('ships a .glb file and a manifest entry for every library model', function () {
    $manifest = json_decode(File::get(database_path('models3d/manifest.json')), true);

    expect($manifest)->toHaveCount(count(Library::entries()));

    foreach ($manifest as $row) {
        $path = database_path("models3d/{$row['file']}");

        expect(is_file($path))->toBeTrue("{$row['file']} is missing — run php artisan models3d:build")
            ->and(substr(File::get($path), 0, 4))->toBe('glTF')
            ->and($row['name']['ar'])->not->toBeEmpty();
    }
});

it('covers the requested brands and equipment', function () {
    $brands = collect(Library::entries())->pluck('brand')->filter()->unique();

    expect($brands->all())->toContain('Voltronic', 'Felicity', 'Solis', 'Tobac');

    $types = collect(Library::entries())->groupBy(fn ($e) => $e['type']->value)->map->count();

    expect($types->keys()->all())->toContain('inverter', 'battery', 'charge-controller', 'breaker')
        ->and($types['breaker'])->toBeGreaterThanOrEqual(8);

    // every breaker pole count and both shunt-trip types
    expect(collect(Library::entries())->pluck('slug')->all())
        ->toContain('breaker-mcb-1p', 'breaker-mcb-2p', 'breaker-mcb-3p', 'breaker-mcb-4p', 'breaker-mcb-shunt-trip', 'breaker-mccb-shunt-trip');
});

it('writes valid binary glTF with positions, normals and in-range indices', function () {
    foreach (Library::entries() as $entry) {
        ['json' => $json, 'bin' => $bin] = readGlb($entry['build']()->toGlb());

        expect($json['asset']['version'])->toBe('2.0')
            ->and($json['materials'])->not->toBeEmpty()
            ->and(strlen($bin))->toBe($json['buffers'][0]['byteLength']);

        foreach ($json['meshes'][0]['primitives'] as $primitive) {
            $vertices = $json['accessors'][$primitive['attributes']['POSITION']]['count'];
            $indices = $json['accessors'][$primitive['indices']];
            $view = $json['bufferViews'][$indices['bufferView']];
            $list = unpack('V*', substr($bin, $view['byteOffset'], $indices['count'] * 4));

            expect(max($list))->toBeLessThan($vertices, "{$entry['slug']} has an out-of-range index")
                ->and($indices['count'] % 3)->toBe(0)
                ->and($json['accessors'][$primitive['attributes']['NORMAL']]['count'])->toBe($vertices);
        }
    }
});

it('builds a rounded box whose bounds match the requested size', function () {
    $glb = (new GlbBuilder('t'))->material('m', '#ff0000')->box('m', [0.4, 0.2, 0.1], radius: 0.02)->toGlb();
    $accessor = readGlb($glb)['json']['accessors'][0];

    expect($accessor['max'][0] - $accessor['min'][0])->toEqualWithDelta(0.4, 1e-5)
        ->and($accessor['max'][1] - $accessor['min'][1])->toEqualWithDelta(0.2, 1e-5)
        ->and($accessor['max'][2] - $accessor['min'][2])->toEqualWithDelta(0.1, 1e-5);
});

it('refuses to write an empty model or an unknown material', function () {
    expect(fn () => (new GlbBuilder)->toGlb())->toThrow(InvalidArgumentException::class)
        ->and(fn () => (new GlbBuilder)->box('ghost', [1, 1, 1])->toGlb())->toThrow(InvalidArgumentException::class);
});

// -- the import command -------------------------------------------------------------

it('imports the whole library with one command', function () {
    $this->artisan('models3d:import')->assertSuccessful();

    expect(ThreeDModel::count())->toBe(count(Library::entries()))
        ->and(ThreeDModel::where('brand', 'Voltronic')->where('type', ThreeDModelType::Inverter)->exists())->toBeTrue()
        ->and(ThreeDModel::where('brand', 'Tobac')->where('type', ThreeDModelType::Battery)->exists())->toBeTrue()
        ->and(ThreeDModel::where('type', ThreeDModelType::ChargeController)->count())->toBeGreaterThanOrEqual(4)
        ->and(ThreeDModel::where('type', ThreeDModelType::Breaker)->count())->toBeGreaterThanOrEqual(8);

    $model = ThreeDModel::where('slug', 'inverter-solis-hybrid')->firstOrFail();

    expect($model->getTranslation('name', 'ar'))->toBe('انفرتر هجين سوليز')
        ->and($model->is_active)->toBeTrue();

    Storage::disk('public')->assertExists($model->file);
    Storage::disk('public')->assertExists($model->preview);
    expect(Storage::disk('public')->get($model->preview))->toContain('SOLIS');
});

it('can be run again without duplicating anything or undoing admin changes', function () {
    $this->artisan('models3d:import')->assertSuccessful();
    ThreeDModel::where('slug', 'breaker-mcb-1p')->update(['is_active' => false]);

    $this->artisan('models3d:import')->assertSuccessful();

    expect(ThreeDModel::count())->toBe(count(Library::entries()))
        ->and(ThreeDModel::where('slug', 'breaker-mcb-1p')->value('is_active'))->toBeFalse();
});

it('imports a real model dropped into the folder', function () {
    $dir = storage_path('framework/testing/models3d-'.uniqid());
    File::ensureDirectoryExists($dir);
    File::put("{$dir}/voltronic-axpert-king.glb", Library::entries()[0]['build']()->toGlb());

    $this->artisan('models3d:import', ['--path' => $dir])->assertSuccessful();

    $model = ThreeDModel::firstOrFail();

    expect($model->slug)->toBe('voltronic-axpert-king')
        ->and($model->type)->toBe(ThreeDModelType::Other)
        ->and($model->name)->toBe('Voltronic Axpert King');

    File::deleteDirectory($dir);
});

it('removes library models that are gone from the folder with --fresh', function () {
    ThreeDModel::factory()->create(['slug' => 'old-model']);

    $this->artisan('models3d:import', ['--fresh' => true])->assertSuccessful();

    expect(ThreeDModel::where('slug', 'old-model')->exists())->toBeFalse()
        ->and(ThreeDModel::count())->toBe(count(Library::entries()));
});

// -- using the models -----------------------------------------------------------------

it('offers the library models, grouped by type, in the pickers', function () {
    $this->artisan('models3d:import')->assertSuccessful();

    $options = Model3dChoices::options();

    expect($options)->toHaveKey('library:inverter-voltronic-axpert')
        ->and($options['library:inverter-voltronic-axpert']['group'])->toBe(ThreeDModelType::Inverter->label())
        ->and($options['library:breaker-mccb-shunt-trip']['image'])->toContain('/storage/models3d/previews/')
        ->and(Model3dChoices::keys(false))->toContain('library:battery-tobac-rack-48v', 'mixed')
        ->and(Model3dChoices::label('library:battery-tobac-rack-48v'))->toContain('Tobac');

    ThreeDModel::where('slug', 'breaker-mcb-1p')->update(['is_active' => false]);

    expect(Model3dChoices::options())->not->toHaveKey('library:breaker-mcb-1p')
        ->and(Model3dChoices::keys())->toContain('library:breaker-mcb-1p');
});

it('shows a library model in a home section', function () {
    $this->artisan('models3d:import')->assertSuccessful();
    HomePageSection::factory()->ofType(HomeSectionType::Services)->create(['model_3d' => 'library:inverter-felicity-hybrid', 'model_3d_side' => 'start']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('sections.0.model_3d.model', 'custom')
        ->where('sections.0.model_3d.side', 'start')
        ->where('sections.0.model_3d.url', fn ($url) => str_ends_with($url, '/storage/models3d/inverter-felicity-hybrid.glb')));
});

it('shows nothing for a library model that is switched off or deleted', function () {
    $this->artisan('models3d:import')->assertSuccessful();
    $section = HomePageSection::factory()->create(['model_3d' => 'library:breaker-mcb-2p']);

    ThreeDModel::where('slug', 'breaker-mcb-2p')->update(['is_active' => false]);
    expect($section->fresh()->model3dPayload())->toBeNull();

    ThreeDModel::where('slug', 'breaker-mcb-2p')->delete();
    expect($section->fresh()->model3dPayload())->toBeNull();
});

it('uses a library model as the site-wide 3D background', function () {
    $this->artisan('models3d:import')->assertSuccessful();

    $settings = app(SiteSettings::class);
    $settings->bg3d_enabled = true;
    $settings->bg3d_model = 'library:battery-tobac-cabinet';
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('settings.bg3d_model', 'custom')
        ->where('settings.bg3d_custom_file', fn ($url) => str_ends_with($url, '/storage/models3d/battery-tobac-cabinet.glb')));
});

// -- admin ---------------------------------------------------------------------------

describe('admin', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(adminUser());
    });

    it('lists and filters the library', function () {
        $inverter = ThreeDModel::factory()->create(['type' => ThreeDModelType::Inverter]);
        $battery = ThreeDModel::factory()->create(['type' => ThreeDModelType::Battery]);

        Livewire::test(ListThreeDModels::class)
            ->assertCanSeeTableRecords([$inverter, $battery])
            ->filterTable('type', 'battery')
            ->assertCanSeeTableRecords([$battery])
            ->assertCanNotSeeTableRecords([$inverter]);
    });

    it('adds and edits a model', function () {
        Storage::disk('public')->put('models3d/solis-s6.glb', 'glTF');

        Livewire::test(CreateThreeDModel::class)
            ->fillForm([
                'name' => 'Solis S6 inverter',
                'brand' => 'Solis',
                'type' => ThreeDModelType::Inverter->value,
                'slug' => 'solis-s6',
                'file' => ['models3d/solis-s6.glb'],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $model = ThreeDModel::where('slug', 'solis-s6')->firstOrFail();

        Livewire::test(EditThreeDModel::class, ['record' => $model->getRouteKey()])
            ->fillForm(['brand' => 'Solis Energy'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($model->fresh()->brand)->toBe('Solis Energy');
    });

    it('needs a name, a file and a unique slug', function () {
        ThreeDModel::factory()->create(['slug' => 'taken']);

        Livewire::test(CreateThreeDModel::class)
            ->fillForm(['name' => null, 'slug' => 'taken', 'file' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'file' => 'required', 'slug' => 'unique']);
    });
});
