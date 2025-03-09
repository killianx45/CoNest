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
        // Vérifier d'abord le guard web (pour les routes web)
        $user = Auth::user();

        // Si l'utilisateur n'est pas trouvé dans le guard web, essayer le guard API
        if (!$user) {
            $user = Auth::guard('api')->user();
        }

        if (!$user) {
            Log::warning('CheckRole: Utilisateur non authentifié');

            // Si c'est une requête API, renvoyer une réponse JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Sinon, rediriger vers la page de connexion
            return redirect()->route('login');
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

        // Si c'est une requête API, renvoyer une réponse JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.'
            ], 403);
        }

        // Sinon, rediriger vers une page d'erreur ou la page d'accueil
        return redirect()->route('dashboard')->with('error', 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.');
    }
}
