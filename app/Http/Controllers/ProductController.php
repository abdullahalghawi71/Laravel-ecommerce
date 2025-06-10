<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $product = Product::paginate($perPage);
        return response()->json($product, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['error' => 'Not found'], 404);
        return response()->json($product, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }


        $products = $request->all();

        if (!is_array($products)) {
            return response()->json(['error' => 'Input must be an array of products'], 422);
        }

        $rules = [
            '*.name' => 'required|string|max:255',
            '*.sku' => 'required|string|max:255',
            '*.description' => 'required|string|max:255',
            '*.unit_price' => 'required|numeric',
            '*.code' => 'required',
            '*.quantity' => 'required|numeric',
        ];

        $validator = Validator::make($products, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $createdProducts = [];

        foreach ($products as $productData) {
            $createdProducts[] = Product::create($productData);
        }

        return response()->json($createdProducts, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'sku' => 'string|max:255',
            'description' => 'string|max:255',
            'unit_price' => 'numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);
        if (!$product) return response()->json(['error' => 'Not found'], 404);

        $product->update($validator->validated());
        return response()->json($product, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product = Product::find($id);
        if (!$product) return response()->json(['error' => 'Not found'], 404);

        $product->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
