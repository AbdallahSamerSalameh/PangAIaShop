<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $productTypes = [
        'Electronics' => [
            'Smartphones', 'Laptops', 'Tablets', 'Smartwatches', 'Headphones',
            'Cameras', 'Gaming Consoles', 'Speakers', 'Power Banks'
        ],
        'Clothing' => [
            'T-Shirts', 'Jeans', 'Dresses', 'Jackets', 'Sweaters',
            'Hoodies', 'Skirts', 'Shorts', 'Athletic Wear'
        ],
        'Home & Living' => [
            'Bedding', 'Furniture', 'Kitchen Appliances', 'Home Decor',
            'Lighting', 'Storage Solutions', 'Bathroom Accessories'
        ],
        'Beauty & Personal Care' => [
            'Skincare', 'Makeup', 'Hair Care', 'Fragrances',
            'Bath & Body', 'Personal Hygiene', 'Beauty Tools'
        ]
    ];

    protected $brands = [
        'Electronics' => ['Apple', 'Samsung', 'Sony', 'LG', 'Dell', 'HP', 'Lenovo', 'Asus'],
        'Clothing' => ['Nike', 'Adidas', 'H&M', 'Zara', 'Uniqlo', 'Levi\'s', 'Gap', 'Under Armour'],
        'Home & Living' => ['IKEA', 'Ashley', 'Wayfair', 'West Elm', 'Crate & Barrel', 'HomeGoods'],
        'Beauty & Personal Care' => ['L\'Oréal', 'Maybelline', 'MAC', 'Estée Lauder', 'Nivea', 'Dove']
    ];

    public function definition(): array
    {
        $category = fake()->randomElement(array_keys($this->productTypes));
        $productType = fake()->randomElement($this->productTypes[$category]);
        $brand = fake()->randomElement($this->brands[$category]);
        $name = "$brand " . fake()->words(2, true) . " " . $productType;
        
        $basePrice = $this->getCategoryBasePrice($category);
        $price = fake()->numberBetween($basePrice * 0.8, $basePrice * 1.2);
        
        $admin = Admin::inRandomOrder()->first()?->id;

        return [
            'name' => $name,
            'description' => fake()->paragraphs(3, true),
            'price' => $price,
            'sku' => strtoupper(fake()->unique()->regexify('[A-Z0-9]{8}')),
            'vendor_id' => Vendor::inRandomOrder()->first()?->id,
            'created_by' => $admin,
            'updated_by' => $admin,
            'status' => fake()->randomElement(['active', 'draft', 'discontinued']),
            'weight' => fake()->randomFloat(2, 0.1, 20),
            'dimensions' => fake()->numberBetween(5, 100) . 'x' . fake()->numberBetween(5, 100) . 'x' . fake()->numberBetween(5, 100),
            'warranty_info' => fake()->boolean(70) ? fake()->paragraph() : null,
            'return_policy' => fake()->boolean(70) ? fake()->paragraph() : null,
            'created_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            }
        ];
    }

    protected function getCategoryBasePrice($category): float
    {
        return match($category) {
            'Electronics' => fake()->numberBetween(199, 1999),
            'Clothing' => fake()->numberBetween(29, 199),
            'Home & Living' => fake()->numberBetween(49, 999),
            'Beauty & Personal Care' => fake()->numberBetween(9, 99),
            default => fake()->numberBetween(19, 499)
        };
    }
}
