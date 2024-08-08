<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FaqControllerTest extends TestCase
{
    protected $adminUser;
    protected $adminToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->adminToken = JWTAuth::fromUser($this->adminUser);
    }

    public function test_index_returns_paginated_faqs()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->adminToken"])
            ->getJson('/api/v1/faqs?page=1&size=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'question', 'answer']
                ],
                'pagination' => ['current_page', 'total_pages', 'page_size', 'total_items']
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_index_returns_faqs_without_pagination()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->adminToken"])
            ->getJson('/api/v1/faqs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'question', 'answer']
                ],
            ]);
    }
}
