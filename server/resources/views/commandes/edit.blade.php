@extends('template')

@section('title', 'Modifier une réservation')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white border-2 border-orange-300 rounded-lg shadow-md">
  <h1 class="text-3xl font-bold mb-6 text-black border-b-2 border-orange-200 pb-2">Modifier la réservation #{{ $commande->id }}</h1>

  @if ($errors->any())
  <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
    <ul class="list-disc pl-4">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <form action="{{ route('commandes.update', $commande->id) }}" method="POST" id="commande-form" class="space-y-4">
    @csrf
    @method('PUT')

    <div class="bg-white border border-orange-200 rounded-lg p-4 mb-4">
      <h2 class="text-xl font-semibold mb-4 text-orange-600">Sélection des espaces de travail</h2>
      <div id="produits-container" class="space-y-4">
        @forelse($commande->produits as $index => $produit)
        <div class="reservation-row border border-gray-200 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="produits[{{ $index }}]" class="block text-sm font-medium text-gray-700 mb-1">Espace de travail</label>
              <select class="produit-select w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="produits[]" required data-index="{{ $index }}">
                <option value="">Sélectionnez un espace</option>
                @foreach($produits as $p)
                <option value="{{ $p->id }}" data-prix="{{ $p->prix }}" {{ $p->id == $produit->id ? 'selected' : '' }}>
                  {{ $p->nom }} - {{ $p->prix }}€/heure
                </option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="dates[{{ $index }}]" class="block text-sm font-medium text-gray-700 mb-1">Date de réservation</label>
              <input type="date" class="date-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="dates[]" value="{{ $produit->pivot->date_reservation }}" required data-index="{{ $index }}" min="{{ date('Y-m-d') }}">
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
            <div>
              <label for="heures_debut[{{ $index }}]" class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
              <input type="time" class="heure-debut-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_debut[]" value="{{ $produit->pivot->heure_debut }}" required data-index="{{ $index }}" min="08:00" max="20:00" step="1800">
            </div>
            <div>
              <label for="heures_fin[{{ $index }}]" class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
              <input type="time" class="heure-fin-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_fin[]" value="{{ $produit->pivot->heure_fin }}" required data-index="{{ $index }}" min="09:00" max="21:00" step="1800">
            </div>
          </div>
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500 creneaux-info">
              Créneau actuellement réservé
            </div>
            <button type="button" class="remove-produit bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-sm" {{ $index == 0 && count($commande->produits) == 1 ? 'style=display:none' : '' }}>Supprimer</button>
          </div>
        </div>
        @empty
        <div class="reservation-row border border-gray-200 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="produits[0]" class="block text-sm font-medium text-gray-700 mb-1">Espace de travail</label>
              <select class="produit-select w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="produits[]" required data-index="0">
                <option value="">Sélectionnez un espace</option>
                @foreach($produits as $produit)
                <option value="{{ $produit->id }}" data-prix="{{ $produit->prix }}">
                  {{ $produit->nom }} - {{ $produit->prix }}€/heure
                </option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="dates[0]" class="block text-sm font-medium text-gray-700 mb-1">Date de réservation</label>
              <input type="date" class="date-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="dates[]" required data-index="0" min="{{ date('Y-m-d') }}">
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
            <div>
              <label for="heures_debut[0]" class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
              <input type="time" class="heure-debut-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_debut[]" required data-index="0" min="08:00" max="20:00" step="1800">
            </div>
            <div>
              <label for="heures_fin[0]" class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
              <input type="time" class="heure-fin-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_fin[]" required data-index="0" min="09:00" max="21:00" step="1800">
            </div>
          </div>
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500 creneaux-info">
              Sélectionnez une date et un espace pour voir les créneaux disponibles
            </div>
            <button type="button" class="remove-produit bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-sm" style="display: none;">Supprimer</button>
          </div>
        </div>
        @endforelse
      </div>
    </div>

    <div class="bg-white border border-orange-200 rounded-lg p-4 mb-4">
      <h2 class="text-xl font-semibold mb-4 text-orange-600">Récapitulatif</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-gray-700">Nombre d'espaces réservés: <span id="nombre-espaces">{{ count($commande->produits) }}</span></p>
        </div>
        <div>
          <p class="text-gray-700">Prix total: <span id="prix-total">{{ $commande->prix }}</span>€</p>
        </div>
      </div>
    </div>

    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-md">Mettre à jour la réservation</button>
  </form>
</div>

@endsection