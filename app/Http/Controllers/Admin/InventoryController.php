<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $searchQuery = $request->input('search');
        $stockStatus = $request->input('stock_status');
        
        $inventory = Product::with('inventory')
            ->when($searchQuery, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->when($stockStatus === 'in_stock', function ($query) {
                return $query->whereHas('inventory', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->when($stockStatus === 'low_stock', function ($query) {
                return $query->whereHas('inventory', function ($q) {
                    $q->where('quantity', '<=', 10)->where('quantity', '>', 0);
                });
            })
            ->when($stockStatus === 'out_of_stock', function ($query) {
                return $query->whereHas('inventory', function ($q) {
                    $q->where('quantity', '<=', 0);
                })
                ->orDoesntHave('inventory');
            })
            ->paginate(20);
        
        return view('admin.inventory.index', compact('inventory', 'searchQuery', 'stockStatus'));
    }

    /**
     * Show the form for editing the specified inventory item.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $product = Product::with('inventory')->findOrFail($id);
        
        return view('admin.inventory.edit', compact('product'));
    }

    /**
     * Update the specified inventory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
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

        // Update the inventory record
        $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
        $inventory->fill($validatedData);
        $inventory->save();

        // Update the in_stock status based on the quantity
        $product->update([
            'in_stock' => $validatedData['quantity'] > 0,
            'sku' => $validatedData['sku'] ?? $product->sku,
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory updated successfully!');
    }

    /**
     * Batch update inventory quantities
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validatedData) {
            foreach ($validatedData['products'] as $productData) {
                $product = Product::find($productData['id']);
                
                if ($product) {
                    $inventory = Inventory::firstOrNew(['product_id' => $product->id]);
                    $inventory->quantity = $productData['quantity'];
                    $inventory->save();
                    
                    $product->update([
                        'in_stock' => $productData['quantity'] > 0,
                    ]);
                }
            }
        });

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory batch updated successfully!');
    }

    /**
     * Import inventory from a CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
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
        
        DB::transaction(function () use ($records, $header, &$successCount, &$errorCount) {
            foreach ($records as $record) {
                $data = array_combine($header, $record);
                
                // Find product by SKU
                $product = Product::where('sku', $data['sku'])->first();
                
                if ($product) {
                    $quantity = (int) $data['quantity'];
                    
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
                    
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        });

        return redirect()->route('admin.inventory.index')
            ->with('success', "{$successCount} products updated successfully. {$errorCount} products not found.");
    }

    /**
     * Export inventory to CSV
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
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
