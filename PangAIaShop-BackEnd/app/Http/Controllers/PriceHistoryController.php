<?php

namespace App\Http\Controllers;

use App\Models\PriceHistory;
use App\Http\Requests\StorePriceHistoryRequest;
use App\Http\Requests\UpdatePriceHistoryRequest;

class PriceHistoryController extends Controller
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
    public function store(StorePriceHistoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceHistory $priceHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceHistory $priceHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePriceHistoryRequest $request, PriceHistory $priceHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceHistory $priceHistory)
    {
        //
    }
}
