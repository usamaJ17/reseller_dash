<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request){
        dd($request->all());
        $order = new Orders();
        $order->commission = $request->amount ;
        $order->status = $request->amount ;
        $order->customer_name = $request->amount ;
        $order->total_amount = $request->amount ;
        $order->reseller_id = Auth::user()->id;
        $data=[
            'message' => 'Order Stored Succsessfully',
        ];
        return response()->json($data);
    }
    public function requestAction(Request $request){
        $data=[
            'message' => 'Request Sent Succsessfully',
        ];
        return response()->json($data);
    }

    public function getall(){
        $order = [
            [
                'id'=> 1,
                'customer_name'=> "name",
                'total_amount'=> 100,
                'order_date'=> "12-5-1",
                'status'=> "Delivered",
            ],
            [
                'id'=> 1,
                'customer_name'=> "name",
                'total_amount'=> 100,
                'order_date'=> "12-5-1",
                'status'=> "Delivered",
            ],
            [
                'id'=> 1,
                'customer_name'=> "name",
                'total_amount'=> 100,
                'order_date'=> "12-5-1",
                'status'=> "Delivered",
            ],
            [
                'id'=> 1,
                'customer_name'=> "name",
                'total_amount'=> 100,
                'order_date'=> "12-5-1",
                'status'=> "Delivered",
            ]
        ];
        return response()->json($order);
    }
}
