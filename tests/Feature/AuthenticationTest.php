<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Shift::create(['name' => 'Shift 1', 'start_time' => '07:00', 'end_time' => '14:00']);

        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'employee_id' => 'ADM-001',
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Employee Test',
            'email' => 'emp@test.com',
            'password' => bcrypt('password123'),
            'employee_id' => 'EMP-001',
            'role' => 'employee',
            'shift_id' => 1,
            'department' => 'IT',
            'position' => 'Developer',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'employee_id' => 'ADM-001',
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(User::where('employee_id', 'ADM-001')->first());
    }

    /** @test */
    public function employee_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'employee_id' => 'EMP-001',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/employee/dashboard');
        $this->assertAuthenticatedAs(User::where('employee_id', 'EMP-001')->first());
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'employee_id' => 'ADM-001',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('employee_id');
        $this->assertGuest();
    }

    /** @test */
    public function unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function employee_cannot_access_admin_dashboard(): void
    {
        $employee = User::where('employee_id', 'EMP-001')->first();
        $response = $this->actingAs($employee)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_access_employee_dashboard(): void
    {
        $admin = User::where('employee_id', 'ADM-001')->first();
        $response = $this->actingAs($admin)->get('/employee/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_logout(): void
    {
        $admin = User::where('employee_id', 'ADM-001')->first();
        $response = $this->actingAs($admin)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        User::where('employee_id', 'EMP-001')->update(['is_active' => false]);

        $response = $this->post('/login', [
            'employee_id' => 'EMP-001',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
