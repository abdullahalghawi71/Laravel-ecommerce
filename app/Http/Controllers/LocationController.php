<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Location::all(),200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aisle' => 'required|string|max:255',
            'shelf' => 'required|string|max:255',
            'bin' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $warehouse = Warehouse::where('id',$request->input('warehouse_id'))->first();
        if (!$warehouse) {
            return response()->json(['error' => 'The provided warehouse_id is not found'], 422);
        }

        $location = Location::create($validator->validated());
        return response()->json(['location' => $location], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $location = Location::find($id);
        if(!$location) return response()->json(['error' => 'Not found'], 404);
        return response()->json($location,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'aisle' => 'string|max:255',
            'shelf' => 'string|max:255',
            'bin' => 'string|max:255',
            'type' => 'string|max:255',
            'warehouse_id' => 'exists:warehouses,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $location = Location::find($id);
        if(!$location) return response()->json(['error' => 'Not found'],404);

        $location->update($validator->validated());
        return response()->json($location,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        foreach ($location->inventories as $inventory) {
            $inventory->location_id = null;
            $inventory->save();
        }

        $location->delete();
        return response()->json(['message' => 'Location deleted and related inventories disassociated'], 200);
    }
}
