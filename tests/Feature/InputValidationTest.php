<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Office;
use App\Models\Outlet;
use App\Models\OutletType;
use Illuminate\Support\Facades\Hash;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_validation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        // Test successful user creation
        $response = $this->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_outlet',
        ]);
        
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        // Test validation errors
        $response = $this->post('/users', [
            'name' => '', // Required field
            'email' => 'invalid-email', // Invalid email
            'password' => '123', // Too short
            'password_confirmation' => 'different', // Doesn't match
            'role' => 'invalid_role', // Invalid role
        ]);
        
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_office_creation_validation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        // Test successful office creation
        $response = $this->post('/offices', [
            'name' => 'Test Office',
            'code' => 'TO001',
            'type' => 'area',
            'description' => 'Test Description',
        ]);
        
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('offices', ['code' => 'TO001']);

        // Test validation errors
        $response = $this->post('/offices', [
            'name' => '', // Required field
            'code' => 'TO001', // Already exists
            'type' => 'invalid_type', // Invalid type
        ]);
        
        $response->assertSessionHasErrors(['name', 'type']);
    }

    public function test_outlet_creation_validation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        $office = Office::factory()->create();
        $outletType = OutletType::factory()->create();

        // Test successful outlet creation
        $response = $this->post('/outlets', [
            'name' => 'Test Outlet',
            'code' => 'TO001',
            'office_id' => $office->id,
            'outlet_type_id' => $outletType->id,
            'description' => 'Test Description',
        ]);
        
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('outlets', ['code' => 'TO001']);

        // Test validation errors
        $response = $this->post('/outlets', [
            'name' => '', // Required field
            'code' => 'TO001', // Already exists
            'office_id' => 999, // Non-existent office
            'outlet_type_id' => 999, // Non-existent outlet type
        ]);
        
        $response->assertSessionHasErrors(['name', 'office_id', 'outlet_type_id']);
    }

    public function test_outlet_type_validation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        // Test successful outlet type creation
        $response = $this->post('/outlet-types', [
            'name' => 'Test Type',
            'description' => 'Test Description',
        ]);
        
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('outlet_types', ['name' => 'Test Type']);

        // Test validation errors
        $response = $this->post('/outlet-types', [
            'name' => '', // Required field
            'description' => str_repeat('a', 600), // Too long
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }

    public function test_daily_income_validation(): void
    {
        $adminOutlet = User::factory()->create(['role' => 'admin_outlet']);
        $this->actingAs($adminOutlet);

        // Test validation errors for daily income creation
        $response = $this->post('/daily-incomes', [
            'date' => 'invalid-date', // Invalid date
            'moda_id' => 999, // Non-existent moda
            'colly' => -1, // Negative value
            'weight' => -1.5, // Negative value
            'income' => -100, // Negative value
        ]);
        
        $response->assertSessionHasErrors(['date', 'moda_id', 'colly', 'weight', 'income']);
    }

    public function test_form_request_authorization(): void
    {
        $adminOutlet = User::factory()->create(['role' => 'admin_outlet']);
        $this->actingAs($adminOutlet);

        // Admin outlet should not be able to create offices
        $response = $this->post('/offices', [
            'name' => 'Unauthorized Office',
            'code' => 'UO001',
            'type' => 'area',
        ]);
        
        $response->assertStatus(403);
    }
}