<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoriesFactory extends Factory
{
    protected $mainCategories = [
        'Electronics' => [
            'Smartphones & Accessories',
            'Laptops & Computers',
            'Audio & Headphones',
            'Cameras & Photography',
            'Gaming & Consoles',
            'Wearable Technology',
        ],
        'Clothing' => [
            'Men\'s Fashion',
            'Women\'s Fashion',
            'Kids\' Fashion',
            'Sportswear',
            'Accessories',
            'Shoes',
        ],
        'Home & Living' => [
            'Furniture',
            'Home Decor',
            'Kitchen & Dining',
            'Bedding & Bath',
            'Storage & Organization',
            'Lighting',
        ],
        'Beauty & Personal Care' => [
            'Skincare',
            'Makeup',
            'Hair Care',
            'Fragrances',
            'Bath & Body',
            'Personal Care Tools',
        ],
    ];

    protected $subCategories = [
        'Smartphones & Accessories' => ['Phones', 'Cases', 'Screen Protectors', 'Chargers', 'Power Banks'],
        'Laptops & Computers' => ['Laptops', 'Desktops', 'Tablets', 'Monitors', 'Computer Parts'],
        'Men\'s Fashion' => ['T-Shirts', 'Shirts', 'Pants', 'Jeans', 'Outerwear', 'Underwear'],
        'Women\'s Fashion' => ['Dresses', 'Tops', 'Pants', 'Skirts', 'Activewear', 'Intimates'],
        'Furniture' => ['Living Room', 'Bedroom', 'Dining Room', 'Office', 'Outdoor'],
        'Skincare' => ['Cleansers', 'Moisturizers', 'Serums', 'Masks', 'Sun Care'],
        // Add more subcategories as needed
    ];

    public function definition(): array
    {
        // First decide if this will be a main category, subcategory, or leaf category
        $categoryLevel = fake()->randomElement(['main', 'sub', 'leaf']);
        
        if ($categoryLevel === 'main') {
            $name = fake()->randomElement(array_keys($this->mainCategories));
            $parentId = null;
        } elseif ($categoryLevel === 'sub') {
            $mainCategory = fake()->randomElement(array_keys($this->mainCategories));
            $name = fake()->randomElement($this->mainCategories[$mainCategory]);
            $parentId = Category::where('name', $mainCategory)->first()?->id;
        } else {
            $subCategory = fake()->randomElement(array_keys($this->subCategories));
            $name = fake()->randomElement($this->subCategories[$subCategory]);
            $parentId = Category::where('name', $subCategory)->first()?->id;
        }

        $slug = Str::slug($name);
        
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->paragraph(),
            'meta_title' => $name . ' - PangAIaShop',
            'meta_description' => fake()->sentence(),
            'parent_category_id' => $parentId,
            'status' => fake()->randomElement(['active', 'inactive']),
            'featured' => fake()->boolean(20),
            'display_order' => fake()->numberBetween(1, 100),
            'icon' => $this->getCategoryIcon($name),
            'banner_image' => "categories/banners/{$slug}.jpg",
            'created_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    protected function getCategoryIcon($categoryName): string
    {
        // Map category names to Font Awesome or other icon names
        $iconMap = [
            'Electronics' => 'fas fa-mobile-alt',
            'Clothing' => 'fas fa-tshirt',
            'Home & Living' => 'fas fa-home',
            'Beauty & Personal Care' => 'fas fa-spa',
            'Smartphones & Accessories' => 'fas fa-mobile-alt',
            'Laptops & Computers' => 'fas fa-laptop',
            'Audio & Headphones' => 'fas fa-headphones',
            'Cameras & Photography' => 'fas fa-camera',
            'Gaming & Consoles' => 'fas fa-gamepad',
            'Men\'s Fashion' => 'fas fa-male',
            'Women\'s Fashion' => 'fas fa-female',
            'Kids\' Fashion' => 'fas fa-child',
            'Furniture' => 'fas fa-couch',
            'Skincare' => 'fas fa-pump-soap',
            'Makeup' => 'fas fa-magic',
            // Add more mappings as needed
        ];

        return $iconMap[$categoryName] ?? 'fas fa-tag'; // Default icon
    }

    public function main(): static
    {
        return $this->state(function (array $attributes) {
            $name = fake()->randomElement(array_keys($this->mainCategories));
            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_category_id' => null,
            ];
        });
    }

    public function sub(): static
    {
        return $this->state(function (array $attributes) {
            $mainCategory = fake()->randomElement(array_keys($this->mainCategories));
            $name = fake()->randomElement($this->mainCategories[$mainCategory]);
            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_category_id' => Category::where('name', $mainCategory)->first()?->id,
            ];
        });
    }

    public function leaf(): static
    {
        return $this->state(function (array $attributes) {
            $subCategory = fake()->randomElement(array_keys($this->subCategories));
            $name = fake()->randomElement($this->subCategories[$subCategory]);
            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'parent_category_id' => Category::where('name', $subCategory)->first()?->id,
            ];
        });
    }
}
