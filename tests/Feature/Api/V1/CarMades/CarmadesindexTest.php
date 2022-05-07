<?php

namespace Tests\Feature\Api\V1\CarMades;

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

class CarmadesindexTest extends TestCase
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
    public function test_carmades()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

        $cats       = \App\Models\ProductCategory::factory()->count(3)->create();
        $car_made   = \App\Models\CarMade::factory()->create();
        $user       = \App\Models\User::factory()->create();

        $this->actingAs($user, 'api');
        Gate::define('car_made_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', '/api/v1/car-mades', [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "car_made",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "categoryid_id",
                    "catName",
                    "categoryid",
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
