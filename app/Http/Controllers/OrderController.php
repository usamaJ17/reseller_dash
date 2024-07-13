<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $trx_id = null;
        $response_1 = null;
        $price = 0;
        $quantity = [];
        $cart_errors = [];
        $valid_key = 0;
        foreach ($request->products as $key => $item) {
            $requestParameters = [
                    "custom_price" => $item['custom_price'],
                    "rating" => 0,
                    "title" => "",
                    "comment" => "",
                    "image" => "",
                    "reseller_portal_user_id" => Auth::user()->portal_id,
                    "product_id" => "",
                    "review_id" => "",
                    "reply" => "",
                    "id" => $item['id'],
                    "color_id" => $item['color_id'],
                    "quantity" => $item['quantity'],
                    "attribute_values" => $item['attribute_values'],
                    "variants_name" => $item['variants_name'],
                    "variants_ids" => $item['variants_ids'],
                    "trx_id" => $trx_id,
                    "image_text" => "Choose File",
                    "is_buy_now" => 0,
            ];
            // Send the POST request with the request parameters
            $response_1 = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_WEB') . '/user/addToCart', $requestParameters);
            if (array_key_exists('error', $response_1->json())) {
                $err_arr = [
                    'id'=> $item['id'],
                    'variants_ids' => $item['variants_ids'],
                    'error' =>$response_1->json()['error'],
                ];
                $cart_errors[] = $err_arr;
                continue;
            }else{
                $err_arr = [
                    'id'=> $item['id'],
                    'variants_ids' => $item['variants_ids'],
                    "trx_id" => $response_1->json()['carts'][0]['trx_id'],
                    'portal_id' => Auth::user()->portal_id,
                ];
                $cart_errors[] = $err_arr;
            }
            $trx_id = $response_1->json()['carts'][0]['trx_id'];
            $price = $price + ($response_1->json()['carts'][0]['quantity'] * $response_1->json()['carts'][0]['price']);
            $temp_data = [
                "id" => $response_1->json()['carts'][$valid_key]['id'],
                "quantity" => $response_1->json()['carts'][$valid_key]['quantity'],
            ];
            $quantity[] = $temp_data;
            $valid_key++;
        }
        if(!empty($cart_errors)){
            $data = [
                'message' => 'Error in Cart',
                'errors' => $cart_errors,
            ];
            $requestParameters = [
                'user_id' => Auth::user()->email,
            ];
            $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_URL') . '/delete_cart_api', $requestParameters);
            return response()->json($data, 500);
        }
        $client = Client::find($request->clientID);
        
        $custom_price = 0;
        $commission = 0;
        $requestParameters = [
            "payment_type" => 0,
            "sub_total" => $price,
            "reseller_portal_user_id" => Auth::user()->portal_id,
            "is_reseller_order" => 1,
            "discount_offer" => 0,
            "shipping_tax" => 0,
            "tax" => 0,
            "coupon_discount" => 0,
            "total" => $price,
            'trx_id' => $trx_id,
            "quantity" => $quantity,
            "coupon_code"=> "",
            "coupon"=> [],
            "checkout_method"=> 2,
            "buy_now" => 0,
            'shipping_address' => [
                "name" => $client->name,
                "email" => $client->email,
                "phone_no" => $client->contact,
                "address" => $client->address,
                "address_ids" => [
                    "country_id" => $client->country_id,
                    "state_id" => $client->state_id,
                    "city_id" => $client->state_id,
                ],
                "country" => $client->country_name,
                "state" => $client->state_name,
                "city" => $client->city_name,
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
                    "city_id" => $client->state_id,
                ],
                "country" => $client->country_name,
                "state" => $client->state_name,
                "city" => $client->city_name,
                "postal_code" => $client->postal_code,
            ],
        ];
        $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_WEB') . '/user/confirm-order', $requestParameters);
        if (!$response->json()['success']) {
            return response()->json($response->json(), 500);
        }
        $requestParameters = [
            "payment_type" => "cash_on_delivery",
            "trx_id" => $trx_id,
            "is_buy_now" => 0,
            "is_reseller_api" => 1,
            "id" => null,
            "file" => null,
        ];
        $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_WEB') . '/user/complete-order?code=', $requestParameters);
        return response($response->body());   
        $order = new Orders();
        $order->commission = $commission;
        $order->status = "Processing";
        $order->customer_name = $client->name;
        $order->total_amount = $custom_price;
        // $order->order_id = $order_id;
        $order->reseller_id = Auth::user()->id;
        $order->save();
        $data = [
            'message' => 'Order Stored Succsessfully',
        ];
        return response()->json($data);
    }
    public function requestAction(Request $request)
    {
        // $requestParameters = [
        //     'order_id' => ,
        // ];
        // $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_URL') . '/confirm-order', $requestParameters);
        return response()->json(true);
    }

    public function getall()
    {
        $orders = Orders::where('reseller_id', Auth::user()->id)->get();
        $ordersArray = $orders->map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['order_date'] = Carbon::parse($itemArray['created_at'])->format('Y-m-d');
            // $response = Http::withToken(Auth::user()->jwt_token)
            //     ->get(env('ADMIN_PORTAL_URL') . '/invoice-url' . '/' . $item['order_id']);
            // $itemArray['file'] = $response->json()['pdf_url'];
            // unset($itemArray['created_at']);
            return $itemArray;
        })->toArray();
        return response()->json($ordersArray);
    }
}
