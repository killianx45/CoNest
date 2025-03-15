<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Commande extends Model
{
    protected $fillable = ['prix', 'id_user'];

    public function client()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'commande_produit', 'commande_id', 'produit_id')
            ->withPivot('date_reservation', 'heure_debut', 'heure_fin');
    }

    public function calculerPrixTotal()
    {
        $total = 0;
        foreach ($this->produits as $produit) {
            $heureDebut = new \DateTime($produit->pivot->heure_debut);
            $heureFin = new \DateTime($produit->pivot->heure_fin);
            $diff = $heureDebut->diff($heureFin);
            $heures = $diff->h + ($diff->i / 60);

            $total += $produit->prix * $heures;
        }
        return $total;
    }

    public static function estDisponible($produitId, $date, $heureDebut, $heureFin)
    {
        return !self::join('commande_produit', 'commandes.id', '=', 'commande_produit.commande_id')
            ->where('commande_produit.produit_id', $produitId)
            ->where('commande_produit.date_reservation', $date)
            ->where('commande_produit.heure_debut', '<', $heureFin)
            ->where('commande_produit.heure_fin', '>', $heureDebut)
            ->exists();
    }
}
