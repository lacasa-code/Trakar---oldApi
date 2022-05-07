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

class CreateCarModelTest extends TestCase
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
    public function test_create_car_model()
    {
        $this->withoutExceptionHandling();
        // $faker = \Faker\Factory::create();

        $car_made   = \App\Models\CarMade::factory()->create();
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'carmade_id'      => $car_made->id,
            'carmodel'        => $faker->word,
        ];
        // $car_made->toArray();

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_model_create', function ($user) {
            return true;
        });
        
        $reponse = $this->json('POST', route('api.car-models.store'), $form_data, [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "carmodel",
                    "carmade_id",
                    "updated_at",
                    "created_at",
                    "id",
            ], // end data 
        ]) // end structure
                ->assertStatus(201);
        
    }
}
