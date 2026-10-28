<?php

namespace Database\Factories;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ['en' => $name, 'ar' => 'حزمة '.$name],
            'slug' => ['en' => Str::slug($name), 'ar' => Str::slug($name).'-ar'],
            'description' => ['en' => fake()->sentence(), 'ar' => 'وصف الحزمة'],
            'bundle_price' => fake()->randomFloat(2, 100, 3000),
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
