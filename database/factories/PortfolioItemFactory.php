<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => ['en' => $title, 'ar' => 'مشروع '.$title],
            'slug' => ['en' => Str::slug($title), 'ar' => Str::slug($title).'-ar'],
            'description' => ['en' => fake()->paragraph(), 'ar' => 'وصف المشروع'],
            'client_name' => ['en' => fake()->company(), 'ar' => 'عميل'],
            'project_url' => fake()->url(),
            'completed_at' => fake()->date(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
