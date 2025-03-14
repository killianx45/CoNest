<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produits = Produit::all();
        $categories = Category::all();
        return view('produits.index', compact('produits', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('produits.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $produit = new Produit();
        $produit->nom = $request->nom;
        $produit->description = $request->description;
        $produit->prix = $request->prix;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            if (!file_exists(resource_path('images'))) {
                mkdir(resource_path('images'), 0777, true);
            }
            copy(public_path('images/' . $imageName), resource_path('images/' . $imageName));
            $produit->image = 'images/' . $imageName;
        }


        $produit->disponibilite = $request->date_debut . '-' . $request->date_fin;
        $produit->id_user = Auth::id();
        $produit->save();
        $produit->categories()->attach($request->categories);
        return redirect()->route('produits.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produit = Produit::findOrFail($id);
        return view('produits.show', compact('produit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produit = Produit::findOrFail($id);
        if (Auth::user()->role === 'ROLE_USER') {
            abort(403, 'Accès non autorisé');
        }
        $categories = Category::all();
        return view('produits.edit', compact('produit', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $produit = Produit::findOrFail($id);
        $produit->nom = $request->nom;
        $produit->description = $request->description;
        $produit->prix = $request->prix;

        // Formater les dates pour la disponibilité
        if ($request->date_debut && $request->date_fin) {
            $produit->disponibilite = $request->date_debut . '-' . $request->date_fin;
        } else {
            $produit->disponibilite = null;
        }

        $produit->id_user = Auth::id();

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($produit->image && file_exists(public_path($produit->image))) {
                unlink(public_path($produit->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            if (!file_exists(resource_path('images'))) {
                mkdir(resource_path('images'), 0777, true);
            }
            copy(public_path('images/' . $imageName), resource_path('images/' . $imageName));
            $produit->image = 'images/' . $imageName;
        }

        $produit->save();
        $produit->categories()->attach($request->categories);
        return redirect()->route('produits.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produit = Produit::findOrFail($id);
        if ($produit->image && file_exists(public_path($produit->image))) {
            unlink(public_path($produit->image));
            if (file_exists(resource_path('images/' . basename($produit->image)))) {
                unlink(resource_path('images/' . basename($produit->image)));
            }
        }

        $commandes = $produit->commandes()->get();
        foreach ($commandes as $commande) {
            $commande->delete();
        }

        $produit->delete();
        return redirect()->route('produits.index');
    }

    /**
     * API: Store a newly created resource in storage.
     */
    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required',
                'description' => 'required',
                'prix' => 'required|numeric',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'categories' => 'required|array',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            $produit = new Produit();
            $produit->nom = $request->nom;
            $produit->description = $request->description;
            $produit->prix = $request->prix;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                if (!file_exists(resource_path('images'))) {
                    mkdir(resource_path('images'), 0777, true);
                }
                copy(public_path('images/' . $imageName), resource_path('images/' . $imageName));
                $produit->image = 'images/' . $imageName;
            }

            $produit->disponibilite = $request->date_debut . '-' . $request->date_fin;
            $produit->id_user = Auth::id();
            $produit->save();

            if ($request->has('categories')) {
                $produit->categories()->attach($request->categories);
            }

            // Récupérer le produit avec ses relations
            $produit = Produit::with('categories')->find($produit->id);

            return response()->json($produit);
        } catch (\Exception $e) {
            error_log('Erreur lors de la création du produit: ' . $e->getMessage());
            error_log($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du produit: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * API: Update the specified resource in storage (simplified version).
     */
    public function apiUpdate(Request $request, string $id)
    {
        try {
            // Trouver le produit
            $produit = Produit::findOrFail($id);

            // Vérifier les permissions
            if (Auth::id() !== $produit->id_user && Auth::user()->role !== 'ROLE_ADMIN' && Auth::user()->role !== 'ROLE_LOUEUR') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier ce produit.'
                ], 403);
            }

            // Mettre à jour les champs de base s'ils sont présents
            if ($request->has('nom')) {
                $produit->nom = $request->input('nom');
            }

            if ($request->has('description')) {
                $produit->description = $request->input('description');
            }

            if ($request->has('prix')) {
                $produit->prix = $request->input('prix');
            }

            if ($request->has('date_debut') && $request->has('date_fin')) {
                $produit->disponibilite = $request->input('date_debut') . '-' . $request->input('date_fin');
            }

            // Gérer l'image si elle a été modifiée
            if ($request->has('image_changed') && $request->input('image_changed') == '1' && $request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($produit->image && file_exists(public_path($produit->image))) {
                    @unlink(public_path($produit->image));
                }

                // Enregistrer la nouvelle image
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $produit->image = 'images/' . $imageName;
            }

            // Sauvegarder le produit
            $produit->save();

            // Gérer les catégories si elles sont présentes
            if ($request->has('categories')) {
                // Détacher toutes les catégories existantes
                $produit->categories()->detach();

                // Récupérer les catégories
                $categories = $request->input('categories');

                // Si c'est une chaîne JSON, la décoder
                if (is_string($categories) && json_decode($categories) !== null) {
                    $categories = json_decode($categories);
                }

                // Si c'est un tableau, attacher chaque catégorie
                if (is_array($categories)) {
                    foreach ($categories as $categoryId) {
                        $produit->categories()->attach((int)$categoryId);
                    }
                }
            }

            // Récupérer le produit mis à jour avec ses relations
            $updatedProduit = Produit::with('categories')->find($produit->id);

            return response()->json($updatedProduit);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du produit: ' . $e->getMessage()
            ], 500);
        }
    }
}
