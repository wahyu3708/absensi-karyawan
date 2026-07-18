<?php

namespace App\Services;

use App\Models\QrToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class QrTokenService
{
    /**
     * Generate a new QR token with a short scan token for compact QR codes.
     * Token expires in 15 seconds for security.
     */
    public function generate(): QrToken
    {
        $token = Str::uuid()->toString() . '-' . bin2hex(random_bytes(16));
        $scanToken = strtoupper(Str::random(16)); // Short token for QR code (~16 chars)
        $expiresAt = now()->addSeconds(15);

        $payload = [
            'token' => $token,
            'scan_token' => $scanToken,
            'generated_at' => now()->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
            'nonce' => Str::random(32),
        ];

        $encryptedPayload = Crypt::encryptString(json_encode($payload));

        return QrToken::create([
            'token' => hash('sha256', $token),
            'scan_token' => $scanToken,
            'encrypted_payload' => $encryptedPayload,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Validate a scanned QR code token.
     * Accepts either a short scan_token or the full encrypted payload.
     * Returns the QrToken if valid, null otherwise.
     */
    public function validate(string $scannedData): ?QrToken
    {
        // Try short scan_token first (new approach)
        $qrToken = QrToken::where('scan_token', $scannedData)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($qrToken) {
            return $qrToken;
        }

        // Fallback: try encrypted payload (old approach)
        try {
            $decrypted = Crypt::decryptString($scannedData);
            $payload = json_decode($decrypted, true);

            if (!$payload || !isset($payload['token'])) {
                return null;
            }

            $hashedToken = hash('sha256', $payload['token']);

            return QrToken::where('token', $hashedToken)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the QR code data string for display.
     * Returns the short scan_token for compact QR codes.
     */
    public function getQrDataString(QrToken $qrToken): string
    {
        return $qrToken->scan_token;
    }

    /**
     * Cleanup expired tokens older than 1 hour.
     */
    public function cleanupExpired(): int
    {
        return QrToken::where('expires_at', '<', now()->subHour())->delete();
    }

    /**
     * Get the latest valid token (for display polling).
     */
    public function getLatestValid(): ?QrToken
    {
        return QrToken::valid()->latest()->first();
    }
}
