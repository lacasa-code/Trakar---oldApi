<?php

namespace Tests\Feature\Api\V1\PartCategories;

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

class ShowPartCategoryTest extends TestCase
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
    public function test_show_part_category()
    {
        $this->withoutExceptionHandling();
        // $categories = \App\Models\ProductCategory::factory()->count(5)->create();
        $part_category   = \App\Models\PartCategory::factory()->create();
        $partCategory    = $part_category->id;

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('part_category_show', function ($user) {
            return true;
        });

         $reponse = $this->json('GET', '/api/v1/part-categories/'.$partCategory, [], [
            'Accept' => 'application/json',
        ]);

        $reponse->assertJsonStructure([
            'data' => [
                   "id",
                    "category_name",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "photo",
                    "media",   
            ], // end data 
        ]) // end structure
                ->assertStatus(200);
    }
}
