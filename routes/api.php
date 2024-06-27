<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['cros'])->group(function () {
  Route::post('register',[AuthController::class,'register']);
  Route::post('verify_register_otp',[AuthController::class,'registerOtp']);
  Route::post('login',[AuthController::class,'login']);
  Route::post('verify_otp',[AuthController::class,'otp']);
  Route::get('all_payouts',[CommissionController::class,'AdminPayouts']);
});
Route::post('send_forgot_password',[AuthController::class,'SendForgotPassword'])->middleware('cros');
Route::post('update_forgot_password',[AuthController::class,'UpdateForgotPassword'])->middleware('cros');


Route::middleware(['auth:sanctum','cros','add.token'])->group(function () {
  Route::get('products',[ProductController::class,'getAll']);
  Route::get('product_detail/{id}',[ProductController::class,'getDetails']);
  Route::get('product_by_category/{id}',[ProductController::class,'getProductByCategory']);
  Route::get('category',[ProductController::class,'getAllCategory']);
  Route::resource('client', ClientController::class);
  Route::post('logout',[AuthController::class,'logout']);
  Route::post('store_order',[OrderController::class,'store']);
  Route::get('orders',[OrderController::class,'getall']);
  Route::post('request_action',[OrderController::class,'requestAction']);
  Route::get('commissions',[CommissionController::class,'getall']);
  Route::get('payouts',[CommissionController::class,'getallPayout']);
  Route::post('request_payouts',[CommissionController::class,'requestPayout']);
});
Route::post('change_reseller_status',[AuthController::class,'changeResellerStatus']);
