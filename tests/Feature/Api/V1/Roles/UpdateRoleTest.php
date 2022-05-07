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

class UpdateRoleTest extends TestCase
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
    public function test_update_role()
    {
        $this->withoutExceptionHandling();

        $permission = \App\Models\Permission::factory()->create();
        $role       = \App\Models\Role::factory()->create();

        $faker      = \Faker\Factory::create();
        $form_data  = [
            'title'       => $faker->word,
            'added_by_id' => 1,
            'permissions' => '$permission->id',
        ];

        $user       = \App\Models\User::find(1);
        $this->actingAs($user, 'api');
        Gate::define('role_edit', function ($user) {
            return true;
        });
        
        $reponse = $this->json('PUT', route('api.roles.update', $role->id), $form_data, [
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
            ], // end data 
        ]) // end structure
                ->assertStatus(202);
        
    }
}
