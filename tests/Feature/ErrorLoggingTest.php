<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ErrorLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_is_logged(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Mock the logger to verify it's called
        Log::shouldReceive('error')
            ->once()
            ->with('auth - login Error: Failed login attempt', \Mockery::any());

        // Attempt failed login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword', // Intentionally wrong password
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_exception_logging_works(): void
    {
        // This test checks that global exception logging is configured
        // We'll trigger an exception and make sure it's logged properly
        
        // Mock the logger to verify it's called when an exception occurs
        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::any());

        $this->get('/non-existent-route')
            ->assertStatus(404);
    }

    public function test_registration_error_logging(): void
    {
        // Mock the logger to verify it's called during registration errors
        Log::shouldReceive('error')
            ->once()
            ->with('auth - registration Error:', \Mockery::any());

        // Create an authorized user for registration
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($admin);

        // Try to register a user with validation error (missing required fields)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'nonexistent_role', // This should cause validation error
        ]);

        $response->assertSessionHasErrors('role');
    }
}