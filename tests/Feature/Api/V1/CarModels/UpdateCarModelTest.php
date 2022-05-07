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
use App\Http\Requests\UpdateCarMadeRequest;
use Validator;

class UpdateCarModelTest extends TestCase
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
    public function test_update_car_model()
    {
        $this->withoutExceptionHandling();
        // $faker = \Faker\Factory::create();
       // $cats       = \App\Models\ProductCategory::factory()->count(5)->create();
        $car_made   = \App\Models\CarMade::factory()->create();
        $car_model  = \App\Models\CarModel::factory()->create();
        $carModel   = $car_model->id;
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'carmodel'      => $faker->word,
            'carmade_id'    => $car_made->id,
        ];

        $user       = \App\Models\User::find(1); //factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_model_edit', function ($user) {
            return true;
        });
        
        /*$reponse = $this->json('PUT', '/api/v1/car-mades/'.$carMade, $form_data, [
            'Accept' => 'application/json',
        ]);*/

        $reponse = $this->json('PUT', route('api.car-models.update', $carModel), $form_data, [
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
            ], // end data 
        ]) // end structure
                ->assertStatus(202);
        
    }
}
