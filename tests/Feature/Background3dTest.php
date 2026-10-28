<?php

use App\Enums\Background3dModel;
use App\Filament\Pages\ManageSiteSettings;
use App\Settings\SiteSettings;
use App\Support\ModelPreview;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

it('ships with a sensible 3D background by default', function () {
    $settings = app(SiteSettings::class);

    expect($settings->bg3d_enabled)->toBeTrue()
        ->and($settings->bg3d_model)->toBe('solar-panel')
        ->and($settings->bg3d_custom_file)->toBeNull()
        ->and($settings->bg3d_position)->toBe('right')
        ->and($settings->bg3d_opacity)->toBe(55)
        ->and($settings->bg3d_speed)->toBe(1)
        ->and($settings->bg3d_interactive)->toBeTrue()
        ->and($settings->bg3d_pages)->toBe('all');
});

it('offers models that fit the business, plus mixed and custom', function () {
    expect(Background3dModel::values())->toEqual([
        'solar-panel', 'battery', 'cctv-camera', 'inverter', 'control-panel', 'wind-turbine', 'mixed', 'custom',
    ]);

    foreach (Background3dModel::cases() as $model) {
        expect($model->label())->not->toBeEmpty();
    }
});

it('has a preview picture for every model', function () {
    foreach (Background3dModel::cases() as $model) {
        $path = public_path($model->previewPath());

        expect(file_exists($path))->toBeTrue("Missing preview for {$model->value} — run php artisan design:previews")
            ->and(file_get_contents($path))->toStartWith('<svg')
            ->and(ModelPreview::url($model))->toEndWith($model->previewPath());
    }
});

it('draws a different picture for each model', function () {
    $svgs = collect(Background3dModel::cases())->map(fn ($m) => ModelPreview::svg($m));

    expect($svgs->unique())->toHaveCount(count(Background3dModel::cases()));
});

it('shares the 3D settings with every storefront page', function () {
    $this->withoutVite();

    $this->get('/shop')->assertInertia(fn (Assert $page) => $page
        ->where('settings.bg3d_enabled', true)
        ->where('settings.bg3d_model', 'solar-panel')
        ->where('settings.bg3d_position', 'right')
        ->where('settings.bg3d_opacity', 55));
});

it('turns the uploaded model path into a public URL', function () {
    $this->withoutVite();

    $settings = app(SiteSettings::class);
    $settings->bg3d_model = 'custom';
    $settings->bg3d_custom_file = 'bg3d/turbine.glb';
    $settings->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('settings.bg3d_model', 'custom')
        ->where('settings.bg3d_custom_file', fn ($url) => str_ends_with($url, '/storage/bg3d/turbine.glb')));
});

describe('admin', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(adminUser());
    });

    it('lets an admin switch the model and tune the background', function () {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'bg3d_enabled' => true,
                'bg3d_model' => 'cctv-camera',
                'bg3d_position' => 'left',
                'bg3d_opacity' => 80,
                'bg3d_speed' => 2,
                'bg3d_interactive' => false,
                'bg3d_pages' => 'home',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SiteSettings::class);

        expect($settings->bg3d_model)->toBe('cctv-camera')
            ->and($settings->bg3d_position)->toBe('left')
            ->and($settings->bg3d_opacity)->toBe(80)
            ->and($settings->bg3d_speed)->toBe(2)
            ->and($settings->bg3d_interactive)->toBeFalse()
            ->and($settings->bg3d_pages)->toBe('home');
    });

    it('can switch the 3D background off', function () {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['bg3d_enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(app(SiteSettings::class)->bg3d_enabled)->toBeFalse();
    });

    it('rejects an unknown model', function () {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['bg3d_enabled' => true, 'bg3d_model' => 'dragon'])
            ->call('save')
            ->assertHasFormErrors(['bg3d_model']);
    });

    it('requires a file when "my own model" is chosen', function () {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['bg3d_enabled' => true, 'bg3d_model' => 'custom', 'bg3d_custom_file' => null])
            ->call('save')
            ->assertHasFormErrors(['bg3d_custom_file' => 'required']);
    });

    it('accepts an uploaded .glb model', function () {
        Storage::fake('public');

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'bg3d_enabled' => true,
                'bg3d_model' => 'custom',
                'bg3d_custom_file' => UploadedFile::fake()->create('turbine.glb', 200, 'model/gltf-binary'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = app(SiteSettings::class)->bg3d_custom_file;

        expect($stored)->toStartWith('bg3d/')->toEndWith('.glb');
        Storage::disk('public')->assertExists($stored);
    });
});
