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

class ShowCarMadeTest extends TestCase
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
    public function test_show_car_made()
    {
        $this->withoutExceptionHandling();
        $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $car_made   = \App\Models\CarMade::factory()->create();
        $carMade    = $car_made->id;

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_made_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/car-mades/'.$carMade, [], [
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
                    "categoryid" => [
                      /*  "id",
                        "name",
                        "description",
                        "created_at",
                        "updated_at",
                        "deleted_at",
                        "photo",
                        "media",*/
                        ] // end categoryid array 
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
