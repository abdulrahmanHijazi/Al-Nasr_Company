<?php

use App\Enums\HomeSectionType;
use App\Filament\Resources\HomePageSections\Pages\CreateHomePageSection;
use App\Models\HomePageSection;
use App\Support\DesignPreview;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

it('is a section type with three designs, each with a preview picture', function () {
    expect(HomeSectionType::Slider->designs())->toHaveKeys(['full', 'product', 'cards'])
        ->and(HomeSectionType::Slider->defaultDesign())->toBe('full')
        ->and(HomeSectionType::Slider->usesItems())->toBeFalse();

    foreach (array_keys(HomeSectionType::Slider->designs()) as $design) {
        expect(public_path(DesignPreview::path(HomeSectionType::Slider, $design)))->toBeFile();
    }
});

it('is part of the built-in home page, right after the hero, with slides', function () {
    $defaults = collect(HomePageSection::defaults());
    $types = $defaults->pluck('type')->map->value->all();

    expect($types)->toContain('slider')
        ->and(array_search('slider', $types))->toBe(array_search('hero', $types) + 1);

    $slides = $defaults->firstWhere('type', HomeSectionType::Slider)['content']['slides'];

    expect($slides)->toHaveCount(3)
        ->and($slides[0]['title'])->toHaveKeys(['en', 'ar'])
        ->and($slides[0]['button_url'])->toBe('/shop');
});

it('hands the storefront real URLs for the slide pictures', function () {
    HomePageSection::factory()->ofType(HomeSectionType::Slider)->create([
        'design' => 'product',
        'content' => ['autoplay' => true, 'interval' => 5, 'slides' => [
            ['title' => ['en' => 'One', 'ar' => 'واحد'], 'image' => 'home/bg.jpg', 'product_image' => 'home/p.png'],
            ['title' => ['en' => 'Two', 'ar' => 'اثنان'], 'image' => 'https://cdn.example/x.jpg', 'product_image' => null],
        ]],
    ]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('sections.0.type', 'slider')
        ->where('sections.0.design', 'product')
        ->where('sections.0.content.interval', 5)
        ->where('sections.0.content.slides.0.image', Storage::disk('public')->url('home/bg.jpg'))
        ->where('sections.0.content.slides.0.product_image', Storage::disk('public')->url('home/p.png'))
        ->where('sections.0.content.slides.1.image', 'https://cdn.example/x.jpg'));
});

it('lets an administrator create a slider with slides', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(adminUser());

    Livewire::test(CreateHomePageSection::class)
        ->fillForm([
            'type' => HomeSectionType::Slider->value,
            'design' => 'full',
            'is_active' => true,
            'content' => ['autoplay' => true, 'interval' => 7, 'slides' => [
                ['title' => ['en' => 'Hello', 'ar' => 'مرحبا'], 'button_url' => '/shop'],
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $section = HomePageSection::where('type', HomeSectionType::Slider)->firstOrFail();

    expect($section->content['interval'])->toBe(7)
        ->and($section->content['slides'][0]['title']['en'])->toBe('Hello');
});

it('puts product pictures on the demo slider', function () {
    Storage::fake('public');

    $this->artisan('demo:seed', ['--no-images' => true, '--no-models' => true])->assertSuccessful();

    $slides = HomePageSection::where('type', HomeSectionType::Slider)->firstOrFail()->content['slides'];

    expect($slides[0]['product_image'])->toBe('home/slides/inverter-felicity-hybrid.png');
    Storage::disk('public')->assertExists('home/slides/inverter-felicity-hybrid.png');
});
