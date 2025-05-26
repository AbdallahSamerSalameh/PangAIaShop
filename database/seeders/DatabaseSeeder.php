<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed independent tables
        $this->call([
            AdminSeeder::class,           // Admins first
            UserSeeder::class,            // Then users
            VendorSeeder::class,          // Vendors for products
            CategoriesSeeder::class,         // Categories for products
            
            // Tables that depend on users
            UserPreferenceSeeder::class,
            
            // Product-related tables
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductImageSeeder::class,
            ProductCategorySeeder::class,
            InventorySeeder::class,
            PriceHistorySeeder::class,
            
            // User interaction tables
            WishlistSeeder::class,
            WishlistItemSeeder::class,
            CartSeeder::class,
            ReviewSeeder::class,
            
            // Order-related tables
            PromoCodeSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            ShipmentSeeder::class,
            
            // Support and audit tables
            SupportTicketSeeder::class,
            AdminAuditLogSeeder::class,
        ]);
    }
}
