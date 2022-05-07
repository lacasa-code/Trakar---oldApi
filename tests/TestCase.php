<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
   // use DatabaseMigrations;

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
