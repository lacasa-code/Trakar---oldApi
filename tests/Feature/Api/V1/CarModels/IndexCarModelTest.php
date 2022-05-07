<?php

namespace Tests\Feature\Api\V1\CarModels;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\CarMade;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\AuthGates;
use Gate;

class IndexCarModelTest extends TestCase
{
    // use DatabaseMigrations;
   // use DatabaseTransactions;
    
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
    public function test_car_models()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

        $car_mades   = \App\Models\CarMade::factory()->count(3)->create();
        $car_model   = \App\Models\CarModel::factory()->create();
        $user        = \App\Models\User::factory()->create();

        $this->actingAs($user, 'api');
        Gate::define('car_model_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', '/api/v1/car-models', [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "carmodel",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "carmade_id",
                    "carmade" => [],
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
