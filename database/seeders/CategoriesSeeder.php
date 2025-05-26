<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first()?->id;

        // First create main categories
        $mainCategories = [
            'Electronics' => [
                'category_description' => 'Latest electronic devices and gadgets',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(1, 1000),
                'display_order' => 1,
            ],
            'Clothing' => [
                'category_description' => 'Fashion and apparel for all',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(1001, 2000),
                'display_order' => 2,
            ],
            'Home & Living' => [
                'category_description' => 'Everything for your home',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(2001, 3000),
                'display_order' => 3,
            ],
            'Beauty & Personal Care' => [
                'category_description' => 'Beauty, skincare, and personal care products',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(3001, 4000),
                'display_order' => 4,
            ],
        ];

        foreach ($mainCategories as $name => $details) {
            Category::factory()->create([
                'name' => $name,
                'category_description' => $details['category_description'],
                'image_url' => $details['image_url'],
                'parent_category_id' => null,
                'is_active' => true,
                'display_order' => $details['display_order'],
                'created_by' => $admin,
            ]);
        }

        // Create subcategories for Electronics
        $electronics = Category::where('name', 'Electronics')->first();
        $electronicsSubcategories = [
            'Smartphones & Accessories' => [
                'category_description' => 'Mobile phones and accessories',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4001, 4100),
                'display_order' => 1,
            ],
            'Laptops & Computers' => [
                'category_description' => 'Computers and accessories',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4101, 4200),
                'display_order' => 2,
            ],
            'Audio & Headphones' => [
                'category_description' => 'Audio equipment and accessories',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4201, 4300),
                'display_order' => 3,
            ],
            'Cameras & Photography' => [
                'category_description' => 'Cameras and photography equipment',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4301, 4400),
                'display_order' => 4,
            ],
            'Gaming & Consoles' => [
                'category_description' => 'Gaming consoles and accessories',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4401, 4500),
                'display_order' => 5,
            ],
        ];

        foreach ($electronicsSubcategories as $name => $details) {
            Category::factory()->create([
                'name' => $name,
                'category_description' => $details['category_description'],
                'image_url' => $details['image_url'],
                'parent_category_id' => $electronics->id,
                'is_active' => true,
                'display_order' => $details['display_order'],
                'created_by' => $admin,
            ]);
        }

        // Create subcategories for Clothing
        $clothing = Category::where('name', 'Clothing')->first();
        $clothingSubcategories = [
            'Men\'s Fashion' => [
                'category_description' => 'Fashion for men',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4501, 4600),
                'display_order' => 1,
            ],
            'Women\'s Fashion' => [
                'category_description' => 'Fashion for women',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4601, 4700),
                'display_order' => 2,
            ],
            'Kids\' Fashion' => [
                'category_description' => 'Fashion for kids',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4701, 4800),
                'display_order' => 3,
            ],
            'Sportswear' => [
                'category_description' => 'Athletic and sports clothing',
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(4801, 4900),
                'display_order' => 4,
            ],
        ];

        foreach ($clothingSubcategories as $name => $details) {
            Category::factory()->create([
                'name' => $name,
                'category_description' => $details['category_description'],
                'image_url' => $details['image_url'],
                'parent_category_id' => $clothing->id,
                'is_active' => true,
                'display_order' => $details['display_order'],
                'created_by' => $admin,
            ]);
        }

        // Add some leaf categories under Men's Fashion
        $mensFashion = Category::where('name', 'Men\'s Fashion')->first();
        $mensFashionCategories = [
            'T-Shirts' => [
                'category_description' => 'Casual and formal t-shirts',
                'display_order' => 1,
            ],
            'Shirts' => [
                'category_description' => 'Formal and casual shirts',
                'display_order' => 2,
            ],
            'Pants' => [
                'category_description' => 'Formal and casual pants',
                'display_order' => 3,
            ],
            'Jeans' => [
                'category_description' => 'Denim jeans in various styles',
                'display_order' => 4,
            ],
            'Outerwear' => [
                'category_description' => 'Jackets, coats, and outerwear',
                'display_order' => 5,
            ],
        ];

        foreach ($mensFashionCategories as $name => $details) {
            Category::factory()->create([
                'name' => $name,
                'category_description' => $details['category_description'],
                'image_url' => 'https://picsum.photos/800/600?random=' . mt_rand(5001, 6000),
                'parent_category_id' => $mensFashion->id,
                'is_active' => true,
                'display_order' => $details['display_order'],
                'created_by' => $admin,
            ]);
        }

        // Create a few more random categories
        Category::factory(5)->create();
    }
}
