<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Order;
use App\Models\Report;
use App\Models\Issue;
use App\Models\Supplier;

class DashboardController extends Controller
{
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
