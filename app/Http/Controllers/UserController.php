<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login()
    {

    }

    public function signup()
    {

    }

    public function userOrders(Request $request)
    {
        $orders = Order::where('user_id',$request->input('user_id'))->all();
        return response()->json($orders,200);
    }
}
