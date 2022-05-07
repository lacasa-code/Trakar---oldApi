<?php

namespace Tests\Feature\Api\V1\Stores;

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

class ShowStoreTest extends TestCase
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
    public function test_show_store()
    {
        $this->withoutExceptionHandling();
        // $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $vendors = \App\Models\AddVendor::factory()->count(5)->create();
        $store   = \App\Models\Store::factory()->create();
        $storeId = $store->id;

        $user    = \App\Models\User::find(1);
        $this->actingAs($user, 'api');
        Gate::define('stores_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/stores/'.$storeId, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                "id",
                "name",
                "address",
                "lat",
                "long",
                "vendor_id",
                "moderator_name",
                "moderator_phone",
                "moderator_alt_phone",
                "status",
                "created_at",
                "updated_at",
                "deleted_at",
                "vendor_name", // should be resolved
                "vendor"  => [], 
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
