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

class ShowRoleTest extends TestCase
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
    public function test_show_role()
    {
        $this->withoutExceptionHandling();
        // $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $role    = \App\Models\Role::factory()->create();
        $roleid  = $role->id;
        $user    = \App\Models\User::find(1); // \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('role_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/roles/'.$roleid, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                    "id",
                    "title",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "added_by_id",
                    "permissions" => [],
                   // "deleted_at", 
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
