<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwitchLanguageApiMiddleware
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
        if (!$request->header('Accept-Language') || $request->header('Accept-Language') == '')
        {
          app()->setLocale('ar');
        }
        if($request->header('Accept-Language') && $request->header('Accept-Language') != '')
        {
            $language = $request->header('Accept-Language');
           app()->setLocale($language);
        }
        return $next($request);
    }
}
