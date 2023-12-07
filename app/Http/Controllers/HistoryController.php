<?php

namespace App\Http\Controllers;

use App\Models\Histories;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->guard('api')->user();


        return $this->Res($user->histories, 'got data successfully', 200);
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
                'user_id' => Auth::id(),
                'qty' => $cartItem->qty,
                'price' => $cartItem->price
            ]);
        }

        // Delete records from the shopping cart
        ShoppingCart::whereIn('product_id', $productIds)->delete();

        return $this->Res(null, 'Payment Success', 200);
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
