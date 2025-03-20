<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Détails du produit') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
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
                <p class="text-black"><span class="font-semibold">Adresse:</span> {{ $produit->adresse }}</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                @foreach (explode(',', $produit->image) as $image)
                <img src="{{ asset('/' . $image) }}" alt="Image du produit" class="object-cover w-full h-48 rounded-md">
                @endforeach
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Categorie:</span> {{ $produit->categories->pluck('name')->implode(', ') }}</p>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Disponibilite:</span> {{ $produit->disponibilite }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>