<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use App\Http\Requests\StoreWishlistItemRequest;
use App\Http\Requests\UpdateWishlistItemRequest;

class WishlistItemController extends Controller
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
    public function store(StoreWishlistItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WishlistItem $wishlistItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WishlistItem $wishlistItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWishlistItemRequest $request, WishlistItem $wishlistItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WishlistItem $wishlistItem)
    {
        //
    }
}
