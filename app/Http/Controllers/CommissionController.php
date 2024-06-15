<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CommissionController extends Controller
{
    public function getall(){
        $response = Http::withToken(Auth::user()->jwt_token)
        ->get(env('ADMIN_PORTAL_URL').'/category/all');   
        $responseJson = $response->json();     
        return response($responseJson);
    }
}
