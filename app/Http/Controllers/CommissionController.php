<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CommissionController extends Controller
{
    public function getall(){
        $order = [
            [
                'order_id'=> 1,
                'sale_amount'=> 10,
                'commission_amount'=> 100,
                'order_date'=> "12-5-1"
            ],
            [
                'order_id'=> 1,
                'sale_amount'=> 10,
                'commission_amount'=> 100,
                'order_date'=> "12-5-1"
            ],
            [
                'order_id'=> 1,
                'sale_amount'=> 10,
                'commission_amount'=> 100,
                'order_date'=> "12-5-1"
            ],
            [
                'order_id'=> 1,
                'sale_amount'=> 10,
                'commission_amount'=> 100,
                'order_date'=> "12-5-1"
            ]
        ];
        return response()->json($order);
    }

    public function getallPayout(){
        $order = [
            [
                'id'=> 1,
                'amount_requested'=> 100,
                'date'=> "12-5-1",
                'status'=> "Processed",
            ],
            [
                'id'=> 1,
                'amount_requested'=> 100,
                'date'=> "12-5-1",
                'status'=> "Processed",
            ],
            [
                'id'=> 1,
                'amount_requested'=> 100,
                'date'=> "12-5-1",
                'status'=> "Processed",
            ],
            [
                'id'=> 1,
                'amount_requested'=> 100,
                'date'=> "12-5-1",
                'status'=> "Processed",
            ]
        ];
        $data = [
            'payouts'=>$order,
            'total'=>500
        ];
        return response()->json($data);
    }

    public function requestPayout(Request $request){
        $data=[
            'message' => 'Payout Requested Succsessfully',
        ];
        return response()->json($data);
    }
}
