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

class IndexPermissionTest extends TestCase
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
    public function test_permissions()
    {
        ///$this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        $this->withoutExceptionHandling();

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('permission_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', '/api/v1/permissions', ['Accept' => 'application/json']);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "title",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
