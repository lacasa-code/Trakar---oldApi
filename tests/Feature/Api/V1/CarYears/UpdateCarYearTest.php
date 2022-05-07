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

class UpdateCarYearTest extends TestCase
{
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
    public function test_update_car_year()
    {
        $this->withoutExceptionHandling();

        $car_year   = \App\Models\CarYear::factory()->create();
        $carYear    = $car_year->id;

        $faker      = \Faker\Factory::create();
        $form_data  = [
            'year'          => intval(date('Y')) - 2 ,
        ];

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_year_edit', function ($user) {
            return true;
        });
        
        /*$reponse = $this->json('PUT', '/api/v1/car-mades/'.$carMade, $form_data, [
            'Accept' => 'application/json',
        ]);*/

        $reponse = $this->json('PUT', route('api.car-years.update', $carYear), $form_data, [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                "id",
                "year",
                "created_at",
                "updated_at",
                "deleted_at",
            ], // end data 
        ]) // end structure
                ->assertStatus(202);
        
    }
}
