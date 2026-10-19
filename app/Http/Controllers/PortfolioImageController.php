<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\PortfolioImage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortfolioImageController extends Controller
{
    public function show(Request $request, Portfolio $portfolio, PortfolioImage $image): Response
    {
        abort_unless($portfolio->template_choice && $image->portfolio_id === $portfolio->id, 404);
        abort_unless(in_array($image->mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        $bytes = $image->bytes;
        abort_unless(is_string($bytes), 404);

        $etag = '"'.hash('sha256', $bytes).'"';
        $headers = [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $etag,
            'X-Content-Type-Options' => 'nosniff',
        ];

        if (trim((string) $request->headers->get('If-None-Match')) === $etag) {
            return response('', 304, $headers);
        }

        return response($bytes, 200, $headers + ['Content-Type' => $image->mime]);
    }
}
