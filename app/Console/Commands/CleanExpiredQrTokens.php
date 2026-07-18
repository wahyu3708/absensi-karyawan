<?php

namespace App\Console\Commands;

use App\Services\QrTokenService;
use Illuminate\Console\Command;

class CleanExpiredQrTokens extends Command
{
    protected $signature = 'qr:cleanup';
    protected $description = 'Clean up expired QR tokens older than 1 hour';

    public function handle(QrTokenService $service): int
    {
        $deleted = $service->cleanupExpired();
        $this->info("Cleaned up {$deleted} expired QR tokens.");
        return self::SUCCESS;
    }
}
