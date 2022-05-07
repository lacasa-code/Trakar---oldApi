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

class DeleteCarModelTest extends TestCase
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
    public function test_delete_car_model()
    {
        $this->withoutExceptionHandling();
       // $categories = \App\Models\ProductCategory::factory()->count(1)->create();
        $car_model  = \App\Models\CarModel::factory()->create();
        $carModel   = $car_model->id;

        $user       = \App\Models\User::find(1); // ->create();
        $this->actingAs($user, 'api');
        Gate::define('car_model_delete', function ($user) {
            return true;
        });

        $response = $this->json('delete', '/api/v1/car-models/'.$car_model->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);
    }
}
