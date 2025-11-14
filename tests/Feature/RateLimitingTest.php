<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiting_works(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Hit the login endpoint multiple times to reach the rate limit
        for ($i = 0; $i < 6; $i++) { // 6 requests to exceed 5 per minute limit
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword', // Intentionally wrong password
            ]);
        }

        // The next request should be throttled
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Should return a 429 status (Too Many Requests)
        $response->assertStatus(429);
    }

    public function test_registration_rate_limiting_works(): void
    {
        // Hit the registration endpoint multiple times to reach the rate limit
        for ($i = 0; $i < 4; $i++) { // 4 requests to exceed 3 per minute limit
            $response = $this->post('/register', [
                'name' => 'Test User ' . $i,
                'email' => 'test' . $i . '@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin_outlet', // Assuming this is valid for testing
            ]);
        }

        // The next request should be throttled
        $response = $this->post('/register', [
            'name' => 'Another Test User',
            'email' => 'another@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_outlet',
        ]);

        // Should return a 429 status (Too Many Requests)
        $response->assertStatus(429);
    }

    public function test_successful_login_does_not_trigger_rate_limit(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Make multiple successful login attempts (should not be throttled)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password', // Correct password
            ]);
            
            // Should be redirected after successful login (status 302)
            $response->assertStatus(302);
            
            // Logout for next attempt
            $this->post('/logout');
        }

        // Final attempt should still work since successful logins don't count toward the limit
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(302);
    }
}