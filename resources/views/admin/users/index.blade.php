<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Utilisateurs</h2>
            <a href="{{ route('admin.utilisateurs.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                Nouvel utilisateur
            </a>
        </div>
    </x-slot>

    @php
        $libellesRoles = ['admin' => 'Administrateur', 'directeur' => 'Directeur', 'collaborateur' => 'Collaborateur'];
        $champ = 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm';
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('succes'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('succes') }}</div>
            @endif

            @if ($errors->has('activation'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ $errors->first('activation') }}</div>
            @endif

            @if (session('identifiants'))
                @php $ident = session('identifiants'); @endphp
                <div class="p-4 bg-yellow-50 border border-yellow-300 rounded-md text-sm space-y-1">
                    <p class="font-semibold">Identifiants à transmettre à {{ $ident['nom'] }} (affichés une seule fois)</p>
                    <p>Courriel : <span class="font-mono">{{ $ident['email'] }}</span></p>
                    <p>Mot de passe temporaire : <span class="font-mono font-bold select-all">{{ $ident['mot_de_passe'] }}</span></p>
                    <p class="text-gray-600">L'utilisateur devra choisir un nouveau mot de passe dès sa première connexion.</p>
                </div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, courriel, fonction…"
                           class="{{ $champ }} w-64">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Rôle</label>
                    <select name="role" class="{{ $champ }}">
                        <option value="">Tous</option>
                        @foreach ($libellesRoles as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(request('role') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">État</label>
                    <select name="etat" class="{{ $champ }}">
                        <option value="">Tous</option>
                        <option value="actif" @selected(request('etat') === 'actif')>Actif</option>
                        <option value="inactif" @selected(request('etat') === 'inactif')>Désactivé</option>
                    </select>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                    Filtrer
                </button>
                <a href="{{ route('admin.utilisateurs.index') }}" class="text-sm text-gray-500 underline">Réinitialiser</a>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nom</th>
                            <th class="px-4 py-3">Courriel</th>
                            <th class="px-4 py-3">Fonction</th>
                            <th class="px-4 py-3">Rôle</th>
                            <th class="px-4 py-3">État</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($utilisateurs as $u)
                            @php $role = $u->roles->pluck('name')->first(); @endphp
                            <tr class="hover:bg-gray-50 {{ $u->actif ? '' : 'opacity-60' }}">
                                <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                                <td class="px-4 py-3">{{ $u->email }}</td>
                                <td class="px-4 py-3">{{ $u->fonction ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $libellesRoles[$role] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5 {{ $u->actif ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $u->actif ? 'Actif' : 'Désactivé' }}
                                    </span>
                                    @if ($u->doit_changer_mot_de_passe)
                                        <span class="text-xs rounded-full px-2 py-0.5 bg-yellow-100 text-yellow-800">mot de passe temporaire</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('admin.utilisateurs.edit', $u) }}" class="text-indigo-600 hover:underline">Modifier</a>

                                        <form method="POST" action="{{ route('admin.utilisateurs.reinitialiser', $u) }}"
                                              onsubmit="return confirm('Réinitialiser le mot de passe de {{ $u->name }} ?');">
                                            @csrf
                                            <button class="text-indigo-600 hover:underline">Réinitialiser le mot de passe</button>
                                        </form>

                                        @if ($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.utilisateurs.activation', $u) }}"
                                                  onsubmit="return confirm('{{ $u->actif ? 'Désactiver' : 'Réactiver' }} le compte de {{ $u->name }} ?');">
                                                @csrf
                                                <button class="{{ $u->actif ? 'text-red-600' : 'text-green-700' }} hover:underline">
                                                    {{ $u->actif ? 'Désactiver' : 'Réactiver' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun utilisateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $utilisateurs->links() }}
        </div>
    </div>
</x-app-layout>