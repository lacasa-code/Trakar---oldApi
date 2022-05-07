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

class ShowCarModelTest extends TestCase
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
    public function test_show_car_model()
    {
        $this->withoutExceptionHandling();

        $car_mades  = \App\Models\CarMade::factory()->count(3)->create();
        $car_model  = \App\Models\CarModel::factory()->create();
        $carModel   = $car_model->id;

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_model_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/car-models/'.$carModel, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "id",
                    "carmodel",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "carmade_id",
                    "carmade" => [], 
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
