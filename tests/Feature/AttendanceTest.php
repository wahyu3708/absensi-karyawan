<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $admin;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shift = Shift::create(['name' => 'Shift 1', 'start_time' => '07:00', 'end_time' => '14:00']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'employee_id' => 'ADM-001',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->employee = User::create([
            'name' => 'Employee',
            'email' => 'emp@test.com',
            'password' => bcrypt('password123'),
            'employee_id' => 'EMP-001',
            'role' => 'employee',
            'shift_id' => $this->shift->id,
            'department' => 'IT',
            'position' => 'Developer',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function employee_can_access_scan_page(): void
    {
        $response = $this->actingAs($this->employee)->get('/employee/scan');
        $response->assertStatus(200);
    }

    /** @test */
    public function clock_in_requires_valid_qr_token(): void
    {
        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-in', [
            'qr_data' => 'invalid-token-data',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function clock_in_requires_location(): void
    {
        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-in', [
            'qr_data' => 'some-data',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function clock_in_with_valid_qr_creates_attendance(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();

        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-in', [
            'qr_data' => $token->encrypted_payload,
            'latitude' => config('app.company_latitude'),
            'longitude' => config('app.company_longitude'),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
            'date' => today()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function duplicate_clock_in_is_rejected(): void
    {
        // Create existing attendance
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_in_status' => 'on_time',
            'shift_id' => $this->shift->id,
            'location_valid' => true,
        ]);

        $service = new QrTokenService();
        $token = $service->generate();

        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-in', [
            'qr_data' => $token->encrypted_payload,
            'latitude' => config('app.company_latitude'),
            'longitude' => config('app.company_longitude'),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function clock_out_requires_clock_in_first(): void
    {
        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-out', [
            'latitude' => config('app.company_latitude'),
            'longitude' => config('app.company_longitude'),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function clock_out_succeeds_after_clock_in(): void
    {
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => today(),
            'clock_in' => now()->subHours(7),
            'clock_in_status' => 'on_time',
            'shift_id' => $this->shift->id,
            'location_valid' => true,
        ]);

        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-out', [
            'latitude' => config('app.company_latitude'),
            'longitude' => config('app.company_longitude'),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function employee_can_view_attendance_history(): void
    {
        $response = $this->actingAs($this->employee)->get('/employee/history');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_all_attendances(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendances');
        $response->assertStatus(200);
    }

    /** @test */
    public function today_status_api_returns_correct_data(): void
    {
        $response = $this->actingAs($this->employee)->getJson('/api/attendance/today');
        $response->assertStatus(200)
            ->assertJsonStructure(['has_clocked_in', 'has_clocked_out', 'shift']);
    }

    /** @test */
    public function location_outside_geofence_is_rejected(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();

        // Location far from company
        $response = $this->actingAs($this->employee)->postJson('/api/attendance/clock-in', [
            'qr_data' => $token->encrypted_payload,
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}
