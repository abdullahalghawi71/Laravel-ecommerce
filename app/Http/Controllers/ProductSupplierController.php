<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ProductSupplierController extends Controller
{
    public function index()
    {
        $data = ProductSupplier::with(['product', 'supplier'])->paginate(10);
        return response()->json($data);
    }

    public function show($product_id, $supplier_id)
    {
        $pivot = ProductSupplier::where('product_id', $product_id)
            ->where('supplier_id', $supplier_id)
            ->first();

        if (!$pivot) return response()->json(['error' => 'Not found'], 404);
        return response()->json($pivot);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'preferred_supplier' => 'boolean',
            'lead_time_days' => 'integer|min:0',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $pivot = ProductSupplier::create($validator->validated());
        return response()->json($pivot, 201);
    }

    public function update(Request $request, $product_id, $supplier_id)
    {
        $pivot = ProductSupplier::where('product_id', $product_id)
            ->where('supplier_id', $supplier_id)
            ->first();

        if (!$pivot) return response()->json(['error' => 'Not found'], 404);

        $pivot->update($request->only(['preferred_supplier', 'lead_time_days']));
        return response()->json($pivot);
    }

    public function destroy($product_id, $supplier_id)
    {
        $pivot = ProductSupplier::where('product_id', $product_id)
            ->where('supplier_id', $supplier_id)
            ->first();

        if (!$pivot) return response()->json(['error' => 'Not found'], 404);

        $pivot->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function featured_products()
    {
        $products = Product::where('sent', false)->get();

        $count = $products->count();

        if ($count < 2) {
            \Log::info("Only $count unsent products found. Not sending yet.");
            return 0;
        }

        $payload = $products->map(function ($product) {
            return [
                'code' => $product->code,
                'sku' => $product->sku,
                'quantity' => $product->quantity,
                'unit_price' => $product->unit_price,
            ];
        })->toArray();

        //Todo : update the product when sent to AI

        try {
            $responsetrain = Http::post('http://192.168.15.226:5000/train', $payload);
            $trainData = $responsetrain->json();

            if (!isset($trainData['success']) || $trainData['success'] !== 'model trained') {
                \Log::error('Failed training: ' . $responsetrain->body());
                return response()->json([
                    'error' => 'Training failed',
                    'response' => $responsetrain->body()
                ], 500);
            }

            Product::whereIn('id', $products->pluck('id'))->update(['sent' => true]);
            \Log::info("Sent $count products for training successfully.");

            // Step 2: Send prediction request to Flask
            $responsePrediction = Http::post('http://192.168.15.226:5000/prediction', $payload);
            $predictionData = $responsePrediction->json();

            // Check if prediction response has product codes
            if (!isset($predictionData['products']) || !is_array($predictionData['products'])) {
                \Log::error('Invalid prediction response: ' . $responsePrediction->body());
                return response()->json([
                    'error' => 'Invalid prediction response',
                    'response' => $responsePrediction->body()
                ], 500);
            }

            $predictedProducts = Product::whereIn('code', $predictionData['products'])->get();

            // Final response
            return response()->json([
                'message' => "Training and prediction completed.",
                'trained_count' => $count,
                'predicted_products' => $predictedProducts
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Exception communicating with Flask: ' . $e->getMessage());
            return response()->json([
                'error' => 'Exception during Flask communication',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
