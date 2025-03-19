<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('commandes', CommandeController::class);
    Route::post('/commandes/verifier-disponibilite', [CommandeController::class, 'verifierDisponibilite'])->name('commandes.verifier-disponibilite');
    Route::post('/commandes/creneaux-reserves', [CommandeController::class, 'getCreneauxReserves'])->name('commandes.creneaux-reserves');

    Route::get('/produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::get('/produits/{produit}', [ProduitController::class, 'show'])->name('produits.show')
        ->where('produit', '[0-9]+');
});

Route::middleware(['auth', 'role:ROLE_LOUEUR,ROLE_ADMIN'])->group(function () {
    Route::get('/produits/create', [ProduitController::class, 'create'])->name('produits.create');
    Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
    Route::get('/produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
    Route::put('/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
    Route::delete('/produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');
});


Route::middleware(['auth', 'role:ROLE_ADMIN'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('clients', ClientController::class);
});


Route::group(['prefix' => 'api'], function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('produits', [ProduitController::class, 'apiIndex']);
    Route::post('auth/register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::get('produits/{produit}', [ProduitController::class, 'apiShow']);

        Route::post('commandes', [CommandeController::class, 'apiStore']);
        Route::get('commandes_complete/{id}', [CommandeController::class, 'getCommandeComplete']);
        Route::get('commandes_complete', [CommandeController::class, 'getAllCommandesComplete']);
        Route::post('commandes/verifier-disponibilite', [CommandeController::class, 'apiVerifierDisponibilite']);
        Route::post('commandes/create', [CommandeController::class, 'apiStore']);


        Route::middleware(['role:ROLE_LOUEUR,ROLE_ADMIN'])->group(function () {
            Route::post('produits/create', [ProduitController::class, 'apiStore']);
            Route::post('produits/update/{produit}', [ProduitController::class, 'apiUpdate']);
            Route::delete('produits/{produit}', [ProduitController::class, 'apiDestroy']);
            Route::get('categories', [CategoryController::class, 'apiIndex']);
        });

        Route::middleware(['role:ROLE_ADMIN'])->group(function () {
            Route::post('categories', [CategoryController::class, 'apiStore']);
            Route::get('categories/{category}', [CategoryController::class, 'apiShow']);
            Route::put('categories/{category}', [CategoryController::class, 'apiUpdate']);
            Route::delete('categories/{category}', [CategoryController::class, 'apiDestroy']);
        });
    });
});

require __DIR__ . '/auth.php';
