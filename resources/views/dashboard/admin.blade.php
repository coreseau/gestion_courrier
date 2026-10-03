<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord : Administration</h2>
            <a href="{{ route('admin.utilisateurs.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                Nouvel utilisateur
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('succes'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('succes') }}</div>
            @endif

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ([
                    ['Comptes', $stats['total'], route('admin.utilisateurs.index')],
                    ['Comptes actifs', $stats['actifs'], route('admin.utilisateurs.index', ['etat' => 'actif'])],
                    ['Administrateurs', $stats['admins'], route('admin.utilisateurs.index', ['role' => 'admin'])],
                    ['Directeurs', $stats['directeurs'], route('admin.utilisateurs.index', ['role' => 'directeur'])],
                    ['Collaborateurs', $stats['collaborateurs'], route('admin.utilisateurs.index', ['role' => 'collaborateur'])],
                ] as [$label, $valeur, $lien])
                    <a href="{{ $lien }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-800">{{ $valeur }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>