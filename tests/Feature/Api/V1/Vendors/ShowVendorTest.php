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

class ShowVendorTest extends TestCase
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
    public function test_show_vendor()
    {
        $this->withoutExceptionHandling();
        // $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $vendor   = \App\Models\AddVendor::factory()->create();
        $vendorId = $vendor->id;

        $user       = \App\Models\User::find(1);
        $this->actingAs($user, 'api');
        Gate::define('add_vendor_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/add-vendors/'.$vendorId, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
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
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
