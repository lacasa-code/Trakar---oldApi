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

class ShowUserTest extends TestCase
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
    public function test_show_user()
    {
        $this->withoutExceptionHandling();

        $member     = \App\Models\User::factory()->create();
        $memberId   = $member->id;

        $user       = \App\Models\User::find(1); //()->create();
        $this->actingAs($user, 'api');
        Gate::define('user_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/users/'.$memberId, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "id",
                    "name",
                    "email",
                    "email_verified_at",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "added_by_id",
                    "roles" => [],      
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
