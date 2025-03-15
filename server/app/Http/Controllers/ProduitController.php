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
        $categories = Category::all();
        return view('produits.edit', compact('produit', 'categories'));
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

        if ($validated['date_debut'] && $validated['date_fin']) {
            $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
        } else {
            $produit->disponibilite = null;
        }

        $produit->id_user = Auth::id();

        if ($request->hasFile('image')) {
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
            if (file_exists(resource_path('images/' . basename($produit->image)))) {
                unlink(resource_path('images/' . basename($produit->image)));
            }
        }

        $commandes = $produit->commandes()->get();
        foreach ($commandes as $commande) {
            $commande->delete();
        }

        $produit->delete();
        return redirect()->route('produits.index')->with('success', 'Produit supprimé avec succès');
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

            if (isset($validated['date_debut']) && isset($validated['date_fin'])) {
                $produit->disponibilite = $validated['date_debut'] . '-' . $validated['date_fin'];
            }

            if (isset($validated['image_changed']) && $validated['image_changed'] == '1' && $request->hasFile('image')) {
                if ($produit->image && file_exists(public_path($produit->image))) {
                    @unlink(public_path($produit->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $produit->image = 'images/' . $imageName;
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
