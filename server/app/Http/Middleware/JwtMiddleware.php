<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Services\AuthorizationService;

class JwtMiddleware
{
    /**
     * Liste des routes qui ne nécessitent pas d'authentification
     */
    protected $exceptRoutes = [
        'api/docs',
        'api/login',
        'api/auth/login',
        'api/contexts',
        'api/.well-known',
        'api/refresh',
        'api/graphql',
    ];

    /**
     * Routes qui nécessitent un rôle spécifique
     */
    protected $roleRoutes = [
        'ROLE_ADMIN' => [
            'api/users',
            'api/categories',
            'api/produits',
            'api/commandes',
        ],
        'ROLE_LOUEUR' => [
            'api/produits',
            'api/commandes',
        ],
        'ROLE_USER' => [
            'api/produits',
            'api/commandes',
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $currentPath = $request->path();

        Log::info('JwtMiddleware: Vérification de la route', [
            'path' => $currentPath,
            'method' => $request->method(),
            'has_authorization' => $request->hasHeader('Authorization'),
            'all_headers' => $request->headers->all()
        ]);

        // Autoriser l'accès à la page d'accueil de l'API
        if ($currentPath === 'api' && $request->isMethod('GET')) {
            Log::info('JwtMiddleware: Accès autorisé à la page d\'accueil API');
            return $next($request);
        }

        // Autoriser l'accès à la route GET api/produits sans token
        if ($currentPath === 'api/produits' && $request->isMethod('GET')) {
            Log::info('JwtMiddleware: Accès autorisé à la liste des produits sans token');
            return $next($request);
        }

        // Autoriser l'accès aux routes publiques
        foreach ($this->exceptRoutes as $route) {
            if (str_starts_with($currentPath, $route)) {
                Log::info('JwtMiddleware: Route exclue de l\'authentification', ['route' => $route]);
                return $next($request);
            }
        }

        // Toutes les autres routes nécessitent un token JWT valide
        try {
            $token = null;

            // Extraire le token de l'en-tête Authorization
            if ($request->hasHeader('Authorization')) {
                $authHeader = $request->header('Authorization');
                Log::info('JwtMiddleware: En-tête Authorization trouvé', ['header' => $authHeader]);

                if (preg_match('/^bearer\s+(.*)$/i', $authHeader, $matches)) {
                    $token = $matches[1];
                    Log::info('JwtMiddleware: Token extrait avec succès du header Authorization');
                }
            }

            // Extraire le token de l'en-tête X-Authorization
            if (!$token && $request->headers->has('X-Authorization')) {
                $authHeader = $request->headers->get('X-Authorization');
                Log::info('JwtMiddleware: En-tête X-Authorization trouvé', ['header' => $authHeader]);

                if (preg_match('/^bearer\s+(.*)$/i', $authHeader, $matches)) {
                    $token = $matches[1];
                    Log::info('JwtMiddleware: Token extrait avec succès du header X-Authorization');
                } else {
                    $token = $authHeader;
                    Log::info('JwtMiddleware: Token brut extrait du header X-Authorization');
                }
            }

            // Extraire le token des paramètres de requête
            if (!$token && $request->has('token')) {
                $token = $request->input('token');
                Log::info('JwtMiddleware: Token trouvé dans les paramètres de requête');
            }

            // Vérifier si un token a été trouvé
            if (!$token) {
                Log::warning('JwtMiddleware: Aucun token trouvé');
                return response()->json(['error' => 'Token d\'autorisation non trouvé'], 401);
            }

            // Définir et authentifier le token
            JWTAuth::setToken($token);
            Log::info('JwtMiddleware: Tentative d\'authentification avec le token', ['token_length' => strlen($token)]);

            $user = JWTAuth::authenticate();

            if (!$user) {
                Log::warning('JwtMiddleware: Utilisateur non trouvé avec le token fourni');
                return response()->json(['error' => 'Utilisateur non trouvé'], 401);
            }

            Log::info('JwtMiddleware: Utilisateur authentifié avec succès', ['user_id' => $user->id, 'role' => $user->role]);

            // Vérifier les permissions basées sur le rôle
            $hasAccess = false;

            // Les administrateurs ont accès à toutes les routes
            if ($user->role === 'ROLE_ADMIN') {
                $hasAccess = true;
            } else {
                // Vérifier si l'utilisateur a accès à cette route spécifique
                if (isset($this->roleRoutes[$user->role])) {
                    foreach ($this->roleRoutes[$user->role] as $route) {
                        if (str_starts_with($currentPath, $route)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                // Routes communes à tous les utilisateurs authentifiés
                if (!$hasAccess) {
                    $commonRoutes = [
                        'api/me',
                        'api/profile',
                        'api/logout',
                        'api/refresh',
                    ];

                    foreach ($commonRoutes as $route) {
                        if (str_starts_with($currentPath, $route)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasAccess) {
                Log::warning('JwtMiddleware: Accès refusé - permissions insuffisantes', [
                    'role' => $user->role,
                    'path' => $currentPath
                ]);
                return response()->json([
                    'error' => 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.'
                ], 403);
            }

            // Ajouter l'utilisateur aux attributs de la requête
            $request->attributes->set('auth_user', $user);
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                Log::warning('JwtMiddleware: Token invalide', ['exception' => get_class($e), 'message' => $e->getMessage()]);
                return response()->json(['error' => 'Token invalide'], 401);
            } else if ($e instanceof TokenExpiredException) {
                Log::warning('JwtMiddleware: Token expiré', ['exception' => get_class($e), 'message' => $e->getMessage()]);
                return response()->json(['error' => 'Token expiré'], 401);
            } else {
                Log::warning('JwtMiddleware: Erreur d\'authentification', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Erreur d\'authentification: ' . $e->getMessage()], 401);
            }
        }

        return $next($request);
    }
}
