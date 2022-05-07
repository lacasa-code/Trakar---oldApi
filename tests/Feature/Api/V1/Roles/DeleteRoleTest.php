<?php

namespace Tests\Feature\Api\V1\Roles;

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

class DeleteRoleTest extends TestCase
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
    public function test_delete_role()
    {
        $this->withoutExceptionHandling();
        $user       = \App\Models\User::find(1); // ->create();
        $role       = \App\Models\Role::factory()->create();

        $this->actingAs($user, 'api');
        Gate::define('role_delete', function ($user) {
            return true;
        });

        $response = $this->json('delete', '/api/v1/roles/'.$role->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);
    }
}
