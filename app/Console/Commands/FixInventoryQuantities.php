<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class FixInventoryQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:fix-quantities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix inventory quantities by ensuring they are stored as integers in the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting inventory quantity fix...');
        
        // Get all inventory records
        $inventories = Inventory::all();
        
        $this->info('Found ' . $inventories->count() . ' inventory records');
        
        $fixed = 0;
        
        foreach ($inventories as $inventory) {
            $oldQuantity = $inventory->quantity;
            $oldType = gettype($oldQuantity);
            
            // Ensure quantity is an integer
            $newQuantity = is_numeric($oldQuantity) ? intval($oldQuantity) : 0;
            
            // Only update if the value type was wrong
            if ($oldType !== 'integer' || $oldQuantity !== $newQuantity) {
                $this->line("Fixing inventory ID: {$inventory->id}, product ID: {$inventory->product_id}");
                $this->line("  Old quantity: {$oldQuantity} ({$oldType})");
                $this->line("  New quantity: {$newQuantity} (integer)");
                
                // Use query builder directly to update the value
                DB::table('inventories')
                    ->where('id', $inventory->id)
                    ->update(['quantity' => $newQuantity]);
                
                $fixed++;
            }
        }
        
        $this->info("Fixed {$fixed} inventory records");
        $this->info('Done!');
        
        return 0;
    }
}
