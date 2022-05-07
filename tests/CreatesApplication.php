<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        //Load .env.testing environment
      //  $app->loadEnvironmentFrom('.env.testing');

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

   /* public function setUp(): void 
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
    }
 
    public function tearDown(): void
    {
        Artisan::call('migrate:reset');
        parent::tearDown();
    }*/
}
