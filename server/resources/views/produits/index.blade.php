@extends('template')

@section('title', 'Produits')

@section('content')
<div class="p-6 bg-white rounded-lg">
  <h1 class="mb-6 text-3xl font-bold text-black">Produits</h1>
  @can('manage-products')
  <a href="{{ route('produits.create') }}" class="inline-block px-4 py-2 mb-4 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Ajouter un produit</a>
  @endcan
  @cannot('manage-products')
  <p>Cette section est réservée aux administrateurs.</p>
  @endcannot
  <div class="overflow-x-auto">
    <table class="w-full text-black bg-white border-collapse">
      <thead>
        <tr class="border-b border-orange-300">
          <th class="p-3 font-semibold text-left">Nom</th>
          <th class="p-3 font-semibold text-left">Description</th>
          <th class="p-3 font-semibold text-left">Prix</th>
          <th class="p-3 font-semibold text-left">Image</th>
          <th class="p-3 font-semibold text-left">Categorie</th>
          <th class="p-3 font-semibold text-left">Disponibilite</th>
          <th class="p-3 font-semibold text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($produits as $produit)
        <tr class="border-b border-orange-300 hover:bg-orange-50">
          <td class="p-3">{{ $produit->nom }}</td>
          <td class="p-3">{{ $produit->description }}</td>
          <td class="p-3">{{ $produit->prix }}</td>
          <td class="p-3"><img src="{{ asset('/' . $produit->image) }}" alt="Image du produit" class="object-cover w-24 h-24 border border-orange-300 rounded"></td>
          <td class="p-3">{{ $produit->categories->pluck('name')->implode(', ') }}</td>
          <td class="p-3">{{ $produit->disponibilite }}</td>
          <td class="p-3 space-y-2">
            <a href="{{ route('produits.show', $produit->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Voir</a>
            @can('manage-products')
            <a href="{{ route('produits.edit', $produit->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Modifier</a>
            @endcan
            @cannot('manage-products')
            <p>Cette section est réservée aux administrateurs.</p>
            @endcannot
            @can('manage-products')
            <form action="{{ route('produits.destroy', $produit->id) }}" method="POST" class="inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="px-3 py-1 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Supprimer</button>
            </form>
            @endcan
            @cannot('manage-products')
            <p>Cette section est réservée aux administrateurs.</p>
            @endcannot
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection