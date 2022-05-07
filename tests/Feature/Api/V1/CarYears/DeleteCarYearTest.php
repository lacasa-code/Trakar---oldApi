<?php

namespace Tests\Feature\Api\V1\CarYears;

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

class DeleteCarYearTest extends TestCase
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
    public function test_delete_car_year()
    {
        $this->withoutExceptionHandling();
       // $categories = \App\Models\ProductCategory::factory()->count(1)->create();
        $car_year  = \App\Models\CarYear::factory()->create();
        $carYear   = $car_year->id;

        $user       = \App\Models\User::find(1); // ->create();
        $this->actingAs($user, 'api');
        Gate::define('car_year_delete', function ($user) {
            return true;
        });

        $response = $this->json('delete', '/api/v1/car-years/'.$car_year->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);
    }
}
