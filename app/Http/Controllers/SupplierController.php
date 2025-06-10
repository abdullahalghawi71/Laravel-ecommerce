<?php

namespace App\Http\Controllers;


use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $suppliers = Supplier::paginate($perPage);
        return response()->json($suppliers, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'risk_score' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $supplier = Supplier::create($validator->validated());
        return response()->json($supplier, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return response()->json(['error' => 'Not found'], 404);
        return response()->json($supplier, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return response()->json(['error' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'contact_email' => 'sometimes|email|max:255',
            'risk_score' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $supplier->update($validator->validated());
        return response()->json($supplier, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return response()->json(['error' => 'Not found'], 404);

        $supplier->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
