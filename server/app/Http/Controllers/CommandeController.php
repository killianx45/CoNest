<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    /**
     * API: Get all commandes with complete pivot data
     */
    public function getAllCommandesComplete()
    {
        $user = Auth::user();
        if ($user->role === 'ROLE_USER') {
            $commandes = Commande::with(['produits' => function ($query) {
                $query->withPivot('date_reservation', 'heure_debut', 'heure_fin');
            }, 'client'])->where('id_user', $user->id)->get();
        } else {
            $commandes = Commande::with(['produits' => function ($query) {
                $query->withPivot('date_reservation', 'heure_debut', 'heure_fin');
            }, 'client'])->get();
        }
        $results = [];
        foreach ($commandes as $commande) {
            $produits = [];
            foreach ($commande->produits as $produit) {
                $produitData = $produit->toArray();
                $produitData['pivot'] = [
                    'date_reservation' => $produit->pivot->date_reservation,
                    'heure_debut' => $produit->pivot->heure_debut,
                    'heure_fin' => $produit->pivot->heure_fin
                ];
                $produits[] = $produitData;
            }

            $results[] = [
                'id' => $commande->id,
                'prix' => $commande->prix,
                'id_user' => $commande->id_user,
                'client' => $commande->client ? $commande->client->toArray() : null,
                'produits' => $produits,
                'createdAt' => $commande->created_at,
                'updatedAt' => $commande->updated_at
            ];
        }

        return response()->json([
            '@context' => '/api/contexts/Commande',
            '@id' => '/api/commandes',
            '@type' => 'Collection',
            'totalItems' => count($results),
            'member' => $results
        ]);
    }

    /**
     * API: Get a complete commande with pivot data
     */
    public function getCommandeComplete(string $id)
    {
        $commande = Commande::with(['produits' => function ($query) {
            $query->withPivot('date_reservation', 'heure_debut', 'heure_fin');
        }, 'client'])->findOrFail($id);

        $produits = [];
        foreach ($commande->produits as $produit) {
            $produitData = $produit->toArray();
            $produitData['pivot'] = [
                'date_reservation' => $produit->pivot->date_reservation,
                'heure_debut' => $produit->pivot->heure_debut,
                'heure_fin' => $produit->pivot->heure_fin
            ];
            $produits[] = $produitData;
        }

        $result = [
            'id' => $commande->id,
            'prix' => $commande->prix,
            'id_user' => $commande->id_user,
            'client' => $commande->client ? $commande->client->toArray() : null,
            'produits' => $produits,
            'createdAt' => $commande->created_at,
            'updatedAt' => $commande->updated_at
        ];

        return response()->json($result);
    }

    /**
     * API: Vérifier la disponibilité d'un créneau horaire
     */
    public function apiVerifierDisponibilite(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
        ]);

        $produit = Produit::find($request->produit_id);
        $date = $request->date;
        $heureDebut = $request->heure_debut;
        $heureFin = $request->heure_fin;

        // Vérifier si le produit est disponible à cette date
        if (!$produit->estDansDisponibilite($date)) {
            return response()->json([
                'disponible' => false,
                'message' => "L'espace n'est pas disponible à la date sélectionnée."
            ]);
        }

        // Vérifier si le créneau horaire est disponible
        $disponible = $produit->estDisponible($date, $heureDebut, $heureFin);

        return response()->json([
            'disponible' => $disponible,
            'message' => $disponible ? "Créneau disponible" : "Ce créneau est déjà réservé."
        ]);
    }

    /**
     * API: Créer une nouvelle commande
     */
    public function apiStore(Request $request)
    {
        try {
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

            // Vérifier la disponibilité de tous les créneaux
            $erreur = $this->verifierDisponibiliteTousCreneaux($produits, $dates, $heuresDebut, $heuresFin);
            if ($erreur) {
                return response()->json([
                    'success' => false,
                    'message' => $erreur
                ], 400);
            }

            // Créer la commande
            $commande = new Commande();
            $commande->id_user = Auth::id();
            $commande->prix = 0;
            $commande->save();

            // Attacher les produits et calculer le prix
            $this->attacherProduitsEtCalculerPrix($commande, $produits, $dates, $heuresDebut, $heuresFin);

            // Récupérer la commande complète avec les relations
            $commandeComplete = Commande::with(['produits' => function ($query) {
                $query->withPivot('date_reservation', 'heure_debut', 'heure_fin');
            }, 'client'])->findOrFail($commande->id);

            // Formater la réponse
            $produitsData = [];
            foreach ($commandeComplete->produits as $produit) {
                $produitData = $produit->toArray();
                $produitData['pivot'] = [
                    'date_reservation' => $produit->pivot->date_reservation,
                    'heure_debut' => $produit->pivot->heure_debut,
                    'heure_fin' => $produit->pivot->heure_fin
                ];
                $produitsData[] = $produitData;
            }

            $result = [
                'id' => $commandeComplete->id,
                'prix' => $commandeComplete->prix,
                'id_user' => $commandeComplete->id_user,
                'client' => $commandeComplete->client ? $commandeComplete->client->toArray() : null,
                'produits' => $produitsData,
                'createdAt' => $commandeComplete->created_at,
                'updatedAt' => $commandeComplete->updated_at
            ];

            return response()->json($result);
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            error_log('Erreur lors de la création de la commande: ' . $e->getMessage());
            error_log($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la commande: ' . $e->getMessage()
            ], 500);
        }
    }
}
