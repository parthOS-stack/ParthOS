<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');

            // In local dev, Vite runs on http://127.0.0.1:5173 — allow it in CSP
            if (app()->environment('local')) {
                $viteHost = 'http://127.0.0.1:5173 ws://127.0.0.1:5173';
                $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: http: data: $viteHost;";
            } else {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
                $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:;";
            }

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
