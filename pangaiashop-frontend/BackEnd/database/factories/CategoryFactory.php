<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    private $categories = [
        'Clothing' => ['Men\'s Clothing', 'Women\'s Clothing', 'Children\'s Clothing'],
        'Electronics' => ['Smartphones', 'Laptops', 'Tablets', 'Audio', 'Gaming'],
        'Home & Kitchen' => ['Furniture', 'Appliances', 'Kitchen Utensils', 'Home Decor'],
        'Beauty & Personal Care' => ['Skincare', 'Makeup', 'Haircare', 'Fragrances'],
        'Sports & Outdoors' => ['Fitness Equipment', 'Outdoor Gear', 'Sports Apparel'],
        'Books & Media' => ['Fiction', 'Non-Fiction', 'Educational', 'Digital Media'],
        'Toys & Games' => ['Board Games', 'Action Figures', 'Educational Toys', 'Outdoor Toys'],
        'Health & Wellness' => ['Vitamins & Supplements', 'Personal Care', 'Medical Supplies'],
        'Food & Beverage' => ['Snacks', 'Beverages', 'Organic Foods', 'Specialty Foods'],
        'Jewelry & Accessories' => ['Necklaces', 'Rings', 'Watches', 'Handbags']
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $admin = Admin::inRandomOrder()->first()?->id;
        
        // Either create a parent category or a child category
        $isParent = fake()->boolean(30);
        
        if ($isParent) {
            // Parent category
            $name = fake()->randomElement(array_keys($this->categories));
            return [
                'name' => $name,
                'parent_category_id' => null,
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(1, 1000),
                'category_description' => fake()->paragraph(),
                'created_by' => $admin,
                'is_active' => true,
                'display_order' => fake()->numberBetween(1, 100),
                'created_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
                'updated_at' => function (array $attributes) {
                    return fake()->dateTimeBetween($attributes['created_at'], 'now');
                }
            ];
        } else {
            // Child category
            $parentName = fake()->randomElement(array_keys($this->categories));
            $childName = fake()->randomElement($this->categories[$parentName]);
            
            return [
                'name' => $childName,
                'parent_category_id' => function() {
                    // This will be set by the seeder to link to an actual parent
                    return null;
                },
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(1, 1000),
                'category_description' => fake()->paragraph(),
                'created_by' => $admin,
                'is_active' => true, 
                'display_order' => fake()->numberBetween(1, 100),
                'created_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
                'updated_at' => function (array $attributes) {
                    return fake()->dateTimeBetween($attributes['created_at'], 'now');
                }
            ];
        }
    }
}