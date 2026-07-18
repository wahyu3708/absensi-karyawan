<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shift;
use App\Models\QrToken;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Shift::create(['name' => 'Shift 1', 'start_time' => '07:00', 'end_time' => '14:00']);

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
    public function admin_can_access_qr_display_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/qr-display');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_generate_qr_code(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/qr/generate');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'qr_svg', 'expires_at', 'next_refresh']);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function qr_token_is_created_in_database(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();

        $this->assertDatabaseHas('qr_tokens', [
            'id' => $token->id,
            'is_used' => false,
        ]);
    }

    /** @test */
    public function expired_qr_token_is_rejected(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();

        // Manually expire the token
        $token->update(['expires_at' => now()->subMinutes(1)]);

        $result = $service->validate($token->encrypted_payload);
        $this->assertNull($result);
    }

    /** @test */
    public function used_qr_token_is_rejected(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();
        $token->markAsUsed($this->admin->id);

        $result = $service->validate($token->encrypted_payload);
        $this->assertNull($result);
    }

    /** @test */
    public function valid_qr_token_is_accepted(): void
    {
        $service = new QrTokenService();
        $token = $service->generate();

        $result = $service->validate($token->encrypted_payload);
        $this->assertNotNull($result);
        $this->assertEquals($token->id, $result->id);
    }

    /** @test */
    public function cleanup_removes_expired_tokens(): void
    {
        $service = new QrTokenService();

        // Create tokens with past expiry
        QrToken::create([
            'token' => 'expired-token-hash',
            'encrypted_payload' => 'test',
            'expires_at' => now()->subHours(2),
        ]);

        $deleted = $service->cleanupExpired();
        $this->assertGreaterThanOrEqual(1, $deleted);
    }
}
