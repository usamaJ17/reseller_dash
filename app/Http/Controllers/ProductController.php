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
        ->get(env('ADMIN_PORTAL_URL').'/product-details'.'/'.$id);   
        $responseJson = $response->json(); 
        dd($responseJson['data']['price']);
        return response($responseJson);
    }
}
