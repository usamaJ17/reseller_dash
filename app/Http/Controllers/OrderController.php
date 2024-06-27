<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request){
        $trx_id = null;
        foreach($request->products as $item){
            $requestParameters = [
                'quantity' => $item['quantity'],
                'product_id' => $item['id'],
                'is_buy_now' => 0,
                'trx_id' => $trx_id,
            ];
            // Send the POST request with the request parameters
            $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_URL').'/cart-store', $requestParameters);
            // get trx_id from response
            $trx_id = $response->json();
            dd($trx_id['data']['trx_id']);
        }

        
        $order = new Orders();
        $client = Client::find($request->clientID);
        $price = 0;
        $commission = 0; 
        foreach($request->products as $item){
            $response = Http::withToken(Auth::user()->jwt_token)
            ->get(env('ADMIN_PORTAL_URL').'/product-details'.'/'.$item['id']);   
            $responseJson = $response->json(); 
            $p_price = (int)$responseJson['data']['price'];
            $commission += ($item['custom_price'] - $p_price);
            $price += $p_price;
        }
        $order->commission = $commission ;
        $order->status = "Processing" ;
        $order->customer_name = $client->name ;
        $order->total_amount = $price ;
        $order->reseller_id = Auth::user()->id;
        $order->save();
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
        $orders = Orders::where('reseller_id',Auth::user()->id)->get();
        $ordersArray = $orders->map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['order_date'] = Carbon::parse($itemArray['created_at'])->format('Y-m-d');
            $itemArray['file'] = "https://test.whitexdigital.com/public/files/20240624210014_original_303.pdf";
            unset($itemArray['created_at']);
            return $itemArray;
        })->toArray();
        return response()->json($ordersArray);
    }
}
