<?php

namespace Tests\Feature\Api\V1\Products;

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

class IndexProductTest extends TestCase
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
    public function test_products()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

       // $cats       = \App\Models\ProductCategory::factory()->count(3)->create();
        $products   = \App\Models\Product::factory()->create();

        $user       = \App\Models\User::find(1); //factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('product_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', route('api.products.index'), [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "name",
                    "description",
                    "price",
                    "discount",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "car_made_id",
                    "car_model_id",
                    "year_id",
                    "part_category_id",
                    "vendor_id",
                    "store_id",
                    "quantity",
                    "serial_number",
                    "photo"         => [],
                    "tags"          => [],
                    "categories"    => [],
                    "car_made"      => [],
                    "car_model"     => [],
                    "year"          => [],
                    "part_category" => [],
                    "store"         => [],
                    "media"         => [],      
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
