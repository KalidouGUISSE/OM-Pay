<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Ajouter les permissions dans la requête pour utilisation ultérieure
        $permissions = $this->getPermissionsForRole($user->role);
        $request->merge(['permissions' => $permissions]);

        return $next($request);
    }

    private function getPermissionsForRole($role)
    {
        $rolePermissions = [
            'admin' => ['create', 'read', 'update', 'delete', 'manage_users'],
            'client' => ['read', 'update_own'],
        ];

        return $rolePermissions[$role] ?? [];
    }
}