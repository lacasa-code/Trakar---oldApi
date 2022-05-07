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

class DeleteCarMadeTest extends TestCase
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
    public function test_delete_car_made()
    {
        $this->withoutExceptionHandling();
       // $categories = \App\Models\ProductCategory::factory()->count(1)->create();
        $car_made   = \App\Models\CarMade::factory()->create();
        $carMade    = $car_made->id;

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('car_made_delete', function ($user) {
            return true;
        });

        $response = $this->json('delete', '/api/v1/car-mades/'.$car_made->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);
    }
}
