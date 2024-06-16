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
        $order = new Orders();
        $client = Client::find($request->clientID);
        $price = 0;
        $commission = 0; 
        foreach($request->products as $item){
            $response = Http::withToken(Auth::user()->jwt_token)
            ->get(env('ADMIN_PORTAL_URL').'/product-details'.'/'.$item['id']);   
            $responseJson = $response->json(); 
            $p_price = $responseJson['data']['price'];
            $commission += ($item['custom_price'] - $p_price);
            $price += $p_price;
        }
        $order->commission = $commission ;
        $order->status = "Processing" ;
        $order->customer_name = $client->name ;
        $order->total_amount = $price ;
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
        $orders = Orders::where('reseller_id',Auth::user()->id)->get()->toArray();
        $ordersArray = $orders->map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['order_date'] = Carbon::parse($itemArray['created_at'])->format('Y-m-d');
            unset($itemArray['created_at']);
            return $itemArray;
        })->toArray();
        // $order = [
        //     [
        //         'id'=> 1,
        //         'customer_name'=> "name",
        //         'total_amount'=> 100,
        //         'order_date'=> "12-5-1",
        //         'status'=> "Delivered",
        //     ],
        //     [
        //         'id'=> 1,
        //         'customer_name'=> "name",
        //         'total_amount'=> 100,
        //         'order_date'=> "12-5-1",
        //         'status'=> "Delivered",
        //     ],
        //     [
        //         'id'=> 1,
        //         'customer_name'=> "name",
        //         'total_amount'=> 100,
        //         'order_date'=> "12-5-1",
        //         'status'=> "Delivered",
        //     ],
        //     [
        //         'id'=> 1,
        //         'customer_name'=> "name",
        //         'total_amount'=> 100,
        //         'order_date'=> "12-5-1",
        //         'status'=> "Delivered",
        //     ]
        // ];
        return response()->json($ordersArray);
    }
}
