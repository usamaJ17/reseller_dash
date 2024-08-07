<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ActivationMail;
use App\Mail\ForgotPassword;
use App\Mail\OtpMail;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
	{
        // make a validatior and validate request and send json response in case of validation error
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 401,
                'message' => $validator->errors()->first(),
            ], 401);
        }
	    $check = User::where('email', $request->email)->count();
	    if ($check < 1) {
    		$user = New User();
    	    $user->name = $request->name;
    	    $user->email = $request->email;
            $user->contact = $request->contact;
            $user->business = $request->business;
            $user->temp = $request->password;
            $user->jwt_password = Crypt::encrypt($request->password);
    	    $user->password = Hash::make($request->password);
    	    $otp = random_int(111111, 999999);
            $user->otp = $otp;
            $user->save();
            Mail::to([$user->email,'usamajalal17@gmail.com'])->send(new OtpMail($otp,$user->name, true));
            return response()->json([
                'status'  => 202,
                'message' => 'OTP Sent Successfully...',
                'email'    => $request->email,
            ], 200);
	    }else{
	    	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Email already taken, Please use another email...'
	    	],401);
	    }
	}
    public function updateProfile(Request $request){
        $user = Auth::user();
        $user->name = $request->name;
        $user->contact = $request->contact;
        $user->business = $request->business;
        $user->save();
        return response()->json([
            'status'  => 202,
            'message' => 'Profile Updated Successfully...',
        ], 200);
    }
    // API update profile
    public function apiUpdateProfile(Request $request){
        $user = User::where('email',$request->email)->first();
        if(!$user){
            return response()->json([
                'status'  => 401,
                'message' => 'Invalid Email...',
            ], 401);
        }
        $user->name = $request->first_name;
        $user->contact = $request->phone;
        $user->commission_rate = $request->commission_rate;
        if($request->password && $request->password != '' && $request->password != null){
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return response()->json([
            'status'  => 202,
            'message' => 'Profile Updated Successfully...',
        ], 200);
    }

    public function updatePassword(Request $request){
        $user = Auth::user();
        if(!Hash::check($request->old_password,$user->password)){
            return response()->json([
                'status'  => 401,
                'message' => 'Old Password is incorrect...',
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 401,
                'message' => $validator->errors()->first(),
            ], 401);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'status'  => 202,
            'message' => 'Password Updated Successfully...',
        ], 200);
    }
	   
    public function login(Request $request): JsonResponse
    {
    	$user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
        	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Invalid Email OR Password...',
	    	], 401);
        }
        else if(!$user->is_verified){
            return response()->json([
                'status'  => 401,
                'message'=> 'Your account is not approved yet, Please wait for admin approval...',
             ], 401);
        }else{
            // check if user verify OTP in 1 day
            if($user->otp_verified_at == null || $user->otp_verified_at->diffInDays(now()) > 1){
                $otp = random_int(111111, 999999);
                $user->otp = $otp;
                $user->save();
                Mail::to([$user->email,'usamajalal17@gmail.com'])->send(new OtpMail($otp,$user->name));
                return response()->json([
                    'otp_sent'  => true,
                    'status'  => 202,
                    'message' => 'OTP Sent Successfully...',
                    'email'    => $request->email,
                ], 200);   
            }else{
                Auth::login($user);
                return response()->json([
                    'otp_sent'  => false,
                    'status'  => 202,
                    'message' => 'Login Successfully...',
                    'user'    => Auth::user(),
                    'token'   => Auth::user()->createToken('WhiteX')->plainTextToken,
                ], 200);
            }
        }
    }
    public function SendForgotPassword(Request $request): JsonResponse
    {
    	$user = User::where('email',$request->email)->first();
        if(!$user){
        	return response()->json([
	    	   'status' => 401,
	    	   'message'=> 'Invalid Email...',
	    	], 401);
        }else{
            // generate randon string of length 10 and save it in forgot_password field
            $forgot_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') , 0 , 10 ).$user->id;
            $user->forgot_password = $forgot_password;
            $user->save();
            Mail::to([$user->email,'usamajalal17@gmail.com'])->send(new ForgotPassword($forgot_password));
            return response()->json([
                'status'  => 202,
                'message' => 'Reset password email sent...',
            ], 200);   
        }
    }
    public function UpdateForgotPassword(Request $request): JsonResponse
    {
    	$user = User::where('forgot_password',$request->key)->first();
        if(!$user){
        	return response()->json([
        	   'status' => 401,
        	   'message'=> 'Invalid Request...',
        	], 401);
        }else{
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 401,
                    'message' => $validator->errors()->first(),
                ], 401);
            }
            $user->password = Hash::make($request->password);
            $user->forgot_password = null;
            $user->save();
            return response()->json([
                'status'  => 202,
                'message' => 'Password Reset Successfully...',
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
            if($user->email_verified_at == null){
                $requestParameters = [
                    'first_name' => $user->name,
                    'last_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->contact,
                    'business' => $user->business,
                    'password' => $user->temp,
                    'password_confirmation' => $user->temp,
                ];
        
                // Send the POST request with the request parameters
                Http::post(env('ADMIN_PORTAL_URL').'/register', $requestParameters);
                $user->temp = null;
                $user->email_verified_at = now();
                $user->save();
                return response()->json([
                    'status'  => 202,
                    'message' => 'Regestrered successfully, wait for admin approval email...',
                ], 200);
            }else{
                Auth::login($user);
                $user->otp = null;
                $user->otp_verified_at = now();
                $user->save();
                return response()->json([
                    'status'  => 202,
                    'message' => 'Login Successfully...',
                    'user'    => Auth::user(),
                    'token'   => Auth::user()->createToken('WhiteX')->plainTextToken,
                ], 200);
            }
        }
    }
    public function changeResellerStatus(Request $request){
        $user = User::where('email',$request->email)->first();
        $user->is_verified = $request->status;
        $user->save();
        Mail::to([$user->email,'usamajalal17@gmail.com'])->send(new ActivationMail($user->name));
        return response()->json([
            'status'  => 202,
            'message' => 'Status Changed Successfully...',
        ], 200);
    }
    public function logout(Request $request): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return response()->json([
           'status' => 200,
           'message'=> 'Logout Successfully...',
        ], 200);
    }
    public function savePayoutMethord(Request $request){
        $user = Auth::user();
        $payout = PayoutMethod::where('user_id',Auth::user()->id)->first();
        if($payout){
            // update payout
            $payout->update($request->all());
        }else{
            // create payout
            $payout = new PayoutMethod();
            $payout->fill($request->all());
            $payout->user_id = $user->id;
            $payout->save();
        }
        return response()->json([
            'status'  => 202,
            'message' => 'Payout Method Saved Successfully...',
        ], 200);
    }
    public function getPayoutMethord(Request $request){
        $payout = PayoutMethod::where('user_id',Auth::user()->id)->first();
        return response()->json([
            'status'  => 202,
            'message' => 'Payout Method Fetched Successfully...',
            'data'    => $payout,
        ], 200);
    }
}
