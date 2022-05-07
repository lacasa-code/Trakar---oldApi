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
use App\Http\Requests\UpdateCarMadeRequest;
use Validator;

class UpdateCarMadeTest extends TestCase
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
    public function test_update_part_category()
    {
        $this->withoutExceptionHandling();
        
       // $cats       = \App\Models\ProductCategory::factory()->count(5)->create();
        $car_made   = \App\Models\CarMade::factory()->create();
        $cat        = \App\Models\ProductCategory::factory()->create();
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'car_made'      => $faker->word,
            'categoryid_id' => $cat->id,
        ];
    
        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_made_edit', function ($user) {
            return true;
        });
        
        /*$reponse = $this->json('PUT', '/api/v1/car-mades/'.$carMade, $form_data, [
            'Accept' => 'application/json',
        ]);*/

        $reponse = $this->json('PUT', route('api.car-mades.update', $car_made->id), $form_data, [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "id",
                    "car_made",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "categoryid_id",
            ], // end data 
        ]) // end structure
                ->assertStatus(202);
        
    }
}
