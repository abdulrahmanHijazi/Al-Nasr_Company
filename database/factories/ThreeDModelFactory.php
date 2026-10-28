<?php

namespace Database\Factories;

use App\Enums\ThreeDModelType;
use App\Models\ThreeDModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ThreeDModel>
 */
class ThreeDModelFactory extends Factory
{
    protected $model = ThreeDModel::class;

    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->words(3, true));

        return [
            'slug' => $slug,
            'name' => ['en' => Str::headline($slug), 'ar' => 'نموذج '.$slug],
            'brand' => fake()->randomElement(['Voltronic', 'Felicity', 'Solis', 'Tobac']),
            'type' => ThreeDModelType::Inverter,
            'file' => "models3d/{$slug}.glb",
            'preview' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
