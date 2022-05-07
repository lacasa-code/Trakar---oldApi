<?php

namespace Tests\Feature\Api\V1\Vendors;

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

class IndexVendorTest extends TestCase
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
    public function test_vendors()
    {
        // $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        // $this->withoutMiddleware(AuthGates::class);
        $this->withoutExceptionHandling();

       // $cats       = \App\Models\ProductCategory::factory()->count(3)->create();
        $vendors   = \App\Models\AddVendor::factory()->create();
        $user       = \App\Models\User::find(1); 
        $this->actingAs($user, 'api');
        Gate::define('add_vendor_access', function ($user) {
            return true;
        });

        $reponse = $this->json('GET', route('api.add-vendors.index'), [
            'Accept'        => 'application/json',
            //'Authorization' => 'Bearer '. $auth_token,
        ]);
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                        "id",
                        "vendor_name",
                        "email",
                        "type",
                        "serial",
                        "created_at",
                        "updated_at",
                        "deleted_at",
                        "userid_id",
                        "images",
                        "userid" => [],
                        "media"  => [],      
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);
    }
}
