<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicApiUrl
{
    /**
     * Handle an incoming request.
     *
     * This middleware dynamically replaces localhost URLs in API responses
     * with the actual request host (e.g., 192.168.100.99:8000).
     * This allows the web dashboard to work with localhost while the mobile
     * app gets URLs with the server's IP address.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process JSON responses
        if ($response->headers->get('Content-Type') === 'application/json') {
            $content = $response->getContent();

            // Get the request's full base URL (e.g., http://192.168.100.99:8000)
            $requestBaseUrl = $request->getSchemeAndHttpHost();

            // Replace localhost:8000 and 127.0.0.1:8000 with the actual request host
            $content = str_replace(
                ['http://localhost:8000', 'http://127.0.0.1:8000'],
                $requestBaseUrl,
                $content
            );

            $response->setContent($content);
        }

        return $response;
    }
}
