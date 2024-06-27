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
    public function store(Request $request)
    {
        $trx_id = null;
        $response_1 = null;
        foreach ($request->products as $item) {
            $requestParameters = [
                'quantity' => $item['quantity'],
                'product_id' => $item['id'],
                'custom_price' => $item['custom_price'],
                'is_buy_now' => 0,
                'trx_id' => $trx_id,
            ];
            // Send the POST request with the request parameters
            $response_1 = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_URL') . '/cart-store', $requestParameters);
            if (!$response_1->json()['data']) {
                return response()->json($response_1->json(), 500);
            }
            $trx_id = $response_1->json()['data']['trx_id'];
        }
        $client = Client::find($request->clientID);
        $price = 0;
        $custom_price = 0;
        $commission = 0;
        foreach ($request->products as $item) {
            $response = Http::withToken(Auth::user()->jwt_token)
                ->get(env('ADMIN_PORTAL_URL') . '/product-details' . '/' . $item['id']);
            $responseJson = $response->json();
            $p_price = (int)$responseJson['data']['price'];
            $commission = $commission  + (($item['custom_price'] - $p_price) * $item['quantity']);
            $total = $p_price * $item['quantity']; 
            $custom_total = $item['custom_price'] * $item['quantity'];
            $price = $price + $total;
            $custom_price = $custom_price + $custom_total;
        }
        $requestParameters = [
            "payment_type" => 0,
            "sub_total" => $price,
            "discount_offer" => 0,
            "shipping_tax" => 0,
            "tax" => 0,
            "coupon_discount" => 0,
            "total" => $price,
            'trx_id' => $trx_id,
            'client_id' => $request->clientID,
            'shipping_address' => [
                "name" => $client->name,
                "email" => $client->email,
                "phone_no" => $client->contact,
                "address" => $client->address,
                "address_ids" => [
                    "country_id" => $client->country_id,
                    "state_id" => $client->state_id,
                    "city_id" => $client->city_id,
                ],
                "country" => "United States",
                "state" => "new york",
                "city" => "new york",
                "postal_code" => $client->postal_code,
            ],
            'billing_address' => [
                "name" => $client->name,
                "email" => $client->email,
                "phone_no" => $client->contact,
                "address" => $client->address,
                "address_ids" => [
                    "country_id" => $client->country_id,
                    "state_id" => $client->state_id,
                    "city_id" => $client->city_id,
                ],
                "postal_code" => $client->postal_code,
            ]
        ];
        $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_URL') . '/confirm-order', $requestParameters);
        dd($response->json()['data']);
        $order_id = $response->json()['data']['id'];
        // store locally        
        $order = new Orders();
        $order->commission = $commission;
        $order->status = "Processing";
        $order->customer_name = $client->name;
        $order->total_amount = $custom_price;
        $order->order_id = $order_id;
        $order->reseller_id = Auth::user()->id;
        $order->save();
        $data = [
            'message' => 'Order Stored Succsessfully',
        ];
        return response()->json($data);
    }
    public function requestAction(Request $request)
    {
        $data = [
            'message' => 'Request Sent Succsessfully',
        ];
        return response()->json($data);
    }

    public function getall()
    {
        $orders = Orders::where('reseller_id', Auth::user()->id)->get();
        $ordersArray = $orders->map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['order_date'] = Carbon::parse($itemArray['created_at'])->format('Y-m-d');
            $response = Http::withToken(Auth::user()->jwt_token)
                ->get(env('ADMIN_PORTAL_URL') . '/invoice-url' . '/' . $item['order_id']);
            $itemArray['file'] = $response->json();
            unset($itemArray['created_at']);
            return $itemArray;
        })->toArray();
        return response()->json($ordersArray);
    }
}
