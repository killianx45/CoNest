@extends('template')

@section('title', 'Produit')

@section('content')

<div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
  <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Produit</h1>

  <div class="grid grid-cols-1 gap-4">
    <div class="p-4 rounded-md bg-orange-50">
      <p class="text-black"><span class="font-semibold">Nom:</span> {{ $produit->nom }}</p>
    </div>
    <div class="p-4 rounded-md bg-orange-50">
      <p class="text-black"><span class="font-semibold">Description:</span> {{ $produit->description }}</p>
    </div>
    <div class="p-4 rounded-md bg-orange-50">
      <p class="text-black"><span class="font-semibold">Prix:</span> {{ $produit->prix }}</p>
    </div>
    <div class="p-4 rounded-md bg-orange-50">
      <img src="{{ asset('/' . $produit->image) }}" alt="Image du produit" class="object-cover w-full h-48 rounded-md">
    </div>
    <div class="p-4 rounded-md bg-orange-50">
      <p class="text-black"><span class="font-semibold">Categorie:</span> {{ $produit->categories->pluck('name')->implode(', ') }}</p>
    </div>
    <div class="p-4 rounded-md bg-orange-50">
      <p class="text-black"><span class="font-semibold">Disponibilite:</span> {{ $produit->disponibilite }}</p>
    </div>
  </div>
</div>

@endsection