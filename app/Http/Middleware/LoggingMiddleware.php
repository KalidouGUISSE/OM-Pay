<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $method = $request->method();
        $uri = $request->path();
        $ip = $request->ip();

        // Log l'opération avant traitement
        Log::info('API Operation', [
            'user_id' => $user ? $user->id : null,
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'timestamp' => now(),
        ]);

        $response = $next($request);

        // Log après traitement si nécessaire
        if ($response->getStatusCode() >= 400) {
            Log::warning('API Error Operation', [
                'user_id' => $user ? $user->id : null,
                'method' => $method,
                'uri' => $uri,
                'status_code' => $response->getStatusCode(),
                'timestamp' => now(),
            ]);
        }

        return $response;
    }
}