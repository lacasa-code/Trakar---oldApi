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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreatePartCategoryTest extends TestCase
{
    // use RefreshDatabase;
      // use DatabaseMigrations;
     // use  DatabaseTransactions;

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
    public function test_create_part_category()
    {
        $this->withoutExceptionHandling();
        Storage::fake('images');
        $faker      = \Faker\Factory::create();
        $form_data  = [
            'category_name' => $faker->word,
            'photo'         => UploadedFile::fake()->image('avatar.jpg'),
            // $faker->image('public/storage/images',640,480, null, false),
        ];

        $user       = \App\Models\User::factory()->create();
        $this->actingAs($user, 'api');
        Gate::define('part_category_create', function ($user) {
            return true;
        });
        
        $reponse = $this->json('POST', route('api.part-categories.store'), $form_data, [
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
                ->assertStatus(201);
        
    }
}
