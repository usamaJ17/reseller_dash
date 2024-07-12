<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function getAll(){
        $response = Http::withToken(Auth::user()->jwt_token)
        ->get(env('ADMIN_PORTAL_URL').'/get-products');   
        $responseJson = $response->json();     
        return response($responseJson);
    }
    public function getAllCategory(){
        $response = Http::withToken(Auth::user()->jwt_token)
        ->get(env('ADMIN_PORTAL_URL').'/category/all');   
        $responseJson = $response->json();     
        return response($responseJson);
    }
    public function getProductByCategory($id){
        $response = Http::withToken(Auth::user()->jwt_token)
        ->get(env('ADMIN_PORTAL_URL').'/products-by-category'.'/'.$id);  
        $responseJson = $response->json();     
        return response($responseJson);
    }
    public function getDetails($id){
        $response = Http::withToken(Auth::user()->jwt_token)
        ->get(env('ADMIN_PORTAL_WEB').'/home/product-details'.'/'.$id);  
        $responseJson = $response->json(); 
        $rating = 0;
        $total = 0;
        foreach ($responseJson['product']['reviews'] as $item){
            $rating += $item['rating'];
            $total++;
        }
        if($total > 0){
            $rating = $rating/$total;
        }


        $attributes = $responseJson['attributes'];
        $selected_variants = $responseJson['product']['selected_variants'];
        $attribute_values = $responseJson['product']['attribute_values'];
        
        $product_attr_variations = array_map(function($attribute) use ($selected_variants, $attribute_values) {
            $attribute_id = $attribute['id'];
            $attribute['values'] = array_filter($attribute_values, function($value) use ($attribute_id, $selected_variants) {
                return $value['attribute_id'] == $attribute_id && in_array($value['id'], $selected_variants[$attribute_id]);
            });
            return $attribute;
        }, $attributes);
        $colors = [];
        $product_colors = $responseJson['product']['product_colors'];
        foreach ($product_colors as $item){
            $data = [
                'id' => $item['id'],
                'code' => $item['code'],
                'name' => $item['name'],
            ];
            $colors[] = $data;
        }
        $stock = $responseJson['product']['stock'];
        $variations = [];
        foreach ($stock as $item){
            $data = [
                "id" => $item['id'],
                "variant_ids" => $item['variant_ids'],
                "product_id" => $item['product_id'],
                "name" => $item['name'],
                "sku" => $item['sku'],
                "current_stock" => $item['current_stock'],
                "wholesale_price" => $item['price'],
                "suggested_retail_price" => $item['wholesale_price'],
                "image" => $item['stock_image'],
            ];
            $variations[] = $data;
        }
        $details = [
            "id" => $responseJson['product']['id'],
            "slug" => $responseJson['product']['slug'],
            "name" => $responseJson['product']['product_name'],
            "wholesale_price" => $responseJson['product']['price'],
            "suggested_retail_price" => $responseJson['product']['wholesale_price'],
            "special_discount" => $responseJson['product']['special_discount'],
            "special_discount_type" => $responseJson['product']['special_discount_type'],
            "special_discount_start" => $responseJson['product']['special_discount_start'],
            "special_discount_end" => $responseJson['product']['special_discount_end'],
            "short_description" => $responseJson['product']['short_description'],
            "long_description" => $responseJson['product']['language_product']['description'],
            "gallery" => $responseJson['product']['gallery'],
            "current_stock" => $responseJson['product']['current_stock'],
            "minimum_order_quantity" => $responseJson['product']['minimum_order_quantity'],
            "shipping_fee" => $responseJson['product']['shipping_fee'],
            "category_name" => $responseJson['product']['category_title'],
            "has_variant" => $responseJson['product']['has_variant'],
            "product_colors" => $colors,
            "attributes" => json_decode(json_encode($product_attr_variations)),
            "variations" => $variations,
            "rating" => $rating,
            "total_reviews" => $total,
        ];
        return response($details);
    }
}
