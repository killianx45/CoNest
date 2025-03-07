<?php

namespace App\Http\Controllers;


use App\Models\Commande;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        $commandes = Commande::with('produits')->where('id_user', Auth::id())->get();
        $date_reservation = null;
        $heure_debut = null;
        $heure_fin = null;
        if ($commandes->isNotEmpty()) {
            $commande = $commandes->first();
            if ($commande->produits->isNotEmpty()) {
                $reservation = $commande->produits->first()->pivot;
                $date_reservation = $reservation->date_reservation;
                $heure_debut = $reservation->heure_debut;
                $heure_fin = $reservation->heure_fin;
            }
        }

        return view('dashboard', compact('commandes', 'date_reservation', 'heure_debut', 'heure_fin'));
    }
}
