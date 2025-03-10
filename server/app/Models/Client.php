<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Http\Controllers\AuthController;

#[ApiResource(
    operations: [
        new Post(
            processor: [AuthController::class, 'apiLogin'],
            uriTemplate: '/auth/login'
        ),
        new Post(
            processor: [AuthController::class, 'apiRegister'],
            uriTemplate: '/auth/register'
        )
    ]
)]
class Client
{
    // Champs pour la connexion
    public string $email;
    public string $password;

    // Champs pour l'inscription
    public ?string $name;
    public ?string $telephone;
    public ?string $role = 'ROLE_USER';
}
