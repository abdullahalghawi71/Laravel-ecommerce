<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 2);
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'description' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'code' => 'required|string|unique:products,code',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'required|date|min:0',
            'category' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $storedPath = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $storedPath;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'description' => $validated['description'],
            'unit_price' => $validated['unit_price'],
            'code' => $validated['code'],
            'quantity' => $validated['quantity'],
            'category' => $validated['category'],
            'expiry_date' => $validated['expiry_date'],
            'image_path' => $imagePath,
        ]);

        return response()->json($product, 201);
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
            'unit_price' => 'numeric',
            'category' => 'numeric',
            'expiry_date' => 'date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);
        if (!$product) return response()->json(['error' => 'Not found'], 404);

        $data = $validator->validated();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $data['image_path'] = $imagePath;
        }

        $product->update($data);
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

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }

    public function GetProductByCategory($id)
    {
        $products = Product::where('category',$id)->paginate(2);;
        return response()->json($products, 200);
    }
}
