<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Http\Controllers\AuthController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
class Client extends Model
{
    public string $email;
    public string $password;
    public ?string $name;
    public ?string $telephone;
    public ?string $role = 'ROLE_USER';
    public ?bool $concours = false;

    public function ApiConcours()
    {
        $user = Auth::user();
        $moisCourant = now()->format('m');
        $anneeCourante = now()->format('Y');
        $commandes = Commande::with('produits')
            ->where('id_user', $user->id)
            ->whereMonth('created_at', $moisCourant)
            ->whereYear('created_at', $anneeCourante)
            ->get();

        $totalHeures = 0;

        foreach ($commandes as $commande) {
            foreach ($commande->produits as $produit) {
                $reservation = $produit->pivot;
                if ($reservation->heure_debut && $reservation->heure_fin) {
                    $debut = strtotime($reservation->heure_debut);
                    $fin = strtotime($reservation->heure_fin);
                    $heures = ($fin - $debut) / 3600;
                    $totalHeures += $heures;
                }
            }
        }

        if ($totalHeures >= 12) {
            $user = User::find($user->id);
            $user->concours = 1;
            $user->save();
        }
    }
}
