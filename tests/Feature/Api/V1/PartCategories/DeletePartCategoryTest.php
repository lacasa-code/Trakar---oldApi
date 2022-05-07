<?php

namespace Tests\Feature\Api\V1\PartCategories;

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

class DeletePartCategoryTest extends TestCase
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
    public function test_delete_part_category()
    {
        $this->withoutExceptionHandling();
       // $categories = \App\Models\ProductCategory::factory()->count(1)->create();
        $part_category  = \App\Models\PartCategory::factory()->create();
        $partCategory   = $part_category->id;

        $user       = \App\Models\User::find(1); // ->create();
        $this->actingAs($user, 'api');
        Gate::define('part_category_delete', function ($user) {
            return true;
        });

        $response = $this->json('delete', '/api/v1/part-categories/'.$part_category->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);
    }
}
