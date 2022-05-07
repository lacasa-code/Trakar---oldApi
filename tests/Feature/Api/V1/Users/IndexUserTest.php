<?php

namespace Tests\Feature\Api\V1\Users;

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

class IndexUserTest extends TestCase
{
    // use DatabaseMigrations;
   // use DatabaseTransactions;
    
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
    public function test_users()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

        $users   = \App\Models\User::factory()->count(3)->create();
        $user    = \App\Models\User::find(1); //()->create();
        $this->actingAs($user, 'api');
        Gate::define('user_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', route('api.users.index'), [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                       "id",
                        "name",
                        "email",
                        "email_verified_at",
                        "created_at",
                        "updated_at",
                        "deleted_at",
                        "added_by_id",
                        "roles" => [],        
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
