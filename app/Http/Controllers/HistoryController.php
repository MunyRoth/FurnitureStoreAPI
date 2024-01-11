<?php

namespace App\Http\Controllers;

use App\Models\Histories;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Histories::with(['product'])
            ->select([
                'id',
                'product_id',
                'user_id',
                'qty',
                DB::raw('DATE_FORMAT(created_at, "%y-%m-%d %H:%i:%s") as formatted_created_at'),
                DB::raw('DATE_FORMAT(updated_at, "%y-%m-%d %H:%i:%s") as formatted_updated_at')
            ])
            ->where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->Res($data, 'got data successfully', 200);
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
            $existingHistory = Histories::where([
                'product_id' => $cartItem->product_id,
                'user_id' => Auth::id(),
            ])->first();

            if ($existingHistory) {
                // Update quantity if record already exists
                $existingHistory->update([
                    'qty' => $existingHistory->qty + $cartItem->qty,
                ]);
            } else {
                // Create a new record if it doesn't exist
                Histories::create([
                    'product_id' => $cartItem->product_id,
                    'user_id' => Auth::id(),
                    'qty' => $cartItem->qty,
                ]);
            }
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
    public function destroy(Request $request)
    {
        $requests = $request->json()->all();
        $user_id = Auth::id();

        // Check if $requests is an array of requests or a single request
        if (!is_array(reset($requests))) {
            // If it's a single request, convert it to an array of requests
            $requests = [$requests];
        }

        foreach ($requests as $requestItem) {
            // If validation passes, delete the record from the "histories" table
            Histories::where('user_id', $user_id)
                ->where('product_id', $requestItem['product_id'])
                ->delete();
        }
        return $this->Res(null, 'Products deleted successfully', 200);
    }
}
