<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 20, 5000);

        return [
            'brand_id' => null,
            'name' => ['en' => $name, 'ar' => 'منتج '.$name],
            'slug' => ['en' => Str::slug($name), 'ar' => Str::slug($name).'-ar'],
            'short_description' => ['en' => fake()->sentence(), 'ar' => 'وصف مختصر'],
            'description' => ['en' => '<p>'.fake()->paragraph().'</p>', 'ar' => '<p>وصف تفصيلي</p>'],
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'price' => $price,
            'compare_price' => null,
            'cost_price' => round($price * 0.7, 2),
            'stock_qty' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
