<?php

namespace App\Http\Controllers;

use App\Models\Histories;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;

class HistoryController extends Controller
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
    public function store(Request $request)
    {
        $requests = $request->json()->all();

        // Check if $requests is an array of requests or a single request
        if (!is_array(reset($requests))) {
            // If it's a single request, convert it to an array of requests
            $requests = [$requests];
        }

        $productIds = array_column($requests, 'product_id');
        $shoppingCartId = array_column($requests, 'shopping_cart_id');
        // Use whereIn to retrieve all records with matching product_ids
        $cartItems = ShoppingCart::whereIn('product_id', $productIds)
            ->whereIn('id', $shoppingCartId)
            ->get();

        // Update the 'paid' column to 1 (it's mean paid done) for each retrieved record
        $cartItems->each(function ($cartItem) {
            $cartItem->update(['paid' => 1]);
        });

        // Move data to the history table
        foreach ($cartItems as $cartItem) {
            Histories::create([
                'product_id' => $cartItem->product_id,
                'qty' => $cartItem->qty,
            ]);
        }

        // Delete records from the shopping cart
        ShoppingCart::whereIn('product_id', $productIds)->delete();

        return $this->Res($cartItems, 'Payment Success', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
