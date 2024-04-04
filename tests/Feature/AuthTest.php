<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AuthTest extends TestCase
{

    public function testLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
            // 'address' => '123 Test St',
            'contact' => '1234567890',
        ]);

        $response = $this->json('POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'login_time' => $response['data']['login_time'],
                    'exp' => $response['data']['exp'],
                    'api_token' => $response['data']['api_token'],
                    'refresh_token' => $response['data']['refresh_token'],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        // 'address' => $user->address,
                        'contact' => $user->contact,
                    ],
                    'role' => [
                        'role_id' => $response['data']['role']['role_id'],
                        'role' => $response['data']['role']['role'],
                        'permission' => $response['data']['role']['permission'],
                    ],
                ],
            ]);
    }

    public function testLoginWithInvalidCredentials()
    {
        $response = $this->json('POST', '/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null,
            ]);
    }

  

}
