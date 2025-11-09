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

        // Stocker l'ID du compte dans les attributs de la requête si disponible dans le token
        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            // L'ID du compte pourrait être stocké dans les abilities ou metadata du token
            // Pour l'instant, on utilise une approche différente
        }

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