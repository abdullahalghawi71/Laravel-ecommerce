<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


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

        if ($product->sent2) {
            return response()->json($product, 200);
        }

        $orderItem = $product->orderItems()->with('order')->latest()->first();

        if (!$orderItem || !$orderItem->order) {
            return response()->json([
                'product' => $product,
                'warning' => 'No order found for this product to send to AI.'
            ], 200);
        }

        $payload = [
            'Price' => $orderItem->unit_price,
            'Quantity' => $orderItem->quantity,
            'InvoiceDate' => $orderItem->order->order_date,
            'Invoice' => $product->code,
        ];

        try {
            $response = Http::post('http://127.0.0.1:5000/predict', $payload);
            //TODO : check the update from the the flask
            if ($response->successful()) {
                $result = $response->json();
                $product->update([
                    'fast' => $result['fast'],
                    'sent2' => true
                ]);
            } else {
                \Log::error('AI response error', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            \Log::error('AI call failed', ['message' => $e->getMessage()]);
        }

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
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Optional: check if it's valid
            if ($image->isValid()) {
                $storedPath = $image->store('products', 'public');
                $imagePath = asset('storage/' . $storedPath);
            } else {
                return response()->json(['error' => 'Invalid image file'], 400);
            }
        } else {
            return response()->json(['error' => 'Image not detected in request'], 400);
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
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'sku' => 'string|max:255',
            'description' => 'string|max:255',
            'unit_price' => 'numeric',
            'category' => 'numeric',
            'expiry_date' => 'date',
            "quantity" => 'numeric',
//            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);

        if (!$product) return response()->json(['error' => 'Not found'], 404);

        $data = $validator->validated();

        $product->update($data);

        return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'manager') {
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
