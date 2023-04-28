<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {



      if(!empty(trim($request->bearerToken())))
      {
        $is_exists = User::where('id' , Auth::guard('api')->id())->exists();
        if($is_exists){
              $request->headers->set('Accept', 'application/json');
                 return $next($request);
                }

           }
            return response()->json([
            'status' => 'error',
            'message' => 'Invalid Token',
            'code' => 401,
            ]);


    }
}
