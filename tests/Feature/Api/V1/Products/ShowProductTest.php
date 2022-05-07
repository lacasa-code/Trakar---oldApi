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

class ShowProductTest extends TestCase
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
    public function test_show_product()
    {
        $this->withoutExceptionHandling();
        // $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $product   = \App\Models\Product::factory()->create();
        $productId = $product->id;

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('product_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/products/'.$productId, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
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
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
