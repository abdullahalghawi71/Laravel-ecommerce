<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page',10);
        $orderItems = OrderItem::paginate($perPage);
        return response()->json($orderItems,200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $orderItem = OrderItem::find($id);
        if(!$orderItem) return response()->json(['error' => 'Not found'], 404);
        return response()->json($orderItem,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orderItem = OrderItem::find($id);
        if (!$orderItem) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'exists:orders,id',
            'product_id' => 'exists:products,id',
            'quantity' => 'integer|min:1',
            'unit_price' => 'numeric|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $orderItem->update($validator->validated());

        return response()->json($orderItem, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json(['error' => 'Order Items not found'], 404);
        }

        $orderItem->delete();
        return response()->json(['message' => 'Order Items deleted'], 200);
    }
}
