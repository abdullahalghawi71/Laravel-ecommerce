<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Order;
use App\Models\Report;
use App\Models\Issue;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $employees = User::where('role', 'employee')->paginate(10);
        return response()->json($employees, 200);
    }
    public function store(Request $request)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'employee',
        ]);

        return response()->json(['message' => 'Employee created successfully.', 'user' => $user], 201);
    }
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::where('role', 'employee')->find($id);
        if (!$user) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::where('role', 'employee')->find($id);
        if (!$user) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        $user->update($data);

        return response()->json(['message' => 'Employee updated successfully.', 'user' => $user], 200);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::where('role', 'employee')->find($id);
        if (!$user) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Employee deleted successfully.'], 200);
    }

    public function statistics()
    {
        $data = [
            'products' => Product::count(),
            'suppliers' => Supplier::count(),
            'employees' => User::where('role', 'employee')->count(),
            'orders_awaiting_shipment' => Order::where('status', 'awaiting_shipment')->count(),
            'low_quantity_products' => Product::where('quantity', '<', 10)->count(),
            'low_stock_products' => Product::where('quantity', '<', 10)->count(),
            'reports' => Report::count(),
            'issues' => Issue::count(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Dashboard statistics loaded successfully',
            'data' => $data
        ]);
    }


}
