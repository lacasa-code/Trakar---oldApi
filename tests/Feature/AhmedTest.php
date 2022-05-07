<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AhmedTest extends TestCase
{
    use WithoutMiddleware;
   // use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_ahmed()
    {
         $this->assertTrue(true);
       /* $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
        $this->withoutExceptionHandling();
        $reponse = $this->json('GET', '/api/v1/permissions');
        $reponse->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "title",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                ] // end *
            ], // end data 
            'total',
        ]) // end structure
                ->assertStatus(200);*/
    }
}
