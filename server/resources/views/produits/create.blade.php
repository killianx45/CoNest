<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Créer un produit') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
            <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Créer un produit</h1>

            <form action="{{ route('produits.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
              @csrf

              <div class="mb-4">
                <label for="nom" class="block mb-2 font-semibold text-black">Nom</label>
                <input type="text" name="nom" id="nom" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">
              </div>

              <div class="mb-4">
                <label for="description" class="block mb-2 font-semibold text-black">Description</label>
                <textarea name="description" id="description" class="w-full h-32 p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea>
              </div>

              <div class="mb-4">
                <label for="prix" class="block mb-2 font-semibold text-black">Prix</label>
                <input type="number" name="prix" id="prix" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">
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
                <p class="mt-1 text-sm text-gray-500">Maintenez la touche Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs catégories</p>
              </div>

              <div class="mb-4">
                <label for="disponibilite" class="block mb-2 font-semibold text-black">Disponibilité</label>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label for="date_debut" class="block mb-1 text-sm font-medium text-gray-700">Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" min="{{ date('Y-m-d') }}" required>
                  </div>
                  <div>
                    <label for="date_fin" class="block mb-1 text-sm font-medium text-gray-700">Date de fin</label>
                    <input type="date" name="date_fin" id="date_fin" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" min="{{ date('Y-m-d') }}" required>
                  </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">La période de disponibilité sera enregistrée au format "JJ/MM/AAAA-JJ/MM/AAAA"</p>
              </div>

              <button type="submit" class="px-4 py-2 font-semibold text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600">Créer</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>