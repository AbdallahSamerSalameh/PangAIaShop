<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products, categories and admins
        $products = Product::all();
        $categories = Category::all();
        $admin = Admin::first(); // Get the first admin for added_by

        if ($products->isEmpty() || $categories->isEmpty()) {
            $this->command->info('Please seed products and categories first.');
            return;
        }

        // Clear existing product-category associations
        // Wrapping in a try-catch in case foreign key constraints prevent truncate
        try {
            DB::table('product_categories')->truncate();
        } catch (\Exception $e) {
            DB::table('product_categories')->delete();
        }

        foreach ($products as $product) {
            // Assign 1-3 categories per product
            $numCategories = rand(1, 3);
            
            // Get random categories
            $selectedCategories = $categories->random(min($numCategories, $categories->count()));
            
            // Insert all categories initially as non-primary
            foreach ($selectedCategories as $category) {
                DB::table('product_categories')->insert([
                    'product_id' => $product->id,
                    'category_id' => $category->id,
                    'is_primary_category' => false, // All set to false initially
                    'added_by' => $admin->id,
                    'added_at' => now()
                ]);
            }
            
            // After inserting all categories, set the first one as primary
            if ($selectedCategories->isNotEmpty()) {
                $primaryCategory = $selectedCategories->first();
                DB::statement(
                    "UPDATE product_categories SET is_primary_category = 1 
                     WHERE product_id = ? AND category_id = ?", 
                    [$product->id, $primaryCategory->id]
                );
            }
        }

        $this->command->info('Product categories seeded successfully!');
    }
}
