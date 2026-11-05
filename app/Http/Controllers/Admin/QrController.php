<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QrService;

class QrController extends Controller
{
    public function show(QrService $qr)
    {
        try {
            return view('admin.qr', ['url' => $qr->targetUrl(), 'svg' => $qr->svg(), 'error' => null]);
        } catch (\RuntimeException $e) {
            return view('admin.qr', ['url' => null, 'svg' => null, 'error' => $e->getMessage()]);
        }
    }

    public function svg(QrService $qr)
    {
        try {
            $svg = $qr->svg(2048);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.qr')->withErrors(['qr' => $e->getMessage()]);
        }

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="graduation-rsvp-qr.svg"',
        ]);
    }
}
