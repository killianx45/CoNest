<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Client;

class AuthController extends Controller
{
    /**
     * Créer une nouvelle instance de AuthController.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Obtenir un JWT via les identifiants fournis.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        Log::info('Tentative de connexion avec les identifiants', ['email' => $credentials['email']]);

        if (!$token = auth('api')->attempt($credentials)) {
            Log::warning('Echec de authentification: identifiants invalides');
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $user = Auth::guard('api')->user();
        Log::info('Authentification réussie', ['user_id' => $user->id, 'role' => $user->role]);

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
        ]);

        $role = $request->role ?? 'ROLE_USER';

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'name' => $request->name,
            'role' => $role,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'telephone' => $user->telephone,
                'role' => $user->role
            ]
        ], 201);
    }
    /**
     * Obtenir l'utilisateur authentifié.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Déconnexion de l'utilisateur (invalidation du token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * Rafraîchir un token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token invalide ou expiré'], 401);
        }
    }

    /**
     * Obtenir la structure de tableau du token.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'instructions' => 'Pour utiliser ce token, ajoutez-le à l\'en-tête Authorization de vos requêtes sous la forme "Bearer {token}"'
        ]);
    }

    /**
     * Méthode statique pour API Platform
     * 
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function apiLogin($data): \Illuminate\Http\JsonResponse
    {
        Log::info('Appel à apiLogin', [
            'data_type' => gettype($data),
            'data' => is_object($data) ? get_class($data) : 'non-objet',
            'request_content' => request()->getContent(),
            'all_headers' => request()->headers->all()
        ]);

        $controller = new self();

        try {
            if (is_object($data) && $data instanceof Client) {
                Log::info('Données reçues comme objet Client', [
                    'email' => $data->email ?? 'non défini',
                    'password_exists' => isset($data->password)
                ]);
                $email = $data->email;
                $password = $data->password;
            } elseif (is_array($data)) {
                Log::info('Données reçues comme tableau', [
                    'data_keys' => array_keys($data),
                    'email_exists' => isset($data['email']),
                    'password_exists' => isset($data['password'])
                ]);
                $email = $data['email'] ?? null;
                $password = $data['password'] ?? null;
            } elseif (is_object($data) && method_exists($data, 'all')) {
                $requestData = $data->all();
                Log::info('Données reçues comme objet Request', [
                    'request_data' => $requestData,
                    'email_exists' => isset($requestData['email']),
                    'password_exists' => isset($requestData['password'])
                ]);
                $email = $requestData['email'] ?? null;
                $password = $requestData['password'] ?? null;
            } else {
                $requestContent = request()->getContent();
                Log::info('Tentative de récupération des données du corps de la requête', [
                    'request_content' => $requestContent
                ]);

                if (!empty($requestContent)) {
                    $jsonData = json_decode($requestContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info('Données JSON décodées avec succès', [
                            'json_data' => $jsonData,
                            'email_exists' => isset($jsonData['email']),
                            'password_exists' => isset($jsonData['password'])
                        ]);
                        $email = $jsonData['email'] ?? null;
                        $password = $jsonData['password'] ?? null;
                    } else {
                        Log::error('Erreur de décodage JSON', [
                            'json_error' => json_last_error_msg()
                        ]);
                    }
                }

                if (!isset($email) || !isset($password)) {
                    Log::error('Format de données invalide dans apiLogin', [
                        'type' => gettype($data),
                        'class' => is_object($data) ? get_class($data) : 'non-objet'
                    ]);
                    return response()->json(['error' => 'Format de données invalide'], 400);
                }
            }

            if (!$email || !$password) {
                Log::error('Données manquantes dans apiLogin', [
                    'email_exists' => (bool)$email,
                    'password_exists' => (bool)$password
                ]);
                return response()->json(['error' => 'Email et mot de passe requis'], 400);
            }

            $request = new Request();
            $request->merge([
                'email' => $email,
                'password' => $password
            ]);

            Log::info('Délégation à la méthode login', ['email' => $email]);
            return $controller->login($request);
        } catch (\Exception $e) {
            Log::error('Exception dans apiLogin', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la connexion: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Méthode statique pour API Platform - Inscription
     * 
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function apiRegister($data): \Illuminate\Http\JsonResponse
    {
        Log::info('Appel à apiRegister', [
            'data_type' => gettype($data),
            'data' => is_object($data) ? get_class($data) : 'non-objet',
            'request_content' => request()->getContent(),
            'all_headers' => request()->headers->all()
        ]);

        $controller = new self();

        try {
            if (is_object($data) && $data instanceof Client) {
                Log::info('Données reçues comme objet Client', [
                    'email' => $data->email ?? 'non défini',
                    'name' => $data->name ?? 'non défini',
                    'telephone' => $data->telephone ?? 'non défini',
                    'role' => $data->role ?? 'ROLE_USER'
                ]);

                $email = $data->email;
                $password = $data->password;
                $name = $data->name;
                $telephone = $data->telephone;
                $role = $data->role;
            } else {
                $requestContent = request()->getContent();
                Log::info('Tentative de récupération des données du corps de la requête', [
                    'request_content' => $requestContent
                ]);

                if (!empty($requestContent)) {
                    $jsonData = json_decode($requestContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $email = $jsonData['email'] ?? null;
                        $password = $jsonData['password'] ?? null;
                        $name = $jsonData['name'] ?? null;
                        $telephone = $jsonData['telephone'] ?? null;
                        $role = $jsonData['role'] ?? 'ROLE_USER';
                    } else {
                        Log::error('Erreur de décodage JSON', [
                            'json_error' => json_last_error_msg()
                        ]);
                    }
                }

                if (!isset($email) || !isset($password) || !isset($name) || !isset($telephone)) {
                    Log::error('Données manquantes dans apiRegister');
                    return response()->json(['error' => 'Données d\'inscription incomplètes'], 400);
                }
            }

            $request = new Request();
            $request->merge([
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'telephone' => $telephone,
                'role' => $role
            ]);

            Log::info('Délégation à la méthode register', ['email' => $email, 'role' => $role]);
            return $controller->register($request);
        } catch (\Exception $e) {
            Log::error('Exception dans apiRegister', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de l\'inscription: ' . $e->getMessage()], 500);
        }
    }
}
