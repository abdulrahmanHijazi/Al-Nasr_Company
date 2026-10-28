<?php

namespace Database\Factories;

use App\Enums\HomeSectionType;
use App\Enums\SectionShowType;
use App\Models\HomePageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomePageSection>
 */
class HomePageSectionFactory extends Factory
{
    protected $model = HomePageSection::class;

    public function definition(): array
    {
        return [
            'type' => HomeSectionType::Products,
            'design' => 'grid',
            'title' => ['en' => fake()->sentence(3), 'ar' => 'عنوان القسم'],
            'subtitle' => null,
            'show_type' => SectionShowType::Latest,
            'limit' => 8,
            'background_color' => null,
            'content' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function ofType(HomeSectionType $type, ?string $design = null): static
    {
        return $this->state(fn () => ['type' => $type, 'design' => $design ?? $type->defaultDesign()]);
    }
}
