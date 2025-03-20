<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Créer une catégorie') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
          <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Créer une catégorie</h1>

          <form action="{{ route('categories.store') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="name" class="block mb-2 font-semibold text-black">Nom</label>
              <input type="text" name="name" id="name" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <button type="submit" class="px-4 py-2 font-semibold text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600">Créer</button>
          </form>
        </div>
      </div>
    </div>
</x-app-layout>