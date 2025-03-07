<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Accueil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Bienvenue sur le site de réservation d'espace de coworking") }}
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="mb-4 text-xl font-semibold">Vos dernières réservations</h2>

                    @if($commandes->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">Vous n'avez pas encore de réservations.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-200">Date</th>
                                    <th class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-200">Heure de début</th>
                                    <th class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-200">Heure de fin</th>
                                    <th class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-200">Prix</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-500">
                                @foreach ($commandes as $cmd)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-600">
                                    @if($cmd->produits->isNotEmpty())
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $cmd->produits->first()->pivot->date_reservation }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $cmd->produits->first()->pivot->heure_debut }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $cmd->produits->first()->pivot->heure_fin }}</td>
                                    @else
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">-</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">-</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">-</td>
                                    @endif
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $cmd->prix }} €</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>