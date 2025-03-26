<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreProduitRequest;
use App\Http\Requests\UpdateProduitRequest;
use App\Http\Requests\ApiUpdateProduitRequest;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $produits = Produit::all();
        $categories = Category::all();
        return view('produits.index', compact('produits', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::all();
        return view('produits.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProduitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $produit = new Produit();
        $produit->nom = $validated['nom'];
        $produit->description = $validated['description'];
        $produit->prix = $validated['prix'];
        $produit->adresse = $validated['adresse'];
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imagePaths = [];
            foreach ($images as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $imagePaths[] = 'images/' . $imageName;
            }
            $produit->image = implode(',', $imagePaths);
        }

        $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
        $produit->id_user = Auth::id();
        $produit->save();
        $produit->categories()->attach($validated['categories']);

        return redirect()->route('produits.index')->with('success', 'Produit créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $produit = Produit::findOrFail($id);
        return view('produits.show', compact('produit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $produit = Produit::findOrFail($id);
        if (Auth::user()->role === 'ROLE_USER') {
            abort(403, 'Accès non autorisé');
        }

        list($dateDebut, $dateFin) = $this->extraireDatesDisponibilite($produit->disponibilite);

        $categories = Category::all();
        return view('produits.edit', compact('produit', 'categories', 'dateDebut', 'dateFin'));
    }

    /**
     * Extrait les dates de début et de fin à partir de la chaîne de disponibilité
     * 
     * @param string|null $disponibilite
     * @return array [dateDebut, dateFin]
     */
    private function extraireDatesDisponibilite(?string $disponibilite): array
    {
        $dateDebut = '';
        $dateFin = '';

        if (!empty($disponibilite)) {
            try {
                if (preg_match('/^(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})$/', $disponibilite, $matches)) {
                    $dateDebut = $matches[1];
                    $dateFin = $matches[2];
                } else {
                    $disponibiliteParts = explode('-', $disponibilite);

                    if (count($disponibiliteParts) === 6) {
                        $dateDebut = $disponibiliteParts[0] . '-' . $disponibiliteParts[1] . '-' . $disponibiliteParts[2];
                        $dateFin = $disponibiliteParts[3] . '-' . $disponibiliteParts[4] . '-' . $disponibiliteParts[5];
                    } else if (count($disponibiliteParts) === 2) {
                        if (isset($disponibiliteParts[0])) {
                            $dateTemp = trim($disponibiliteParts[0]);
                            if (strpos($dateTemp, '/') !== false) {
                                $dateParts = explode('/', $dateTemp);
                                if (count($dateParts) === 3) {
                                    $dateDebut = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                                }
                            } else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTemp)) {
                                $dateDebut = $dateTemp;
                            } else {
                                try {
                                    $date = \Carbon\Carbon::parse($dateTemp);
                                    $dateDebut = $date->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $dateDebut = '';
                                }
                            }
                        }

                        if (isset($disponibiliteParts[1])) {
                            $dateTemp = trim($disponibiliteParts[1]);
                            if (strpos($dateTemp, '/') !== false) {
                                $dateParts = explode('/', $dateTemp);
                                if (count($dateParts) === 3) {
                                    $dateFin = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                                }
                            } else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTemp)) {
                                $dateFin = $dateTemp;
                            } else {
                                try {
                                    $date = \Carbon\Carbon::parse($dateTemp);
                                    $dateFin = $date->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $dateFin = '';
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return [$dateDebut, $dateFin];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProduitRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $produit = Produit::findOrFail($id);
        $produit->nom = $validated['nom'];
        $produit->description = $validated['description'];
        $produit->prix = $validated['prix'];
        $produit->adresse = $validated['adresse'];
        if ($validated['date_debut'] && $validated['date_fin']) {
            $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
        } else {
            $produit->disponibilite = null;
        }

        $produit->id_user = Auth::id();

        if ($request->hasFile('images')) {
            if ($produit->image) {
                $oldImages = explode(',', $produit->image);
                foreach ($oldImages as $oldImage) {
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
            }

            $images = $request->file('images');
            $imagePaths = [];
            foreach ($images as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $imagePaths[] = 'images/' . $imageName;
            }
            $produit->image = implode(',', $imagePaths);
        }

        $produit->save();
        $produit->categories()->sync($validated['categories']);
        return redirect()->route('produits.index')->with('success', 'Produit mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $produit = Produit::findOrFail($id);
        if ($produit->image && file_exists(public_path($produit->image))) {
            unlink(public_path($produit->image));
        }

        $commandes = $produit->commandes()->get();
        foreach ($commandes as $commande) {
            $commande->delete();
        }

        $produit->delete();
        return redirect()->route('produits.index')->with('success', 'Produit supprimé avec succès');
    }

    /**
     * API: Get all products with their categories
     */
    public function apiIndex(): JsonResponse
    {
        $produits = Produit::with('categories')->get();
        return response()->json([
            '@context' => '/api/contexts/Produit',
            '@id' => '/api/produits',
            '@type' => 'hydra:Collection',
            'totalItems' => count($produits),
            'member' => $produits
        ]);
    }

    /**
     * API: Store a newly created resource in storage.
     */
    public function apiStore(StoreProduitRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $produit = new Produit();
            $produit->nom = $validated['nom'];
            $produit->description = $validated['description'];
            $produit->prix = $validated['prix'];
            $produit->adresse = $validated['adresse'];
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $imagePaths = [];
                foreach ($images as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('images'), $imageName);
                    $imagePaths[] = 'images/' . $imageName;
                }
                $produit->image = implode(',', $imagePaths);
            }

            $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
            $produit->id_user = Auth::id();
            $produit->save();

            if (isset($validated['categories'])) {
                $produit->categories()->attach($validated['categories']);
            }

            $produit = Produit::with('categories')->find($produit->id);

            return response()->json($produit);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Update the specified resource in storage.
     */
    public function apiUpdate(ApiUpdateProduitRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $produit = Produit::findOrFail($id);

            if (isset($validated['nom'])) {
                $produit->nom = $validated['nom'];
            }

            if (isset($validated['description'])) {
                $produit->description = $validated['description'];
            }

            if (isset($validated['prix'])) {
                $produit->prix = $validated['prix'];
            }

            if (isset($validated['adresse'])) {
                $produit->adresse = $validated['adresse'];
            }

            if (isset($validated['date_debut']) && isset($validated['date_fin'])) {
                $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
            }

            if (isset($validated['image_changed']) && $validated['image_changed'] == '1' && $request->hasFile('images')) {
                if ($produit->image) {
                    $oldImages = explode(',', $produit->image);
                    foreach ($oldImages as $oldImage) {
                        if (file_exists(public_path($oldImage))) {
                            @unlink(public_path($oldImage));
                        }
                    }
                }

                $images = $request->file('images');
                $imagePaths = [];
                foreach ($images as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('images'), $imageName);
                    $imagePaths[] = 'images/' . $imageName;
                }
                $produit->image = implode(',', $imagePaths);
            }

            $produit->save();

            if (isset($validated['categories'])) {
                $produit->categories()->detach();
                $categories = $validated['categories'];

                if (is_string($categories) && json_decode($categories) !== null) {
                    $categories = json_decode($categories);
                }

                if (is_array($categories)) {
                    $produit->categories()->attach($categories);
                }
            }

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
