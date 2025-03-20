<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    /**
     * Display the client dashboard with their reservations.
     */
    public function index(): View
    {
        $clients = User::all();

        return view('clients.index', compact('clients'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $client = User::findOrFail($id);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès');
    }

    public function dashboard(): View
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

    /**
     * Get the concours status for the authenticated user.
     */
    public function getConcoursStatus()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        return response()->json([
            'concours' => (bool) $user->concours,
            'user' => $user->name
        ]);
    }

    /**
     * Update the concours status for the authenticated user.
     */
    public function updateConcoursStatus()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $client = new Client();
        $client->ApiConcours();
        $updatedUser = User::find($user->id);

        return response()->json([
            'concours' => (bool) $updatedUser->concours,
            'message' => 'Statut du concours mis à jour avec succès'
        ]);
    }

    /**
     * Get the number of users eligible for the contest.
     */
    public function getConcoursEligibleCount()
    {
        $count = User::where('concours', 1)->count();

        return response()->json([
            'count' => $count
        ]);
    }
}
