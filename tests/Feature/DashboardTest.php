<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Shift::create(['name' => 'Shift 1', 'start_time' => '07:00', 'end_time' => '14:00']);
        Shift::create(['name' => 'Shift 2', 'start_time' => '14:00', 'end_time' => '21:00']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'employee_id' => 'ADM-001',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_stats_api_returns_valid_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/dashboard/stats');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_employees',
                'present_today',
                'absent_today',
                'late_today',
                'on_time_today',
                'attendance_rate',
                'avg_late_minutes',
                'weekly_trend',
            ]);
    }

    /** @test */
    public function dashboard_charts_api_returns_valid_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/dashboard/charts');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'thirty_day_trend',
                'status_distribution',
                'department_stats',
                'shift_distribution',
                'top_late',
            ]);
    }

    /** @test */
    public function admin_can_access_employees_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/employees');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_reports_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertStatus(200);
    }

    /** @test */
    public function employees_page_supports_search(): void
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => bcrypt('password'),
            'employee_id' => 'EMP-001',
            'role' => 'employee',
            'shift_id' => 1,
            'department' => 'IT',
            'position' => 'Dev',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/employees?search=John');
        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }
}
