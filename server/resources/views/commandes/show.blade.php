<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Détails de la commande') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
            <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Commande #{{ $commande->id }}</h1>

            <div class="grid grid-cols-1 gap-4">
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Prix:</span> {{ $commande->prix }}€</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Produits:</span></p>
                @foreach($commande->produits as $produit)
                <div class="mt-2 ml-4">
                  <p>{{ $produit->nom }}</p>
                </div>
                @endforeach
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Utilisateur:</span> {{ $commande->client->name }}</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Date de réservation:</span> {{ $date_reservation }}</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Heure de début:</span> {{ $heure_debut }}</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Heure de fin:</span> {{ $heure_fin }}</p>
              </div>
            </div>

            <div class="flex mt-6 space-x-4">
              <a href="{{ route('commandes.edit', $commande->id) }}" class="inline-block px-4 py-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Modifier</a>
              <form action="{{ route('commandes.destroy', $commande->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Supprimer</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>