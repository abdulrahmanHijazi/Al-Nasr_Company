<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => ['en' => $name, 'ar' => 'فئة '.$name],
            'slug' => ['en' => Str::slug($name), 'ar' => Str::slug($name).'-ar'],
            'description' => ['en' => fake()->sentence(), 'ar' => 'وصف الفئة'],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
