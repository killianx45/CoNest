<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'ROLE_USER') {
            $commandes = Commande::with(['produits', 'client'])->where('id_user', $user->id)->get();
        } else {
            $commandes = Commande::with(['produits', 'client'])->get();
        }

        // Récupérer le nom de l'utilisateur qui a fait la commande à partir de la relation client
        // au lieu du user courant

        return view('commandes.index', compact('commandes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produits = Produit::all();
        return view('commandes.create', compact('produits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produits' => 'required|array',
            'produits.*' => 'exists:produits,id',
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'heures_debut' => 'required|array',
            'heures_debut.*' => 'required',
            'heures_fin' => 'required|array',
            'heures_fin.*' => 'required',
        ]);

        $produits = $request->produits;
        $dates = $request->dates;
        $heuresDebut = $request->heures_debut;
        $heuresFin = $request->heures_fin;
        $erreur = $this->verifierDisponibiliteTousCreneaux($produits, $dates, $heuresDebut, $heuresFin);
        if ($erreur) {
            return back()->withErrors(['message' => $erreur])->withInput();
        }

        $commande = new Commande();
        $commande->id_user = Auth::id();
        $commande->prix = 0;
        $commande->save();

        $this->attacherProduitsEtCalculerPrix($commande, $produits, $dates, $heuresDebut, $heuresFin);

        return redirect()->route('commandes.index')->with('success', 'Réservation créée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $commande = Commande::with('produits')->findOrFail($id);
        $user_name = $commande->client->name;
        $reservation = $commande->produits->first()->pivot;
        $date_reservation = $reservation->date_reservation;
        $heure_debut = $reservation->heure_debut;
        $heure_fin = $reservation->heure_fin;

        return view('commandes.show', compact('commande', 'user_name', 'date_reservation', 'heure_debut', 'heure_fin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $commande = Commande::with('produits')->findOrFail($id);
        $produits = Produit::all();
        return view('commandes.edit', compact('commande', 'produits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'produits' => 'required|array',
            'produits.*' => 'exists:produits,id',
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'heures_debut' => 'required|array',
            'heures_debut.*' => 'required',
            'heures_fin' => 'required|array',
            'heures_fin.*' => 'required',
        ]);

        $commande = Commande::findOrFail($id);

        $produits = $request->produits;
        $dates = $request->dates;
        $heuresDebut = $request->heures_debut;
        $heuresFin = $request->heures_fin;

        $erreur = $this->verifierDisponibiliteTousCreneaux($produits, $dates, $heuresDebut, $heuresFin, $id);
        if ($erreur) {
            return back()->withErrors(['message' => $erreur])->withInput();
        }

        $commande->produits()->detach();

        $this->attacherProduitsEtCalculerPrix($commande, $produits, $dates, $heuresDebut, $heuresFin);

        return redirect()->route('commandes.index')->with('success', 'Réservation mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $commande = Commande::findOrFail($id);
        $commande->delete();
        return redirect()->route('commandes.index')->with('success', 'Réservation supprimée avec succès');
    }

    /**
     * Vérifie la disponibilité de tous les créneaux demandés
     */
    private function verifierDisponibiliteTousCreneaux($produits, $dates, $heuresDebut, $heuresFin, $commandeId = null)
    {
        for ($i = 0; $i < count($produits); $i++) {
            $produit = Produit::find($produits[$i]);
            $date = $dates[$i];
            $heureDebut = $heuresDebut[$i];
            $heureFin = $heuresFin[$i];
            if (!$produit->estDansDisponibilite($date)) {
                return "Le produit '{$produit->nom}' n'est pas disponible à la date sélectionnée.";
            }
            if (!$produit->estDisponible($date, $heureDebut, $heureFin)) {
                return "Le créneau horaire demandé pour '{$produit->nom}' est déjà réservé.";
            }
        }

        return null;
    }

    /**
     * Attache les produits à la commande et calcule le prix total
     */
    private function attacherProduitsEtCalculerPrix($commande, $produits, $dates, $heuresDebut, $heuresFin)
    {
        $prixTotal = 0;

        for ($i = 0; $i < count($produits); $i++) {
            $produit = Produit::find($produits[$i]);
            $date = $dates[$i];
            $heureDebut = $heuresDebut[$i];
            $heureFin = $heuresFin[$i];
            $commande->produits()->attach($produit->id, [
                'date_reservation' => $date,
                'heure_debut' => $heureDebut,
                'heure_fin' => $heureFin
            ]);

            $heureDebutObj = new \DateTime($heureDebut);
            $heureFinObj = new \DateTime($heureFin);
            $diff = $heureDebutObj->diff($heureFinObj);
            $heures = $diff->h + ($diff->i / 60);
            $prixTotal += $produit->prix * $heures;
        }

        $commande->prix = $prixTotal;
        $commande->save();
    }
}
