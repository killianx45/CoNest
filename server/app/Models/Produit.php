<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Produit extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'prix', 'image', 'categorie', 'disponibilite', 'id_user', 'adresse'];

    protected $appends = ['images'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_produit', 'produit_id', 'commande_id')
            ->withPivot('date_reservation', 'heure_debut', 'heure_fin');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function estDansDisponibilite($date)
    {
        if (empty($this->disponibilite)) {
            return false;
        }
        $dates = explode('-', $this->disponibilite);
        if (count($dates) < 2) {
            return false;
        }
        try {
            if (count($dates) === 6) {
                $dateDebut = Carbon::parse($dates[0] . '-' . $dates[1] . '-' . $dates[2]);
                $dateFin = Carbon::parse($dates[3] . '-' . $dates[4] . '-' . $dates[5]);
            } else {
                $dateDebut = Carbon::parse($dates[0]);
                $dateFin = Carbon::parse($dates[1]);
            }

            $dateReservation = Carbon::parse($date);
            return $dateReservation->between($dateDebut, $dateFin);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function estDisponible($date, $heureDebut, $heureFin)
    {
        if (!$this->estDansDisponibilite($date)) {
            return false;
        }

        return Commande::estDisponible($this->id, $date, $heureDebut, $heureFin);
    }

    public function getCreneauxReserves($date)
    {
        return $this->commandes()
            ->wherePivot('date_reservation', $date)
            ->get()
            ->map(function ($commande) {
                return [
                    'heure_debut' => $commande->pivot->heure_debut,
                    'heure_fin' => $commande->pivot->heure_fin
                ];
            });
    }

    public function getImagesAttribute()
    {
        if (empty($this->image)) {
            return [];
        }
        return explode(',', $this->image);
    }

    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['image'] = implode(',', $value);
        } else {
            $this->attributes['image'] = $value;
        }
    }
}
