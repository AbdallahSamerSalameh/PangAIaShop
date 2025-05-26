<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $adminFilter = $request->input('admin_id');
        $actionFilter = $request->input('action');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $modelTypeFilter = $request->input('model_type');
        
        $query = AdminAuditLog::with('admin')
            ->when($adminFilter, function ($query, $adminId) {
                return $query->where('admin_id', $adminId);
            })
            ->when($actionFilter, function ($query, $action) {
                return $query->where('action', 'like', "%{$action}%");
            })
            ->when($dateFrom, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($dateTo, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->when($modelTypeFilter, function ($query, $modelType) {
                return $query->where('model_type', $modelType);
            })
            ->orderBy('created_at', 'desc');
        
        $auditLogs = $query->paginate(50);
        
        $admins = \App\Models\Admin::orderBy('name')->get();
        $modelTypes = AdminAuditLog::distinct('model_type')->pluck('model_type');
        
        return view('admin.audit-logs.index', compact(
            'auditLogs', 
            'admins', 
            'modelTypes', 
            'adminFilter', 
            'actionFilter', 
            'dateFrom', 
            'dateTo', 
            'modelTypeFilter'
        ));
    }

    /**
     * Display the specified audit log.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $auditLog = AdminAuditLog::with('admin')->findOrFail($id);
        
        // Attempt to load the related model if it still exists
        $relatedModel = null;
        if ($auditLog->model_type && $auditLog->model_id && class_exists($auditLog->model_type)) {
            $relatedModel = app($auditLog->model_type)->find($auditLog->model_id);
        }
        
        return view('admin.audit-logs.show', compact('auditLog', 'relatedModel'));
    }

    /**
     * Export audit logs to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to perform this action.');
        }
        
        $adminFilter = $request->input('admin_id');
        $actionFilter = $request->input('action');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $modelTypeFilter = $request->input('model_type');
        
        $query = AdminAuditLog::with('admin')
            ->when($adminFilter, function ($query, $adminId) {
                return $query->where('admin_id', $adminId);
            })
            ->when($actionFilter, function ($query, $action) {
                return $query->where('action', 'like', "%{$action}%");
            })
            ->when($dateFrom, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($dateTo, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->when($modelTypeFilter, function ($query, $modelType) {
                return $query->where('model_type', $modelType);
            })
            ->orderBy('created_at', 'desc');
        
        $auditLogs = $query->get();
        
        $fileName = 'audit_logs_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($auditLogs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV header row
            fputcsv($file, [
                'ID', 
                'Admin', 
                'Action', 
                'Model Type', 
                'Model ID',
                'IP Address',
                'Date/Time',
            ]);
            
            // Add audit logs
            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->admin ? $log->admin->name : 'Unknown',
                    $log->action,
                    $log->model_type,
                    $log->model_id,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
