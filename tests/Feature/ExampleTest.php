<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSeeText(['EPF Africa', 'Programmes', 'Soumettre une candidature']);
    }

    public function test_candidature_creation_route_is_available(): void
    {
        $response = $this->get('/candidatures/create');

        $response->assertStatus(200)
            ->assertSeeText(['Nouvelle candidature', 'Informations personnelles']);
    }
}
