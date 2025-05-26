<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        // Get a sample admin for the uploaded_by field
        $admin = Admin::first();
        
        $products = Product::all();

        foreach ($products as $product) {
            // First, make sure no images for this product are primary
            DB::statement("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$product->id]);
            
            // Create 3-5 images per product
            $imageCount = fake()->numberBetween(3, 5);
            
            for ($i = 0; $i < $imageCount; $i++) {
                $imageType = fake()->randomElement(['thumbnail', 'gallery', '360-view']);
                $isPrimary = false; // Set all to false initially
                
                // Directly insert using query builder to bypass trigger
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'image_url' => fake()->imageUrl(800, 800, 'products'),
                    'alt_text' => $product->name . ' - Image ' . ($i + 1),
                    'image_type' => $imageType,
                    'is_primary' => $isPrimary,
                    'uploaded_by' => $admin->id,
                    'uploaded_at' => now()
                ]);
            }
            
            // Set one image as primary after all are inserted
            $randomImage = DB::table('product_images')
                ->where('product_id', $product->id)
                ->inRandomOrder()
                ->first();
                
            if ($randomImage) {
                DB::table('product_images')
                    ->where('id', $randomImage->id)
                    ->update(['is_primary' => 1]);
            }
        }
    }
}
