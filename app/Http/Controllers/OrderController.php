<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request){
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
