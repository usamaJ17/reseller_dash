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
        $response = Http::get('https://test.whitexdigital.com/api/get_payout/11');   
        $responseJson = $response->json();     
        $data = [
            'payouts'=>$responseJson,
            'total'=>500
        ];
        return response()->json($data);
    }

    public function requestPayout(Request $request){
        $requestParameters = [
            'reseller_name' => Auth::user()->name,
            'reseller_id' => Auth::user()->id,
            'amount' => $request->amount,
        ];

        // Send the POST request with the request parameters
        $response = Http::post(env('ADMIN_PORTAL_URL_OTHER').'/process_payout', $requestParameters);

        // Decode the response JSON
        $responseJson = $response->json();

        return response($responseJson);
    }
}
