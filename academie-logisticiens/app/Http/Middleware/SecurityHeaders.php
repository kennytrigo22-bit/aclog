<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $h = $response->headers;
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');
        $h->set('X-Permitted-Cross-Domain-Policies', 'none');
        $h->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        $h->set('Content-Security-Policy', $this->csp());

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($request->user()) {
            $h->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    private function csp(): string
    {
        // Serveur Vite de developpement (public/hot, env local uniquement) : adresses variables, y compris IPv6 que CSP n'accepte pas en litteral.
        $vite = $viteWs = '';
        if (app()->environment('local') && file_exists(public_path('hot'))) {
            $vite = ' http:';
            $viteWs = ' ws:';
        }

        $directives = [
            "default-src 'self'",
            // Alpine.js evalue ses expressions et les vues utilisent des scripts en ligne.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$vite}",
            "style-src 'self' 'unsafe-inline'{$vite}",
            "font-src 'self' data:",
            "img-src 'self' data: blob: https://i.ytimg.com https://img.youtube.com",
            "media-src 'self' https:",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com",
            "connect-src 'self'{$vite}{$viteWs}",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        if (app()->environment('production')) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
