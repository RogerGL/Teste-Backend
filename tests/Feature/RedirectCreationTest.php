<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_creation_with_valid_url()
    {
        $url = 'https://valid-url.com';
        $response = $this->postJson('/api/redirects', ['destination_url' => $url, 'status' => 'ativo']);
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('redirects', ['destination_url' => $url]);
    }

    public function test_redirect_creation_with_invalid_url()
    {
        $url = 'https://invalid-dns.com';
        $response = $this->postJson('/redirects', ['destination_url' => $url, 'status' => 'ativo']);
        $response->assertStatus(404);
    }

    
}
