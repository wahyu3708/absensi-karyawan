<?php

namespace App\Http\Controllers;

use App\Services\QrTokenService;
use Illuminate\Http\Request;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;

class QrCodeController extends Controller
{
    protected QrTokenService $qrTokenService;

    public function __construct(QrTokenService $qrTokenService)
    {
        $this->qrTokenService = $qrTokenService;
    }

    /**
     * Display the QR code page (for admin monitor/TV).
     */
    public function display()
    {
        return view('admin.qr-display');
    }

    /**
     * Generate a new QR code (called via AJAX every 5-10 seconds).
     */
    public function generate(Request $request)
    {
        $qrToken = $this->qrTokenService->generate();
        $qrData = $this->qrTokenService->getQrDataString($qrToken);

        // Generate QR Code as SVG (chillerlan/php-qrcode v6 API)
        $options = new QROptions();
        $options->outputInterface = QRMarkupSVG::class;
        $options->eccLevel = EccLevel::M;
        $options->scale = 10;
        $options->outputBase64 = false;
        $options->addQuietzone = true;
        $options->svgUseCssProperties = true;

        $qrCode = new QRCode($options);
        $svgString = $qrCode->render($qrData);

        return response()->json([
            'success' => true,
            'qr_svg' => $svgString,
            'expires_at' => $qrToken->expires_at->toISOString(),
            'generated_at' => now()->toISOString(),
            'next_refresh' => rand(5, 10), // Random 5-10 seconds
        ]);
    }

    /**
     * Validate a scanned QR code token.
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        $qrToken = $this->qrTokenService->validate($request->qr_data);

        if (!$qrToken) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa. Silakan scan ulang.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'token_id' => $qrToken->id,
            'message' => 'QR Code valid.',
        ]);
    }
}
