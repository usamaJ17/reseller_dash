<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClientController;
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

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login'])->middleware('cros');

Route::middleware(['auth:sanctum','cros','add.token'])->group(function () {
  Route::get('products',[ProductController::class,'getAll']);
  Route::get('product_detail/{id}',[ProductController::class,'getDetails']);
  Route::get('product_by_category/{id}',[ProductController::class,'getProductByCategory']);
  Route::get('category',[ProductController::class,'getAllCategory']);
  Route::resource('client', ClientController::class);
  Route::post('logout',[AuthController::class,'logout']);
  Route::post('store_order',[OrderController::class,'store']);
});
