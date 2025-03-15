<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

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
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $user = Auth::guard('api')->user();

        return $this->respondWithToken($token);
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = $validated['role'] ?? 'ROLE_USER';

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'],
            'name' => $validated['name'],
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
     */
    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Déconnexion de l'utilisateur (invalidation du token).
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * Rafraîchir un token.
     */
    public function refresh(): JsonResponse
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
     */
    protected function respondWithToken(string $token): JsonResponse
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
     * Méthode statique pour API Platform - Connexion
     */
    public static function apiLogin($data): JsonResponse
    {
        $controller = new self();

        try {
            if (is_object($data) && $data instanceof Client) {
                $email = $data->email;
                $password = $data->password;
            } else {
                $requestContent = request()->getContent();

                if (!empty($requestContent)) {
                    $jsonData = json_decode($requestContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $email = $jsonData['email'] ?? null;
                        $password = $jsonData['password'] ?? null;
                    } else {
                        return response()->json(['error' => 'Format de données invalide'], 400);
                    }
                }

                if (!isset($email) || !isset($password)) {
                    return response()->json(['error' => 'Format de données invalide'], 400);
                }
            }

            if (!$email || !$password) {
                return response()->json(['error' => 'Email et mot de passe requis'], 400);
            }

            $request = new LoginRequest();
            $request->merge([
                'email' => $email,
                'password' => $password
            ]);

            return $controller->login($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la connexion: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Méthode statique pour API Platform - Inscription
     */
    public static function apiRegister($data): JsonResponse
    {
        $controller = new self();

        try {
            if (is_object($data) && $data instanceof Client) {
                $email = $data->email;
                $password = $data->password;
                $name = $data->name;
                $telephone = $data->telephone;
                $role = $data->role;
            } else {
                $requestContent = request()->getContent();

                if (!empty($requestContent)) {
                    $jsonData = json_decode($requestContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $email = $jsonData['email'] ?? null;
                        $password = $jsonData['password'] ?? null;
                        $name = $jsonData['name'] ?? null;
                        $telephone = $jsonData['telephone'] ?? null;
                        $role = $jsonData['role'] ?? 'ROLE_USER';
                    } else {
                        return response()->json(['error' => 'Format de données invalide'], 400);
                    }
                }

                if (!isset($email) || !isset($password) || !isset($name) || !isset($telephone)) {
                    return response()->json(['error' => 'Données d\'inscription incomplètes'], 400);
                }
            }

            $request = new RegisterRequest();
            $request->merge([
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'telephone' => $telephone,
                'role' => $role
            ]);

            return $controller->register($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'inscription: ' . $e->getMessage()], 500);
        }
    }
}
