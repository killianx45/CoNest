<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            Log::warning('CheckRole: Utilisateur non authentifié');
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        Log::info('CheckRole: Vérification des rôles', [
            'user_role' => $user->role,
            'required_roles' => $roles
        ]);

        // Si l'utilisateur est admin, il a accès à tout
        if ($user->role === 'ROLE_ADMIN') {
            return $next($request);
        }

        // Vérifier si l'utilisateur a l'un des rôles requis
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        Log::warning('CheckRole: Accès refusé - rôle insuffisant', [
            'user_role' => $user->role,
            'required_roles' => $roles
        ]);

        return response()->json([
            'error' => 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.'
        ], 403);
    }
}
