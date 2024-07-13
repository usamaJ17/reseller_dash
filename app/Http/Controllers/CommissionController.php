<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CommissionController extends Controller
{
    public function getall(){
        $orders = Orders::where('reseller_id', Auth::user()->id)->get();
        $data = [];
        foreach ($orders as $order) {
            $order_arr = [
                'order_id' => $order->id,
                'sale_amount' => $order->total_amount,
                'commission_amount' => $order->commission,
                'order_date' => $order->created_at->format('Y-m-d')
            ];
            $data[] = $order_arr;
        }
        return response()->json($data);
    }

    public function getallPayout(){
        $response = Http::get(env('ADMIN_PORTAL_URL_OTHER').'/get_payout'.'/'.Auth::user()->id);   
        $responseJson = $response->json();     
        $data = [
            'payouts'=>$responseJson,
            'total'=>500
        ];
        return response()->json($data);
    }

    public function requestPayout(Request $request){
        preg_match('/\d+(\.\d+)?/', $request->amount, $matches);

        // Convert the extracted value to a float
        $amount = isset($matches[0]) ? (float) $matches[0] : 0.0;
        $requestParameters = [
            'requested_by' => Auth::user()->name,
            'reseller_id' => Auth::user()->id,
            'reseller_email' => Auth::user()->email,
            'message' => $request->note,
            'amount' => $amount,
        ];
        // Send the POST request with the request parameters
        $response = Http::post(env('ADMIN_PORTAL_URL_OTHER').'/process_payout', $requestParameters);
        // Decode the response JSON
        $responseJson = $response->body();

        return response($responseJson , 200);
    }
}
