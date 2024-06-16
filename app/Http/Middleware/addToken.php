<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class addToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()){
            $user=Auth::user();
            if($user->jwt_token == "" || $user->jwt_token == null){
                $dc = Crypt::decrypt($user->jwt_password);
                $response = Http::post(env('ADMIN_PORTAL_URL').'/login', [
                    'email' => $user->email,
                    'password' => $dc,
                ]);   
                $responseJson = $response->json();             
                $user->jwt_token = $responseJson['data']['token'];
                $user->save();
            }
        }
        return $next($request);
    }
}
