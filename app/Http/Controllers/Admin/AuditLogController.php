<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    use AuditLoggable;
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
          // Log the audit logs access
        $this->logCustomAction('access_audit_logs_dashboard', null, 'Accessed audit logs dashboard');
        
        $adminFilter = $request->input('admin_id');
        $actionFilter = $request->input('action');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $resourceFilter = $request->input('resource');
        
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
            ->when($resourceFilter, function ($query, $resource) {
                return $query->where('resource', $resource);
            })
            ->orderBy('created_at', 'desc');
        
        $auditLogs = $query->paginate(50);
          $admins = \App\Models\Admin::orderBy('username')->get();
        $resources = AdminAuditLog::distinct('resource')->pluck('resource')->filter();
        
        // Get statistics for the dashboard
        $stats = [
            'total_logs' => AdminAuditLog::count(),
            'today_logs' => AdminAuditLog::whereDate('created_at', today())->count(),
            'this_week_logs' => AdminAuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'unique_admins' => AdminAuditLog::distinct('admin_id')->count(),
        ];
        
        return view('admin.audit-logs.index', compact(
            'auditLogs', 
            'admins', 
            'resources', 
            'stats',
            'adminFilter', 
            'actionFilter', 
            'dateFrom', 
            'dateTo', 
            'resourceFilter'
        ));
    }    /**
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
        
        // Get related logs (same resource and resource_id)
        $relatedLogs = null;
        if ($auditLog->resource && $auditLog->resource_id) {
            $relatedLogs = AdminAuditLog::with('admin')
                ->where('resource', $auditLog->resource)
                ->where('resource_id', $auditLog->resource_id)
                ->where('id', '!=', $auditLog->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Get recent timeline logs from the same admin
        $timelineLogs = null;
        if ($auditLog->admin_id) {
            $timelineLogs = AdminAuditLog::with('admin')
                ->where('admin_id', $auditLog->admin_id)
                ->where('created_at', '>=', $auditLog->created_at->subHours(24))
                ->where('created_at', '<=', $auditLog->created_at->addHours(24))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
          // Log the detailed view access
        $this->logCustomAction('view_audit_log_detail', $auditLog, "Viewed detailed audit log entry #{$id}");
        
        return view('admin.audit-logs.show', compact('auditLog', 'relatedLogs', 'timelineLogs'));
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
