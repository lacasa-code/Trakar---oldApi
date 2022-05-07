<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
          Schema::defaultStringLength(191);
        
        // This line force the http to https in the pagination url.

        if(env('APP_ENV') !== 'local')
        {
            $this->app['request']->server->set('HTTPS','on');
        }
        else{
            $this->app['request']->server->set('HTTPS','off');
        } 
    }
}
