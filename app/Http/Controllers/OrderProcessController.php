<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OrderProcessController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json(['error' => 'Data must be an array of items'], 422);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalPrice = 0;

        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            $product = Product::find($validated['product_id']);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $totalPrice += $validated['unit_price'] * $validated['quantity'];
        }

        $order = Order::create([
            'order_date' => now(),
            'status' => 'pending',
            'user_id' => $user->id,
            'total_price' => $totalPrice
        ]);

        $createdItems = [];

        foreach ($data as $item) {
            $validated = Validator::make($item, [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0'
            ])->validated();

            $validated['order_id'] = $order->id;

            $createdItems[] = OrderItem::create($validated);
        }

        return response()->json([
            'order_id' => $order->id,
            'items' => $createdItems
        ], 201);
    }

    public function featured_products()
    {
        $response = Http::post('http://192.168.15.226:5000/train');

    }
}
