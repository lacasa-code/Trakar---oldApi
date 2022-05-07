<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class ApproveVendorApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('Vendor', $user_roles)) 
      {
        return response()->json([
            'status_code' => 400, 
            'errors' => 'vendor not approved yet'], 400);
      }
        return $next($request);
    }
}
