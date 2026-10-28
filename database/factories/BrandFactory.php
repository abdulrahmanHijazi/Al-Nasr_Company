<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => ['en' => $name, 'ar' => 'علامة '.$name],
            'slug' => ['en' => Str::slug($name), 'ar' => Str::slug($name).'-ar'],
            'description' => ['en' => fake()->sentence(), 'ar' => 'وصف '.fake()->word()],
            'website_url' => fake()->url(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
