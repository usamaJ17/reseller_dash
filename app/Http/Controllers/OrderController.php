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
        $custom_price = 0;
        $commission = 0;
        $pro_det = [];
        foreach ($request->products as $key => $item) {
            $data = [
                "id" => $item['id'],
                "slug" => $item['id'],
                "quantity" => $item['quantity'],
                'price' => $item['custom_price'],
            ];
            $custom_price = $custom_price + ($item['quantity'] * $item['custom_price']);
            $commission = $commission + ($item['quantity'] * (($item['custom_price']+40) - $item['custom_price']));
            $pro_det[] = $data;
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
            }
            $trx_id = $response_1->json()['carts'][0]['trx_id'];
            $price = $price + ($response_1->json()['carts'][0]['quantity'] * $response_1->json()['carts'][0]['price']);
            if($valid_key >= sizeof($response_1->json()['carts'])){
                $valid_key--;
                $temp_data = [
                    "id" => $response_1->json()['carts'][$valid_key]['id'],
                    "quantity" => $response_1->json()['carts'][$valid_key]['quantity'],
                ];
            }else{
                $temp_data = [
                    "id" => $response_1->json()['carts'][$valid_key]['id'],
                    "quantity" => $response_1->json()['carts'][$valid_key]['quantity'],
                ];
            }
            $quantity[] = $temp_data;
            $valid_key++;
        }
        if(!empty($cart_errors)){
            $requestParameters = [
                'user_id' => Auth::user()->portal_id,
            ];     
            $response = Http::post(env('ADMIN_PORTAL_URL') . '/delete_cart_api', $requestParameters);       
            $data = [
                'message' => 'Error in Cart',
                'errors' => $cart_errors,
            ];
            return response()->json($data, 500);
        }
        $client = Client::find($request->clientID);
        
        $delivery_amount_total = 0;
        if(isset($request->shipping)){
            if($request->shipping == 'Inside Dhaka'){
                $delivery_amount_total = 120;
            }else{
                $delivery_amount_total = 150;
            }
        }
        $order_note = "";
        if(isset($request->order_note)){
            $order_note = $request->order_note;
        }
        $requestParameters = [
            "payment_type" => 0,
            "sub_total" => $price,
            "reseller_portal_user_id" => Auth::user()->portal_id,
            "is_reseller_order" => 1,
            "discount_offer" => 0,
            "shipping_tax" => 0,
            "tax" => 0,
            'delivery_amount_total'=> $delivery_amount_total,
            'order_note'=> $order_note,
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
        $order_id = $response->json()['order_id'][0];
        $requestParameters = [
            "payment_type" => "cash_on_delivery",
            "trx_id" => $trx_id,
            "is_buy_now" => 0,
            "is_reseller_api" => 1,
            "id" => null,
            "file" => null,
        ];
        $response = Http::withToken(Auth::user()->jwt_token)->post(env('ADMIN_PORTAL_WEB') . '/user/complete-order?code=', $requestParameters);
        $order = new Orders();
        $order->commission = $commission;
        $order->status = "Processing";
        $order->pro_det = json_encode($pro_det);
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
            $pro_det = $item->pro_det;
            foreach ($pro_det as $key => $value) {
                $img_response = Http::get(env('ADMIN_PORTAL_URL_OTHER') . '/product_thumbnail' . '/' . $value->id);
                $pro_det[$key]->image_url = $img_response->json();
            }
            $itemArray = $item->toArray();
            $itemArray['pro_det'] = $pro_det;
            $itemArray['order_date'] = Carbon::parse($itemArray['created_at'])->format('Y-m-d');
            $response = Http::withToken(Auth::user()->jwt_token)
                ->get(env('ADMIN_PORTAL_URL') . '/invoice-url' . '/' . $item['order_id']);
            $itemArray['file'] = $response->json()['pdf_url'];
            unset($itemArray['created_at']);
            unset($itemArray['updated_at']);
            return $itemArray;
        })->toArray();
        return response()->json($ordersArray);
    }
}
