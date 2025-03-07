@extends('template')

@section('title', 'Commandes')

@section('content')
<div class="p-6 bg-white rounded-lg">
  <h1 class="mb-6 text-3xl font-bold text-black">Commandes</h1>
  <a href="{{ route('commandes.create') }}" class="inline-block px-4 py-2 mb-4 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Ajouter une commande</a>
  <div class="overflow-x-auto">
    <table class="w-full text-black bg-white border-collapse">
      <thead>
        <tr class="border-b border-orange-300">
          <th class="p-3 font-semibold text-left">Prix</th>
          <th class="p-3 font-semibold text-left">Utilisateur</th>
          <th class="p-3 font-semibold text-left">Produits</th>
          <th class="p-3 font-semibold text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($commandes as $commande)
        <tr class="border-b border-orange-300 hover:bg-orange-50">
          <td class="p-3">{{ $commande->prix }}€</td>
          <td class="p-3">{{ $commande->client->name }}</td>
          <td class="p-3">
            @if($commande->produits)
            @foreach($commande->produits as $produit)
            <div>{{ $produit->nom }}</div>
            @endforeach
            @else
            -
            @endif
          </td>
          <td class="p-3 space-y-2">
            <a href="{{ route('commandes.show', $commande->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Voir</a>
            <a href="{{ route('commandes.edit', $commande->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Modifier</a>
            <form action="{{ route('commandes.destroy', $commande->id) }}" method="POST" class="inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="px-3 py-1 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Supprimer</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection