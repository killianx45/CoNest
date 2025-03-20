<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Catégories') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
            <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Categories</h1>
            <a href="{{ route('categories.create') }}" class="inline-block px-4 py-2 mb-4 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Ajouter une catégorie</a>
            <div class="grid grid-cols-1 gap-4">
              @foreach ($categories as $category)
              <div class="p-4 rounded-md bg-orange-50">
                <h2 class="text-black">{{ $category->name }}</h2>
                <a href="{{ route('categories.show', $category->id) }}" class="inline-block px-3 py-1 mr-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200">Voir</a>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>