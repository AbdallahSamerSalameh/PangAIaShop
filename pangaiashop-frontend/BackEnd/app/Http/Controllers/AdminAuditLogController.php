<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Http\Requests\StoreAdminAuditLogRequest;
use App\Http\Requests\UpdateAdminAuditLogRequest;

class AdminAuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminAuditLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AdminAuditLog $adminAuditLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminAuditLog $adminAuditLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminAuditLogRequest $request, AdminAuditLog $adminAuditLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminAuditLog $adminAuditLog)
    {
        //
    }
}
