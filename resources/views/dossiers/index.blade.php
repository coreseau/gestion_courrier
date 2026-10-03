<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800 tracking-tight">Dossiers</h2>
            <a href="{{ route('dossiers.create') }}" class="md-btn md-btn-primary md-btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nouveau dossier
            </a>
        </div>
    </x-slot>

    @php
        $statuts = ['recu' => 'Reçu', 'cote' => 'Coté', 'en_traitement' => 'En traitement', 'soumis' => 'Soumis',
                    'valide' => 'Validé', 'a_reprendre' => 'À reprendre', 'cloture' => 'Clôturé'];
        $priorites = ['normale' => 'Normale', 'urgente' => 'Urgente', 'tres_urgente' => 'Très urgente'];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('succes'))
                <div class="md-alert md-alert-success">{{ session('succes') }}</div>
            @endif

            <form method="GET" class="md-card-elevated p-4 flex flex-wrap gap-3 items-end">
                <div class="md-form-group">
                    <label class="md-form-label">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, objet, origine…"
                           class="md-input w-64">
                </div>
                <div class="md-form-group">
                    <label class="md-form-label">Statut</label>
                    <select name="statut" class="md-select">
                        <option value="">Tous</option>
                        @foreach ($statuts as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(request('statut') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md-form-group">
                    <label class="md-form-label">Priorité</label>
                    <select name="priorite" class="md-select">
                        <option value="">Toutes</option>
                        @foreach ($priorites as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(request('priorite') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="md-btn md-btn-primary md-btn-sm">
                    Filtrer
                </button>
                <a href="{{ route('dossiers.index') }}" class="text-sm text-primary-600 hover:underline">Réinitialiser</a>
            </form>

            <div class="md-table-container">
                <table class="md-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Référence</th>
                            <th class="px-4 py-3">Objet</th>
                            <th class="px-4 py-3">Origine</th>
                            <th class="px-4 py-3">Reçu le</th>
                            <th class="px-4 py-3">Priorité</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Échéance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dossiers as $dossier)
                            <tr>
                                <td class="px-4 py-3 font-medium">
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-primary-600 hover:underline">
                                        {{ $dossier->reference }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">{{ $dossier->objet }}</td>
                                <td class="px-4 py-3">{{ $dossier->origine_nom }}</td>
                                <td class="px-4 py-3">{{ $dossier->date_reception->format('d/m/Y') }}</td>
                                <td class="px-4 py-3"><x-priorite-badge :priorite="$dossier->priorite" /></td>
                                <td class="px-4 py-3"><x-statut-badge :statut="$dossier->statut" /></td>
                                <td class="px-4 py-3">{{ $dossier->echeance?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucun dossier trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $dossiers->links() }}
        </div>
    </div>
</x-app-layout>