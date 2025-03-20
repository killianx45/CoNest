<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * API: Display a listing of the resource.
     */
    public function apiIndex(): JsonResponse
    {
        $categories = Category::all();
        return response()->json([
            '@context' => '/api/contexts/Category',
            '@id' => '/api/categories',
            '@type' => 'hydra:Collection',
            'totalItems' => count($categories),
            'member' => $categories
        ]);
    }

    /**
     * API: Display a public listing of categories.
     */
    public function apiPublicIndex(): JsonResponse
    {
        $categories = Category::all();
        return response()->json([
            '@context' => '/api/contexts/Category',
            '@id' => '/api/categories_public',
            '@type' => 'hydra:Collection',
            'totalItems' => count($categories),
            'member' => $categories
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $category = new Category();
        $category->name = $validated['name'];
        $category->slug = Str::slug($validated['name']);
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Catégorie créée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $category = Category::findOrFail($id);
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $category = Category::findOrFail($id);
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $category = Category::findOrFail($id);
        $category->name = $validated['name'];
        $category->slug = Str::slug($validated['name']);
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Catégorie mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée avec succès');
    }
}
