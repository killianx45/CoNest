<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Produits') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
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
                  <th class="p-3 font-semibold text-left">Adresse</th>
                  <th class="p-3 font-semibold text-left"></th>Image</th>
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
                  <td class="p-3">{{ $produit->adresse }}</td>
                  <td class="p-3">
                    @foreach (explode(',', $produit->image) as $image)
                    <img src="{{ asset('/' . $image) }}" alt="Image du produit" class="object-cover w-24 h-24 border border-orange-300 rounded">
                    @endforeach
                  </td>
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
      </div>
    </div>
  </div>
</x-app-layout>