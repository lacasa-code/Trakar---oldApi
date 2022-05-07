<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        
    }

    public function render($request, Throwable $exception)
    {

        /*if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return response()->json(['errors' => 'csrf token mismatch'], 400);
        }*/

        if ($exception instanceof \Propaganistas\LaravelPhone\Exceptions\NumberParseException) {
           // return response()->json(['errors' => __('site_messages.number_mismatch_with_country')], 400);
            return response()->json(['errors' => 'Number does not match the provided country.'], 400);
        }

        if ($exception instanceof \libphonenumber\NumberParseException) {
           // return response()->json(['errors' => __('site_messages.number_mismatch_with_country')], 400);
            return response()->json(['errors' => 'Number Format does not match the provided country.'], 400);
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json(['errors' => 'sorry, you are not authorized access this page'], 403);
        }

         if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json(['errors' => 'sorry, make sure both your url and method are true'], 403);
        }

        if ($exception instanceof \Illuminate\Session\HttpException) {
            return response()->json(['errors' => '555'], 400);
        }

        return parent::render($request, $exception);
    }

    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception) 
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => 'login and try again later'], 401);
        }

        return redirect()->guest('login');
    }

}
