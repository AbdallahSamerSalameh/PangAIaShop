<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Temporarily disable the trigger that's causing problems
        DB::unprepared('DROP TRIGGER IF EXISTS before_product_category_insert');
        
        try {
            $mainVendor = Vendor::where('name', 'PangAIa Official Store')->first();
            $otherVendors = Vendor::where('status', 'active')
                ->where('name', '!=', 'PangAIa Official Store')
                ->get();

            // Create electronics products
            $electronicsCategory = Category::where('name', 'Electronics')->first();
            $this->createProductsForCategory($electronicsCategory, $mainVendor, 5);
            foreach ($otherVendors as $vendor) {
                $this->createProductsForCategory($electronicsCategory, $vendor, 2);
            }

            // Create clothing products
            $clothingCategory = Category::where('name', 'Clothing')->first();
            $this->createProductsForCategory($clothingCategory, $mainVendor, 8);
            foreach ($otherVendors as $vendor) {
                $this->createProductsForCategory($clothingCategory, $vendor, 3);
            }

            // Create some featured products
            $products = Product::factory()->count(5)->create([
                'vendor_id' => $mainVendor->id,
                'status' => 'active'
            ]);
            
            // Assign random categories to these products
            $categories = Category::inRandomOrder()->limit(3)->get();
            foreach ($products as $product) {
                // Get a random category from the collection by key
                $randomIndex = array_rand($categories->toArray(), 1);
                $randomCategory = $categories[$randomIndex];
                $this->assignCategoriesToProduct($product, $randomCategory);
            }

            // Create some products in different states
            $draftProducts = Product::factory()->count(3)->create(['status' => 'draft']);
            $this->assignRandomCategories($draftProducts);
            
            // Changed 'inactive' to 'draft' to match allowed ENUM values
            $draftProducts2 = Product::factory()->count(2)->create(['status' => 'draft']);
            $this->assignRandomCategories($draftProducts2);
            
            $discontinuedProducts = Product::factory()->count(2)->create(['status' => 'discontinued']);
            $this->assignRandomCategories($discontinuedProducts);
        } finally {
            // Recreate the trigger regardless of whether seeding succeeded or failed
            DB::unprepared('
                CREATE TRIGGER before_product_category_insert BEFORE INSERT ON product_categories
                FOR EACH ROW
                BEGIN
                    IF NEW.is_primary_category = 1 THEN
                        UPDATE product_categories 
                        SET is_primary_category = 0 
                        WHERE product_id = NEW.product_id;
                    END IF;
                END
            ');
        }
    }

    private function createProductsForCategory($category, $vendor, $count)
    {
        $products = Product::factory()->count($count)->create([
            'vendor_id' => $vendor->id,
            'status' => 'active'
        ]);

        $this->assignCategoriesToProduct($products, $category);
    }
    
    private function assignCategoriesToProduct($products, $categories)
    {
        if (!is_array($products) && !($products instanceof \Illuminate\Database\Eloquent\Collection)) {
            $products = [$products];
        }
        
        if (!is_array($categories) && !($categories instanceof \Illuminate\Database\Eloquent\Collection)) {
            $categories = [$categories];
        }
        
        foreach ($products as $product) {
            $isPrimary = true;
            foreach ($categories as $category) {
                if ($category && $product) {
                    // First, manually reset any primary categories for this product
                    if ($isPrimary) {
                        DB::table('product_categories')
                            ->where('product_id', $product->id)
                            ->update(['is_primary_category' => 0]);
                    }
                    
                    DB::table('product_categories')->insert([
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                        'is_primary_category' => $isPrimary,
                        'added_at' => now()
                    ]);
                    $isPrimary = false; // Only the first category is primary
                }
            }
        }
    }
    
    private function assignRandomCategories($products)
    {
        $categoriesCollection = Category::inRandomOrder()->limit(3)->get();
        $categoriesArray = $categoriesCollection->all();
        
        foreach ($products as $product) {
            // Safely get 1-2 random categories
            $numToSelect = min(rand(1, 2), count($categoriesArray));
            $keys = array_rand($categoriesArray, $numToSelect);
            
            // Convert to array if only one key is returned
            if (!is_array($keys)) {
                $keys = [$keys];
            }
            
            $selectedCategories = [];
            foreach ($keys as $key) {
                $selectedCategories[] = $categoriesArray[$key];
            }
            
            $this->assignCategoriesToProduct($product, $selectedCategories);
        }
    }
}
