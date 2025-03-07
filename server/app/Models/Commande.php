<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Méthode pour calculer le prix total de la commande
    public function calculerPrixTotal()
    {
        $total = 0;
        foreach ($this->produits as $produit) {
            // Calculer la différence d'heures
            $heureDebut = new \DateTime($produit->pivot->heure_debut);
            $heureFin = new \DateTime($produit->pivot->heure_fin);
            $diff = $heureDebut->diff($heureFin);
            $heures = $diff->h + ($diff->i / 60);

            // Multiplier le prix horaire par le nombre d'heures
            $total += $produit->prix * $heures;
        }
        return $total;
    }

    // Vérifier si un créneau horaire est disponible
    public static function estDisponible($produitId, $date, $heureDebut, $heureFin)
    {
        return self::whereHas('produits', function ($query) use ($produitId, $date, $heureDebut, $heureFin) {
            $query->where('produit_id', $produitId)
                ->where('date_reservation', $date)
                ->where(function ($q) use ($heureDebut, $heureFin) {
                    // Vérifie si le créneau demandé chevauche un créneau existant
                    $q->where(function ($subq) use ($heureDebut, $heureFin) {
                        $subq->where('heure_debut', '<', $heureFin)
                            ->where('heure_fin', '>', $heureDebut);
                    });
                });
        })->count() === 0;
    }
}
