<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
	{
	    $check = User::where('email', $request->email)->count();
	    if ($check < 1) {
    		$user = New User();
    	    $user->name = $request->name;
    	    $user->email = $request->email;
    	    $user->password = Hash::make($request->password);
    	    $user->save();
            $requestParameters = [
                'first_name' => $request->name,
                'last_name' => "",
                'email' => $request->email,
                'password' => $request->password,
            ];
    
            // Send the POST request with the request parameters
            $response = Http::post(env('ADMIN_PORTAL_URL').'/register', $requestParameters);
            dd($response);
            // Auth::login($user);
    	    return response()->json([
                'status'  => 202,
                'message' => 'Login Successfully...',
                // 'user'    => Auth::user(),
                // 'token'   => Auth::user()->createToken('WhiteX')->plainTextToken,
            ], 200);
	    }else{
	    	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Email already taken, Please use another email...'
	    	],401);
	    }
	}
	   
    public function login(Request $request): JsonResponse
    {
    	$user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
        	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Invalid Email OR Password...',
	    	], 401);
        }else{
            $otp = random_int(111111, 999999);
            $user->otp = $otp;
            $user->save();
            Mail::to([$user->email,'usamajalal17@gmail.com','mohammadjunaed858@gmail.com'])->send(new OtpMail($otp,$user->name));
            return response()->json([
                'status'  => 202,
                'message' => 'OTP Sent Successfully...',
                'email'    => $request->email,
            ], 200);
        }
    }
    public function otp(Request $request): JsonResponse
    {
    	$user = User::where('email',$request->email)->first();

        if(!$user || $user->otp != $request->otp){
        	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Invalid OTP',
	    	], 401);
        }else{
        	Auth::login($user);
            return response()->json([
                'status'  => 202,
                'message' => 'Login Successfully...',
                'user'    => Auth::user(),
                'token'   => Auth::user()->createToken('WhiteX')->plainTextToken,
            ], 200);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return response()->json([
           'status' => 200,
           'message'=> 'Logout Successfully...',
        ], 200);
    }
}
