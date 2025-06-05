<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;

class PromotionController extends Controller
{
    use AuditLoggable;/**
     * Display a unified listing of promotions and promo codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */    public function index(Request $request)
    {
        $query = PromoCode::withCount('usages')->with('createdBy');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->where('valid_until', '>', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('valid_until', '<', now());
            }
        }        if ($request->filled('type')) {
            $query->where('discount_type', $request->type);
        }        // Get entries per page (default 10)
        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [5, 10, 15, 25])) {
            $perPage = 10;
        }

        // Get paginated results (newest start date first)
        $promoCodes = $query->orderBy('valid_from', 'desc')->paginate($perPage);
        
        // Append query parameters to pagination links
        $promoCodes->appends($request->query());        // Calculate statistics
        $stats = [
            'total' => PromoCode::count(),
            'active' => PromoCode::where('is_active', true)
                                 ->where('valid_until', '>', now())
                                 ->count(),
            'inactive' => PromoCode::where('is_active', false)->count(),
            'expired' => PromoCode::where('is_active', true)
                                  ->where('valid_until', '<', now())
                                  ->count(),
            'used_this_month' => PromoCodeUsage::whereMonth('used_at', now()->month)->count(),
            'total_savings' => PromoCodeUsage::sum('discount_amount')
        ];
        
        return view('admin.promotions.index', compact('promoCodes', 'stats'));
    }

    /**
     * Show the form for creating a new promo code.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPromoCode()
    {
        return view('admin.promotions.create_promo_code');
    }

    /**
     * Store a newly created promo code in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePromoCode(Request $request)
    {        $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'discount_type' => 'required|in:percentage,fixed,free_shipping',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'target_audience' => 'nullable|json',
            'is_active' => 'required|in:0,1'
        ]);$data = $request->all();
        $data['created_by'] = Auth::guard('admin')->id();
        $data['is_active'] = $request->is_active == '1';
        
        // Parse target audience
        if ($request->target_audience) {
            $data['target_audience'] = json_decode($request->target_audience, true);
        }        $promoCode = PromoCode::create($data);

        // Log the activity
        $this->logCreate($promoCode, "Created promo code: {$promoCode->code}");

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo code created successfully!');
    }    /**
     * Display the specified promo code.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showPromoCode($id)
    {
        $promoCode = PromoCode::with(['createdBy', 'usages.user', 'usages.order'])
            ->withCount('usages')
            ->findOrFail($id);

        // Calculate statistics
        $totalSavings = $promoCode->usages->sum('discount_amount');
        $totalRevenue = $promoCode->usages->sum(function($usage) {
            return $usage->order ? $usage->order->total : 0;
        });

        return view('admin.promotions.show_promo_code', compact('promoCode', 'totalSavings', 'totalRevenue'));
    }

    /**
     * Show the form for editing the specified promo code.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editPromoCode($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        return view('admin.promotions.edit_promo_code', compact('promoCode'));
    }

    /**
     * Update the specified promo code in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePromoCode(Request $request, $id)
    {
        $promoCode = PromoCode::findOrFail($id);        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promo_codes')->ignore($id)],
            'discount_type' => 'required|in:percentage,fixed,free_shipping',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'target_audience' => 'nullable|json',
            'is_active' => 'required|in:0,1'        ]);$data = $request->all();
        $data['is_active'] = $request->is_active == '1';
        
        // Store original data for audit log
        $originalData = $promoCode->toArray();
        
        // Parse target audience
        if ($request->target_audience) {
            $data['target_audience'] = json_decode($request->target_audience, true);
        }        $promoCode->update($data);

        // Log the activity
        $this->logUpdate($promoCode, $originalData, "Updated promo code: {$promoCode->code}");

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo code updated successfully!');
    }

    /**
     * Remove the specified promo code from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    public function destroyPromoCode($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        
        // Log the activity before deleting
        $this->logDelete($promoCode, "Deleted promo code: {$promoCode->code}");
        
        $promoCode->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo code deleted successfully!');
    }

    /**
     * Toggle the active status of a promo code.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    public function togglePromoCode($id)
    {        $promoCode = PromoCode::findOrFail($id);
        $oldStatus = $promoCode->is_active;
        $newStatus = !$promoCode->is_active;
        
        $promoCode->update(['is_active' => $newStatus]);

        // Log the toggle activity
        $this->logToggle($promoCode, 'is_active', $oldStatus, $newStatus, 
            "Toggled promo code status: {$promoCode->code} from " . ($oldStatus ? 'active' : 'inactive') . 
            " to " . ($newStatus ? 'active' : 'inactive'));

        $status = $promoCode->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Promo code {$status} successfully!");
    }

    /**
     * Export promo codes data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
          $promoCodes = PromoCode::withCount('usages')
            ->with('createdBy')
            ->orderBy('valid_from', 'desc')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($promoCodes);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($promoCodes);
        }

        return redirect()->back()->with('error', 'Invalid export format!');
    }

    /**
     * Export promo codes as CSV.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $promoCodes
     * @return \Illuminate\Http\Response
     */
    private function exportCsv($promoCodes)
    {
        $filename = 'promo-codes-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($promoCodes) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Code', 'Discount Type', 'Discount Value', 'Min Order Amount', 
                'Max Uses', 'Current Usage', 'Valid From', 'Valid Until', 
                'Status', 'Created By', 'Created At'
            ]);

            // CSV Data
            foreach ($promoCodes as $code) {
                $status = $code->is_active && $code->valid_until > now() ? 'Active' : 
                         (!$code->is_active ? 'Inactive' : 'Expired');
                
                fputcsv($file, [
                    $code->code,
                    ucfirst($code->discount_type),
                    $code->discount_value,
                    $code->min_order_amount ?? 'N/A',
                    $code->max_uses ?? 'Unlimited',
                    $code->usages_count,
                    $code->valid_from->format('Y-m-d'),
                    $code->valid_until->format('Y-m-d'),
                    $status,
                    $code->createdBy->name ?? 'N/A',
                    $code->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export promo codes as PDF.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $promoCodes
     * @return \Illuminate\Http\Response
     */
    private function exportPdf($promoCodes)
    {
        // For now, return CSV format with PDF headers
        // You can implement actual PDF generation using packages like dompdf or tcpdf
        $filename = 'promo-codes-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return response()->json([
            'message' => 'PDF export functionality is not implemented yet. Please use CSV export.',
            'redirect' => route('admin.promotions.export', ['format' => 'csv'])
        ], 501);
    }
}
