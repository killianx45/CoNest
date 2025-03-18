@extends('template')

@section('content')
<div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
  <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Modifier le produit</h1>

  <form action="{{ route('produits.update', $produit->id) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-4">
      <label for="nom" class="block mb-2 font-semibold text-black">Nom</label>
      <input type="text" name="nom" id="nom" value="{{ $produit->nom }}" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">
    </div>

    <div class="mb-4">
      <label for="description" class="block mb-2 font-semibold text-black">Description</label>
      <textarea name="description" id="description" class="w-full h-32 p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">{{ $produit->description }}</textarea>
    </div>

    <div class="mb-4">
      <label for="prix" class="block mb-2 font-semibold text-black">Prix</label>
      <input type="number" name="prix" id="prix" value="{{ $produit->prix }}" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">
    </div>

    <div class="mb-4">
      <label for="images" class="block mb-2 font-semibold text-black">Images</label>
      <input type="file" name="images[]" id="images" class="w-full p-2 bg-white border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" multiple>
    </div>

    <div class="mb-4">
      <label for="categories" class="block mb-2 font-semibold text-black">Catégories</label>
      <select name="categories[]" id="categories" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" multiple>
        @foreach ($categories as $categorie)
        <option value="{{ $categorie->id }}">{{ $categorie->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-4">
      <label for="disponibilite" class="block mb-2 font-semibold text-black">Disponibilité</label>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label for="date_debut" class="block mb-1 text-sm font-medium text-gray-700">Date de début</label>
          @php
          $dates = explode('-', $produit->disponibilite);
          $dateDebut = isset($dates[0]) ? $dates[0] : '';
          $dateFin = isset($dates[1]) ? $dates[1] : '';
          @endphp
          <input type="date" name="date_debut" id="date_debut" value="{{ $dateDebut }}" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" required>
        </div>
        <div>
          <label for="date_fin" class="block mb-1 text-sm font-medium text-gray-700">Date de fin</label>
          <input type="date" name="date_fin" id="date_fin" value="{{ $dateFin }}" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" required>
        </div>
      </div>
      <p class="mt-1 text-sm text-gray-500">La période de disponibilité sera enregistrée au format "JJ/MM/AAAA-JJ/MM/AAAA"</p>
    </div>

    <button type="submit" class="px-4 py-2 font-semibold text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600">Modifier</button>
  </form>
</div>

@endsection