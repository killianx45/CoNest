<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes accessibles à tous les utilisateurs authentifiés
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes pour les commandes (tous les utilisateurs authentifiés)
    Route::resource('commandes', CommandeController::class);
    Route::post('/commandes/verifier-disponibilite', [CommandeController::class, 'verifierDisponibilite'])->name('commandes.verifier-disponibilite');
    Route::post('/commandes/creneaux-reserves', [CommandeController::class, 'getCreneauxReserves'])->name('commandes.creneaux-reserves');

    // Routes pour les produits (lecture seule pour ROLE_USER)
    Route::get('/produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::get('/produits/{produit}', [ProduitController::class, 'show'])->name('produits.show')
        ->where('produit', '[0-9]+');
});

// Routes accessibles aux loueurs et admins
Route::middleware(['auth', 'role:ROLE_LOUEUR,ROLE_ADMIN'])->group(function () {
    // Gestion complète des produits pour les loueurs
    Route::get('/produits/create', [ProduitController::class, 'create'])->name('produits.create');
    Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
    Route::get('/produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
    Route::put('/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
    Route::delete('/produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');
});

// Routes accessibles uniquement aux admins
Route::middleware(['auth', 'role:ROLE_ADMIN'])->group(function () {
    // Gestion des catégories
    Route::resource('categories', CategoryController::class);
});

require __DIR__ . '/auth.php';
