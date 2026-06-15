<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'compare_price' => $this->faker->randomFloat(2, 20, 1200),
            'stock' => $this->faker->numberBetween(0, 100),
            'sku' => strtoupper($this->faker->bothify('???-#####')),
            'category_id' => Category::factory(),
            'images' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
            'status' => 'published',
            'is_featured' => $this->faker->boolean(20),
            'rating' => $this->faker->randomFloat(2, 1, 5),
        ];
    }
}