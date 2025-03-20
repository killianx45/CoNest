<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Détails de la catégorie') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
            <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Catégorie</h1>

            <div class="grid grid-cols-1 gap-4">
              <div class="p-4 rounded-md bg-orange-50">
                <p class="text-black"><span class="font-semibold">Nom:</span> {{ $category->name }}</p>
                <p class="text-black"><span class="font-semibold">Slug:</span> {{ $category->slug }}</p>
                <p class="text-black"><span class="font-semibold">Produits:</span> {{ $category->produits->count() }}</p>
                <div class="flex">
                  <p class="text-black"><span class="font-semibold">Action:</span>
                    <a href="{{ route('categories.edit', $category->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Modifier</a>
                  <form action="{{ route('categories.destroy', $category->id) }}" method="post" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Supprimer</button>
                  </form>
                  </p>
                </div>
              </div>
              <div class="p-4 rounded-md bg-orange-50">
                <h2 class="text-lg font-semibold text-black">Produits</h2>
                <ul class="text-black list-disc list-inside">
                  @foreach ($category->produits as $produit)
                  <li class="text-black">{{ $produit->nom }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>