<?php

use App\Enums\HomeSectionType;
use App\Filament\Resources\HomePageSections\Schemas\HomePageSectionForm;
use App\Support\DesignPreview;

it('ships a preview image for every design of every section type', function () {
    foreach (HomeSectionType::cases() as $type) {
        foreach (array_keys($type->designs()) as $design) {
            $path = public_path(DesignPreview::path($type, $design));

            expect(file_exists($path))->toBeTrue("Missing preview: {$type->value}/{$design} — run php artisan design:previews");

            $svg = file_get_contents($path);
            expect($svg)->toStartWith('<svg')->toContain('viewBox="0 0 320 200"');
        }
    }
});

it('generates a different picture for each design of a type', function () {
    foreach (HomeSectionType::cases() as $type) {
        $svgs = collect(array_keys($type->designs()))
            ->map(fn (string $design) => DesignPreview::svg($type, $design));

        expect($svgs->unique())->toHaveCount($svgs->count());
    }
});

it('offers label and image for each design in the picker', function () {
    $options = HomePageSectionForm::designOptions(HomeSectionType::Products);

    expect($options)->toHaveKeys(['grid', 'carousel'])
        ->and($options['carousel']['image'])->toEndWith('/images/designs/products-carousel.svg')
        ->and($options['grid']['label'])->not->toBeEmpty()
        ->and(HomePageSectionForm::designOptions(null))->toBe([]);
});

it('regenerates the previews with an artisan command', function () {
    $file = public_path('images/designs/hero-split.svg');
    $before = file_get_contents($file);
    file_put_contents($file, 'stale');

    $this->artisan('design:previews')->assertSuccessful();

    expect(file_get_contents($file))->toBe($before);
});
