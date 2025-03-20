<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Commandes') }}
    </h2>
  </x-slot>
  <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Modifier la réservation #{{ $commande->id }}</h1>

    @if ($errors->any())
    <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500">
      <ul class="pl-4 list-disc">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form action="{{ route('commandes.update', $commande->id) }}" method="POST" id="commande-form" class="space-y-4">
      @csrf
      @method('PUT')

      <div class="p-4 mb-4 bg-white border border-orange-200 rounded-lg">
        <h2 class="mb-4 text-xl font-semibold text-orange-600">Sélection des espaces de travail</h2>
        <div id="produits-container" class="space-y-4">
          @forelse($commande->produits as $index => $produit)
          <div class="p-4 border border-gray-200 rounded-lg reservation-row">
            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
              <div>
                <label for="produits[{{ $index }}]" class="block mb-1 text-sm font-medium text-gray-700">Espace de travail</label>
                <select class="w-full border-gray-300 rounded-md shadow-sm produit-select focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="produits[]" required data-index="{{ $index }}">
                  <option value="">Sélectionnez un espace</option>
                  @foreach($produits as $p)
                  <option value="{{ $p->id }}" data-prix="{{ $p->prix }}" {{ $p->id == $produit->id ? 'selected' : '' }}>
                    {{ $p->nom }} - {{ $p->prix }}€/heure
                  </option>
                  @endforeach
                </select>
              </div>
              <div>
                <label for="dates[{{ $index }}]" class="block mb-1 text-sm font-medium text-gray-700">Date de réservation</label>
                <input type="date" class="w-full border-gray-300 rounded-md shadow-sm date-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="dates[]" value="{{ $produit->pivot->date_reservation }}" required data-index="{{ $index }}" min="{{ date('Y-m-d') }}">
              </div>
            </div>
            <div class="grid grid-cols-1 gap-4 mb-2 md:grid-cols-2">
              <div>
                <label for="heures_debut[{{ $index }}]" class="block mb-1 text-sm font-medium text-gray-700">Heure de début</label>
                <input type="time" class="w-full border-gray-300 rounded-md shadow-sm heure-debut-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_debut[]" value="{{ $produit->pivot->heure_debut }}" required data-index="{{ $index }}" min="08:00" max="20:00" step="1800">
              </div>
              <div>
                <label for="heures_fin[{{ $index }}]" class="block mb-1 text-sm font-medium text-gray-700">Heure de fin</label>
                <input type="time" class="w-full border-gray-300 rounded-md shadow-sm heure-fin-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_fin[]" value="{{ $produit->pivot->heure_fin }}" required data-index="{{ $index }}" min="09:00" max="21:00" step="1800">
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500 creneaux-info">
                Créneau actuellement réservé
              </div>
              <button type="button" class="px-3 py-1 text-sm text-white bg-red-500 rounded-md remove-produit hover:bg-red-600" {{ $index == 0 && count($commande->produits) == 1 ? 'style=display:none' : '' }}>Supprimer</button>
            </div>
          </div>
          @empty
          <div class="p-4 border border-gray-200 rounded-lg reservation-row">
            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
              <div>
                <label for="produits[0]" class="block mb-1 text-sm font-medium text-gray-700">Espace de travail</label>
                <select class="w-full border-gray-300 rounded-md shadow-sm produit-select focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="produits[]" required data-index="0">
                  <option value="">Sélectionnez un espace</option>
                  @foreach($produits as $produit)
                  <option value="{{ $produit->id }}" data-prix="{{ $produit->prix }}">
                    {{ $produit->nom }} - {{ $produit->prix }}€/heure
                  </option>
                  @endforeach
                </select>
              </div>
              <div>
                <label for="dates[0]" class="block mb-1 text-sm font-medium text-gray-700">Date de réservation</label>
                <input type="date" class="w-full border-gray-300 rounded-md shadow-sm date-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="dates[]" required data-index="0" min="{{ date('Y-m-d') }}">
              </div>
            </div>
            <div class="grid grid-cols-1 gap-4 mb-2 md:grid-cols-2">
              <div>
                <label for="heures_debut[0]" class="block mb-1 text-sm font-medium text-gray-700">Heure de début</label>
                <input type="time" class="w-full border-gray-300 rounded-md shadow-sm heure-debut-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_debut[]" required data-index="0" min="08:00" max="20:00" step="1800">
              </div>
              <div>
                <label for="heures_fin[0]" class="block mb-1 text-sm font-medium text-gray-700">Heure de fin</label>
                <input type="time" class="w-full border-gray-300 rounded-md shadow-sm heure-fin-input focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" name="heures_fin[]" required data-index="0" min="09:00" max="21:00" step="1800">
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500 creneaux-info">
                Sélectionnez une date et un espace pour voir les créneaux disponibles
              </div>
              <button type="button" class="px-3 py-1 text-sm text-white bg-red-500 rounded-md remove-produit hover:bg-red-600" style="display: none;">Supprimer</button>
            </div>
          </div>
          @endforelse
        </div>
      </div>

      <div class="p-4 mb-4 bg-white border border-orange-200 rounded-lg">
        <h2 class="mb-4 text-xl font-semibold text-orange-600">Récapitulatif</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <p class="text-gray-700">Nombre d'espaces réservés: <span id="nombre-espaces">{{ count($commande->produits) }}</span></p>
          </div>
          <div>
            <p class="text-gray-700">Prix total: <span id="prix-total">{{ $commande->prix }}</span>€</p>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full px-4 py-2 text-white bg-orange-500 rounded-md hover:bg-orange-600">Mettre à jour la réservation</button>
    </form>
  </div>
</x-app-layout>