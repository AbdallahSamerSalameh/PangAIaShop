<?php  

namespace Database\Seeders;  

use App\Models\Product; 
use App\Models\ProductVariant; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductVariantSeeder extends Seeder 
{     
    public function run(): void     
    {         
        // Get clothing products using the pivot table relationship
        $clothingProducts = Product::whereIn('id', function($query) {
            $query->select('product_id')
                ->from('product_categories')
                ->whereIn('category_id', function($subQuery) {
                    $subQuery->select('id')
                        ->from('categories')
                        ->where('name', 'like', '%Clothing%')
                        ->orWhere('name', 'like', '%Fashion%');
                });
        })->get();          
        
        // Clothing specific variants         
        foreach ($clothingProducts as $product) {             
            $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];             
            $colors = ['Black', 'White', 'Navy', 'Red', 'Grey', 'Blue'];                          
            
            // Create size and color combinations             
            foreach ($sizes as $size) {                 
                foreach (Arr::random($colors, 3) as $color) {
                    // Include product ID in the SKU to ensure uniqueness
                    $sku = strtoupper(substr($product->name, 0, 3) . "-" . $product->id . "-" . $size . "-" . substr($color, 0, 3));
                    $name = "{$product->name} - {$size} - {$color}";
                    $priceAdjustment = $size === 'XXL' ? 5.00 : 0.00;
                    
                    ProductVariant::factory()->create([                         
                        'product_id' => $product->id,                         
                        'sku' => $sku,                         
                        'name' => $name,
                        'price_adjustment' => $priceAdjustment,                        
                        'attributes' => json_encode([                             
                            'size' => $size,                             
                            'color' => $color                         
                        ])                   
                    ]);                 
                }             
            }         
        }          
        
        // Electronics variants (for products in electronics category)         
        $electronicProducts = Product::whereIn('id', function($query) {             
            $query->select('product_id')                 
                ->from('product_categories')                 
                ->whereIn('category_id', function($subQuery) {
                    $subQuery->select('id')
                        ->from('categories')
                        ->where('name', 'like', '%Electronics%');
                });         
        })->get();          
        
        foreach ($electronicProducts as $product) {             
            $storageOptions = ['64GB', '128GB', '256GB', '512GB', '1TB'];             
            $colors = ['Space Grey', 'Silver', 'Gold', 'Black'];              
            
            // Create storage and color combinations for electronics             
            foreach (Arr::random($storageOptions, 3) as $storage) {                 
                foreach (Arr::random($colors, 2) as $color) {                     
                    $priceAdjustment = match($storage) {                         
                        '128GB' => 100.00,                         
                        '256GB' => 200.00,                         
                        '512GB' => 300.00,                         
                        '1TB' => 400.00,                         
                        default => 0.00                     
                    };  
                    
                    // Include product ID in the SKU to ensure uniqueness
                    $sku = strtoupper(substr($product->name, 0, 3) . "-" . $product->id . "-" . $storage . "-" . substr($color, 0, 3));
                    $name = "{$product->name} - {$storage} - {$color}";
                    
                    ProductVariant::factory()->create([                         
                        'product_id' => $product->id,                         
                        'sku' => $sku,   
                        'name' => $name,                     
                        'price_adjustment' => $priceAdjustment,                         
                        'attributes' => json_encode([                             
                            'storage' => $storage,                             
                            'color' => $color                         
                        ])                    
                    ]);                 
                }             
            }         
        }
    }
}
