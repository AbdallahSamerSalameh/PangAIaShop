<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use AuditLoggable;
    /**
     * Display a listing of the inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {
        // Log inventory access with filter details
        $filterDetails = collect([
            'search' => $request->input('search'),
            'stock_status' => $request->input('stock_status'),
            'per_page' => $request->input('per_page', 25)
        ])->filter()->toArray();
          $this->logCustomAction(
            'inventory_list_viewed',
            null,
            'Viewed inventory listing' . ($filterDetails ? ' with filters: ' . json_encode($filterDetails) : '')
        );

        $searchQuery = $request->input('search');
        $stockStatus = $request->input('stock_status');
        $perPage = $request->input('per_page', 25); // Default to 25 items per page        // Step 1: Create a query that will retrieve product IDs ordered by inventory quantity
        $productIdsQuery = DB::table('products')
            ->leftJoin('inventories', function ($join) {
                $join->on('products.id', '=', 'inventories.product_id')
                     ->whereNull('inventories.variant_id')
                     ->whereNull('inventories.deleted_at');
            })
            ->select('products.id', DB::raw('COALESCE(inventories.quantity, 0) as inventory_quantity'))
            ->orderByDesc('inventory_quantity')
            ->orderBy('products.name');

        // Apply the search filters if needed
        if ($searchQuery) {
            $productIdsQuery->where(function($query) use ($searchQuery) {
                $query->where('products.name', 'like', "%{$searchQuery}%")
                      ->orWhere('products.sku', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply stock status filters
        if ($stockStatus === 'in_stock') {
            $productIdsQuery->where('inventories.quantity', '>', 10);
        } elseif ($stockStatus === 'low_stock') {
            $productIdsQuery->where('inventories.quantity', '<=', 10)
                           ->where('inventories.quantity', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $productIdsQuery->where(function ($query) {
                $query->where('inventories.quantity', '<=', 0)
                      ->orWhereNull('inventories.quantity');
            });
        }        // Get the ordered product IDs - handle pagination separately
        $orderedProductIds = $productIdsQuery->pluck('id');

        // Step 2: Use these IDs to retrieve full product data with proper ordering
        if ($orderedProductIds->isEmpty()) {
            // No products match the filter criteria
            $inventory = Product::where('id', 0)->paginate($perPage); // Empty result
        } else {
            $inventory = Product::with(['images', 'categories'])
                ->select([
                    'products.*',
                    DB::raw('(SELECT COALESCE(quantity, 0) FROM inventories 
                             WHERE inventories.product_id = products.id 
                             AND inventories.variant_id IS NULL 
                             AND inventories.deleted_at IS NULL) as inventory_quantity')
                ])
                ->whereIn('id', $orderedProductIds)
                // Use FIELD() for MySQL to maintain exact ordering from the first query
                ->orderByRaw('FIELD(id,' . implode(',', $orderedProductIds->toArray()) . ')')
                ->paginate($perPage);
        }// Load the inventory relationship for each product after pagination
        $inventory->load('inventory');
        
        // Make sure the inventory_quantity value is properly accessible in the view
        foreach ($inventory as $product) {
            // Cast to integer to ensure proper numeric ordering
            $product->inventory_quantity = (int) $product->inventory_quantity;
        }

        // Add directCategories property for each product (same logic as ProductController)
        foreach ($inventory as $product) {
            $leafCategories = $product->categories->filter(function($category) {
                return $category->isLeafCategory();
            });
            
            // Sort leaf categories by primary status first, then by name
            $product->directCategories = $leafCategories->sortByDesc(function($category) {
                return $category->pivot->is_primary_category;
            });
        }

        // Calculate statistics for cards
        $totalProducts = Product::count();
        
        $inStockProducts = Product::whereHas('inventory', function ($q) {
            $q->where('quantity', '>', 10);
        })->count();
        
        $lowStockProducts = Product::whereHas('inventory', function ($q) {
            $q->where('quantity', '<=', 10)->where('quantity', '>', 0);
        })->count();
        
        $outOfStockProducts = Product::where(function ($query) {
            $query->whereHas('inventory', function ($q) {
                $q->where('quantity', '<=', 0);
            })->orDoesntHave('inventory');
        })->count();
        
        return view('admin.inventory.index', compact(
            'inventory', 
            'searchQuery', 
            'stockStatus',
            'totalProducts',
            'inStockProducts',
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }

    /**
     * Show the form for editing the specified inventory item.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */    public function edit($id)
    {
        $product = Product::with('inventory')->findOrFail($id);
          // Log inventory edit form access
        $this->logCustomAction(
            'inventory_edit_viewed',
            $product,
            "Viewed inventory edit form for product: {$product->name} (SKU: {$product->sku})"
        );
        
        return view('admin.inventory.edit', compact('product'));
    }/**
     * Update the specified inventory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'restock_status' => 'nullable|in:Ordered,Pending,Not Required',
            'restock_eta' => 'nullable|date',
            'safety_stock' => 'nullable|integer|min:0',
        ]);

        try {
            // Store original data for audit logging
            $originalInventory = Inventory::where('product_id', $product->id)->first();
            $originalData = $originalInventory ? $originalInventory->toArray() : [];
            $originalProductData = ['sku' => $product->sku, 'in_stock' => $product->in_stock];
            
            // Update the inventory record
            $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
            $inventory->fill($validatedData);
            $inventory->save();

            // Update the in_stock status based on the quantity
            $product->update([
                'in_stock' => $validatedData['quantity'] > 0,
                'sku' => $validatedData['sku'] ?? $product->sku,
            ]);

            // Log the inventory update
            $changes = [];
            if (isset($originalData['quantity']) && $originalData['quantity'] != $validatedData['quantity']) {
                $changes[] = "quantity: {$originalData['quantity']} → {$validatedData['quantity']}";
            } elseif (!isset($originalData['quantity'])) {
                $changes[] = "quantity set to: {$validatedData['quantity']}";
            }
            
            if (isset($validatedData['location']) && (!isset($originalData['location']) || $originalData['location'] != $validatedData['location'])) {
                $oldLocation = $originalData['location'] ?? 'none';
                $changes[] = "location: {$oldLocation} → {$validatedData['location']}";
            }
            
            if (isset($validatedData['low_stock_threshold']) && (!isset($originalData['low_stock_threshold']) || $originalData['low_stock_threshold'] != $validatedData['low_stock_threshold'])) {
                $oldThreshold = $originalData['low_stock_threshold'] ?? 'none';
                $changes[] = "low stock threshold: {$oldThreshold} → {$validatedData['low_stock_threshold']}";
            }
            
            if (isset($validatedData['restock_status']) && (!isset($originalData['restock_status']) || $originalData['restock_status'] != $validatedData['restock_status'])) {
                $oldStatus = $originalData['restock_status'] ?? 'none';
                $changes[] = "restock status: {$oldStatus} → {$validatedData['restock_status']}";
            }
            
            if ($originalProductData['sku'] != ($validatedData['sku'] ?? $product->sku)) {
                $changes[] = "SKU: {$originalProductData['sku']} → {$validatedData['sku']}";
            }
            
            $description = "Updated inventory for product: {$product->name}";
            if (!empty($changes)) {
                $description .= " - Changes: " . implode(', ', $changes);
            }
            
            $this->logUpdate($product, $originalData, $description);

            // Return JSON response for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory updated successfully!',
                    'data' => [
                        'product_id' => $product->id,
                        'quantity' => $validatedData['quantity'],
                        'in_stock' => $validatedData['quantity'] > 0
                    ]
                ]);
            }

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory updated successfully!');
                  } catch (\Exception $e) {
            // Log error for audit trail
            $this->logCustomAction(
                'inventory_update_failed',
                $product,
                "Failed to update inventory for product: {$product->name} - Error: {$e->getMessage()}"
            );
            
            // Handle errors
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating inventory: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.inventory.index')
                ->with('error', 'Error updating inventory: ' . $e->getMessage());
        }
    }

    /**
     * Batch update inventory quantities
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */    public function batchUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
        ]);

        try {
            $updatedProducts = [];
            
            DB::transaction(function () use ($validatedData, &$updatedProducts) {
                foreach ($validatedData['products'] as $productData) {
                    $product = Product::find($productData['id']);
                    
                    if ($product) {
                        // Store original quantity for audit logging
                        $originalInventory = Inventory::where('product_id', $product->id)->first();
                        $originalQuantity = $originalInventory ? $originalInventory->quantity : 0;
                        
                        $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
                        $inventory->quantity = $productData['quantity'];
                        $inventory->save();
                        
                        $product->update([
                            'in_stock' => $productData['quantity'] > 0,
                        ]);
                        
                        $updatedProducts[] = [
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'old_quantity' => $originalQuantity,
                            'new_quantity' => $productData['quantity']
                        ];
                    }
                }
            });
            
            // Log the batch update
            $productNames = collect($updatedProducts)->pluck('name')->take(5)->implode(', ');
            $totalCount = count($updatedProducts);
            
            $description = "Batch updated inventory for {$totalCount} products";            if ($totalCount <= 5) {
                $description .= ": {$productNames}";
            } else {
                $description .= " including: {$productNames} and " . ($totalCount - 5) . " more";
            }
            
            $this->logCustomAction(
                'inventory_batch_updated',
                null,
                $description,
                ['updated_products' => $updatedProducts]
            );

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory batch updated successfully!');
                
        } catch (\Exception $e) {
            // Log error for audit trail
            $this->logCustomAction(
                'inventory_batch_update_failed',
                null,
                "Failed to batch update inventory - Error: {$e->getMessage()}"
            );
            
            return redirect()->route('admin.inventory.index')
                ->with('error', 'Error batch updating inventory: ' . $e->getMessage());
        }
    }

    /**
     * Import inventory from a CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $records = array_map('str_getcsv', file($path));
        
        // Assumes first row is header
        $header = array_shift($records);
        
        $successCount = 0;
        $errorCount = 0;
        $importedProducts = [];
        
        try {
            DB::transaction(function () use ($records, $header, &$successCount, &$errorCount, &$importedProducts) {
                foreach ($records as $record) {
                    $data = array_combine($header, $record);
                    
                    // Find product by SKU
                    $product = Product::where('sku', $data['sku'])->first();
                    
                    if ($product) {
                        $quantity = (int) $data['quantity'];
                        
                        // Store original quantity for audit logging
                        $originalInventory = Inventory::where('product_id', $product->id)->first();
                        $originalQuantity = $originalInventory ? $originalInventory->quantity : 0;
                        
                        // Update inventory
                        $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
                        $inventory->quantity = $quantity;
                        $inventory->location = $data['location'] ?? $inventory->location;
                        $inventory->low_stock_threshold = isset($data['low_stock_threshold']) 
                            ? (int) $data['low_stock_threshold'] 
                            : $inventory->low_stock_threshold;
                        $inventory->save();
                        
                        // Update product stock status
                        $product->update(['in_stock' => $quantity > 0]);
                        
                        $importedProducts[] = [
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'old_quantity' => $originalQuantity,
                            'new_quantity' => $quantity
                        ];
                        
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            });
            
            // Log the import action
            $fileName = $request->file('csv_file')->getClientOriginalName();
            $productNames = collect($importedProducts)->pluck('name')->take(5)->implode(', ');
            
            $description = "Imported inventory from CSV file: {$fileName} - {$successCount} products updated successfully";
            if ($errorCount > 0) {
                $description .= ", {$errorCount} products not found";
            }
            if ($successCount <= 5) {
                $description .= " - Products: {$productNames}";
            } else {
                $description .= " - Including: {$productNames} and " . ($successCount - 5) . " more";
            }
              $this->logCustomAction(
                'inventory_imported',
                null,
                $description,
                [
                    'file_name' => $fileName,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'imported_products' => $importedProducts
                ]
            );
              } catch (\Exception $e) {
            // Log error for audit trail
            $this->logCustomAction(
                'inventory_import_failed',
                null,
                "Failed to import inventory from CSV - Error: {$e->getMessage()}"
            );
            
            return redirect()->route('admin.inventory.index')
                ->with('error', 'Error importing inventory: ' . $e->getMessage());
        }

        return redirect()->route('admin.inventory.index')
            ->with('success', "{$successCount} products updated successfully. {$errorCount} products not found.");
    }

    /**
     * Export inventory to CSV
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */    public function export()
    {        // Log the export action
        $this->logCustomAction(
            'inventory_exported',
            null,
            'Exported inventory data to CSV file'
        );
        
        $fileName = 'inventory_export_' . date('Y-m-d_His') . '.csv';
        
        $products = Product::with('inventory')->get();
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Add CSV header row
            fputcsv($file, ['product_id', 'sku', 'name', 'quantity', 'location', 'low_stock_threshold']);
            
            // Add products
            foreach ($products as $product) {
                fputcsv($file, [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'quantity' => $product->inventory ? $product->inventory->quantity : 0,
                    'location' => $product->inventory ? $product->inventory->location : '',
                    'low_stock_threshold' => $product->inventory ? $product->inventory->low_stock_threshold : 5,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
