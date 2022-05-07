<?php

namespace Tests\Feature\Api\V1\Roles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\CarYear;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\AuthGates;
use Gate;

class CreateRoleTest extends TestCase
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
    public function test_create_role()
    {
        $this->withoutExceptionHandling();
        // $faker = \Faker\Factory::create();
        $permission = \App\Models\Permission::factory()->create();
       // $car_year   = \App\Models\CarYear::factory()->create();
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'title'       => $faker->word,
            'added_by_id' => $faker->randomDigit,
            'permissions' => '$permission->id',
        ];
        // $car_made->toArray();

        $user       = \App\Models\User::factory()->create();
        $user->roles()->sync(1);
        $this->actingAs($user, 'api');
        Gate::define('role_create', function ($user) {
            return true;
        });
        
        $reponse = $this->json('POST', route('api.roles.store'), $form_data, [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "title",
                    "added_by_id",
                    "updated_at",
                    "created_at",
                    "id",
            ], // end data 
        ]) // end structure
                ->assertStatus(201);
        
    }
}
