<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $warehouses = Warehouse::paginate($perPage);
        return response()->json($warehouses, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'capacity' => 'required|string|max:255',
            'manager_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $manager = User::where('id', $request->input('manager_id'))->where('role', 'manager')->first();
        if (!$manager) {
            return response()->json(['error' => 'The provided manager_id is not a manager'], 422);
        }

        $warehouse = Warehouse::create($validator->validated());
        $manager->warehouse_id = $warehouse->id;
        $manager->save();
        return response()->json(['warehouse' => $warehouse], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) return response()->json(['error' => 'Not found'], 404);
        return response()->json($warehouse, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'address' => 'string|max:255',
            'capacity' => 'string|max:255',
            'manager_id' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $warehouse = Warehouse::find($id);
        if (!$warehouse) return response()->json(['error' => 'Not found'], 404);

        $warehouse->update($validator->validated());
        return response()->json($warehouse, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse not found'], 404);
        }

        $manager = User::where('warehouse_id', $warehouse->id)->first();
        if ($manager) {
            $manager->warehouse_id = null;
            $manager->save();
        }

        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted and manager unlinked'], 200);
    }
}
