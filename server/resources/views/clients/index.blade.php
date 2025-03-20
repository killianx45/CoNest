<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Clients') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <div class="p-6 bg-white rounded-lg">
            <h1 class="mb-6 text-3xl font-bold text-black">Clients</h1>
            <table class="w-full">
              <thead>
                <tr>
                  <th class="p-2 text-left text-black">Nom</th>
                  <th class="p-2 text-left text-black">Email</th>
                  <th class="p-2 text-left text-black">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($clients as $client)
                <tr>
                  <td class="p-2 text-black">{{ $client->name }}</td>
                  <td class="p-2 text-black">{{ $client->email }}</td>
                  <td class="p-2">
                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-red-500">Supprimer</button>
                    </form>
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