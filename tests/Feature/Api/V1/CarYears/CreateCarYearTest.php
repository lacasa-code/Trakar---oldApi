<?php

namespace Tests\Feature\Api\V1\CarYears;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\CarYear;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\AuthGates;
use Gate;

class CreateCarYearTest extends TestCase
{
    // use RefreshDatabase;
      // use DatabaseMigrations;
     // use  DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_car_made()
    {
        $this->withoutExceptionHandling();
        // $faker = \Faker\Factory::create();
       // $cats       = \App\Models\ProductCategory::factory()->count(5)->create();
        // $cat        = \App\Models\ProductCategory::factory()->create();
       // $car_year   = \App\Models\CarYear::factory()->create();
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'year'      => intval(date('Y')) - 1 ,
        ];
        // $car_made->toArray();

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_year_create', function ($user) {
            return true;
        });
        
        $reponse = $this->json('POST', route('api.car-years.store'), $form_data, [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "year",
                    "updated_at",
                    "created_at",
                    "id",
            ], // end data 
        ]) // end structure
                ->assertStatus(201);
        
    }
}
