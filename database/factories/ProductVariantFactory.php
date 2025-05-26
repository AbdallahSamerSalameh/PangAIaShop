<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $variantOptions = [
        'Electronics' => [
            'colors' => ['Black', 'White', 'Silver', 'Gold', 'Space Gray', 'Rose Gold'],
            'storage' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
            'ram' => ['4GB', '8GB', '16GB', '32GB'],
            'size' => ['13"', '14"', '15.6"', '17"'],
        ],
        'Clothing' => [
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'colors' => ['Black', 'White', 'Navy', 'Gray', 'Red', 'Blue', 'Green', 'Pink', 'Purple', 'Yellow'],
            'materials' => ['Cotton', 'Polyester', 'Wool', 'Denim', 'Linen', 'Silk'],
        ],
        'Home & Living' => [
            'sizes' => ['Small', 'Medium', 'Large', 'Extra Large'],
            'colors' => ['White', 'Black', 'Brown', 'Gray', 'Beige', 'Navy', 'Cream'],
            'materials' => ['Wood', 'Metal', 'Glass', 'Plastic', 'Fabric', 'Leather'],
        ],
        'Beauty & Personal Care' => [
            'sizes' => ['30ml', '50ml', '100ml', '200ml', '500ml'],
            'types' => ['Normal', 'Sensitive', 'Combination', 'Oily', 'Dry'],
            'shades' => ['Light', 'Medium', 'Dark', 'Fair', 'Deep'],
        ],
    ];

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        
        // Generate variant attributes
        $attributes = [];
        
        // Add some random attributes based on product type
        if (str_contains(strtolower($product->name), 'electronics')) {
            $attributes['color'] = fake()->randomElement($this->variantOptions['Electronics']['colors']);
            $attributes['storage'] = fake()->randomElement($this->variantOptions['Electronics']['storage']);
        } elseif (str_contains(strtolower($product->name), 'shirt') || str_contains(strtolower($product->name), 'cloth')) {
            $attributes['size'] = fake()->randomElement($this->variantOptions['Clothing']['sizes']);
            $attributes['color'] = fake()->randomElement($this->variantOptions['Clothing']['colors']);
        } elseif (str_contains(strtolower($product->name), 'home')) {
            $attributes['size'] = fake()->randomElement($this->variantOptions['Home & Living']['sizes']);
            $attributes['color'] = fake()->randomElement($this->variantOptions['Home & Living']['colors']);
        } else {
            $attributes['color'] = fake()->randomElement(array_merge(
                $this->variantOptions['Clothing']['colors'],
                $this->variantOptions['Electronics']['colors']
            ));
            if (fake()->boolean(70)) {
                $attributes['size'] = fake()->randomElement(array_merge(
                    $this->variantOptions['Clothing']['sizes'],
                    $this->variantOptions['Home & Living']['sizes']
                ));
            }
        }
        
        // Calculate price adjustment based on attributes
        $priceAdjustment = 0;
        if (isset($attributes['storage']) && $attributes['storage'] !== '64GB') {
            $priceAdjustment += match($attributes['storage']) {
                '128GB' => 50,
                '256GB' => 100,
                '512GB' => 200,
                '1TB' => 300,
                default => 0
            };
        }
        
        if (isset($attributes['size']) && in_array($attributes['size'], ['L', 'XL', 'XXL', 'Extra Large'])) {
            $priceAdjustment += 5;
        }
        
        // Generate variant name based on attributes
        $variantName = $product->name;
        foreach ($attributes as $key => $value) {
            $variantName .= " - $value";
        }
        
        return [
            'product_id' => $product->id,
            'sku' => strtoupper(fake()->unique()->regexify('[A-Z0-9]{2}') . '-' . substr($product->sku, 0, 6) . '-' . fake()->randomNumber(2, true)),
            'name' => $variantName,
            'price_adjustment' => $priceAdjustment,
            'attributes' => json_encode($attributes),
            'image_url' => fake()->boolean(70) ? 'https://picsum.photos/id/' . fake()->numberBetween(1, 1000) . '/400/400' : null,
        ];
    }
}
