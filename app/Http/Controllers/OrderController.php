<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $order = Order::paginate($perPage);
        return response()->json($order, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_price' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('id', $request->input('user_id'))
            ->where('role', 'user')
            ->first();

        if (!$user) {
            return response()->json(['error' => 'The provided user_id is not a regular user'], 422);
        }

        $order = Order::create([
            'order_date' => now(),
            'status' => 'pending',
            'user_id' => $request->input('user_id'),
            'total_price' => $request->input('total_price'),
        ]);

        return response()->json(['order' => $order], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['error' => 'Not found'], 404);
        return response()->json($order, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'order_date' => 'date',
            'total_price' => 'string|max:255',
            'user_id' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::find($id);
        if (!$order) return response()->json(['error' => 'Not found'], 404);

        $order->update($validator->validated());
        return response()->json($order, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted'], 200);
    }
}
