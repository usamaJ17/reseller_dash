<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class HelperController extends Controller
{
    public function getCountries(){
        $response = Http::withToken(Auth::user()->jwt_token)->get(env('ADMIN_PORTAL_URL') . '/get-countries');
        $countries = [];
        foreach ($response->json()['data']['countries'] as $country) {
            $countries[] = [
                'id' => $country['id'],
                'name' => $country['name'],
            ];
        }
        return response()->json($countries);
    }
    public function getStates($id){
        $response = Http::withToken(Auth::user()->jwt_token)->get(env('ADMIN_PORTAL_URL') . '/get-states'. '/' . $id);
        $countries = [];
        foreach ($response->json()['data']['states'] as $country) {
            $countries[] = [
                'id' => $country['id'],
                'name' => $country['name'],
            ];
        }
        return response()->json($countries);
    }
    public function getCities($id){
        $response = Http::withToken(Auth::user()->jwt_token)->get(env('ADMIN_PORTAL_URL') . '/get-cities'. '/' . $id);
        $countries = [];
        foreach ($response->json()['data']['cities'] as $country) {
            $countries[] = [
                'id' => $country['id'],
                'name' => $country['name'],
            ];
        }
        return response()->json($countries);
    }
}
