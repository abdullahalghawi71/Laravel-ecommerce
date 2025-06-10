<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $transactions = Transaction::with(['user', 'inventory'])->paginate($perPage);
        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'inventory'])->find($id);
        if (!$transaction) return response()->json(['error' => 'Not found'], 404);
        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:inbound,outbound',
            'quantity' => 'required|integer|min:1',
            'user_id' => 'required|exists:users,id',
            'inventory_id' => 'required|exists:inventories,id',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $transaction = Transaction::create($validator->validated());
        return response()->json($transaction, 201);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) return response()->json(['error' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'type' => 'string|in:inbound,outbound',
            'quantity' => 'integer|min:1',
            'user_id' => 'exists:users,id',
            'inventory_id' => 'exists:inventories,id',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $transaction->update($validator->validated());
        return response()->json($transaction);
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) return response()->json(['error' => 'Not found'], 404);

        $transaction->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
