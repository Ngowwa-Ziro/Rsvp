<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

class QrService
{
    public function __construct(private EventSettings $event) {}

    /** The permanent public URL the QR points to. Refuses dev/temporary hosts. */
    public function targetUrl(): string
    {
        $base = rtrim($this->event->get('public_url'), '/');
        if ($base === '') {
            throw new RuntimeException('Set the permanent public URL in Settings before generating the QR code.');
        }
        $parts = parse_url($base);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https') {
            throw new RuntimeException('The public URL must start with https://');
        }
        if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)
            || preg_match('/\.(test|local|localhost|internal)$/', $host)
            || preg_match('/(ngrok|trycloudflare|loca\.lt|herokuapp|vercel\.app|netlify\.app)/', $host)
            || str_contains($host, 'your-domain')) {
            throw new RuntimeException('The public URL looks temporary or local. Use your final permanent domain.');
        }

        return $base.'/rsvp';
    }

    public function svg(int $size = 1024, int $quietZoneModules = 4): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, $quietZoneModules), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($this->targetUrl(), 'UTF-8', ErrorCorrectionLevel::H());
    }
}
