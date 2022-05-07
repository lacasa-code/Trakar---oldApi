<?php

namespace Tests\Feature\Api\V1\Permissions;

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

class CreatePermissionTest extends TestCase
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
    public function test_create_permission()
    {
        $this->withoutExceptionHandling();
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'title'   => $faker->word,
        ];

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('permission_create', function ($user) {
            return true;
        });
        
        $reponse = $this->json('POST', route('api.permissions.store'), $form_data, [
            'Accept' => 'application/json',
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                    "title",
                    "updated_at",
                    "created_at",
                    "id",
            ], // end data 
        ]) // end structure
                ->assertStatus(201);
        
    }
}
