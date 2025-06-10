<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $inventories = Inventory::with(['product', 'location'])->paginate($perPage);
        return response()->json($inventories, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'location_id' => 'required|exists:locations,id',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $inventory = Inventory::create($validator->validated());
        return response()->json($inventory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $inventory = Inventory::with(['product', 'location'])->find($id);
        if (!$inventory) return response()->json(['error' => 'Not found'], 404);
        return response()->json($inventory,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) return response()->json(['error' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'quantity' => 'integer|min:0',
            'batch_number' => 'string|max:255',
            'expiry_date' => 'date',
            'location_id' => 'exists:locations,id',
            'product_id' => 'exists:products,id',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $inventory->update($validator->validated());
        return response()->json($inventory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) return response()->json(['error' => 'Not found'], 404);

        $inventory->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
