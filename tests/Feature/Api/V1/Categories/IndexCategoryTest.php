<?php

namespace Tests\Feature\Api\V1\Categories;

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

class IndexCategoryTest extends TestCase
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
    public function test_categories()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

       // $cats       = \App\Models\ProductCategory::factory()->count(3)->create();
        $categories   = \App\Models\ProductCategory::factory()->create();

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('product_category_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', route('api.product-categories.index'), [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "name",
                    "description",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "photo",
                    "media",       
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
