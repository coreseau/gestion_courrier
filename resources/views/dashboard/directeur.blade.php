<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800 tracking-tight">Tableau de bord : Directeur</h2>
            <a href="{{ route('dossiers.create') }}"
               class="md-btn md-btn-primary md-btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nouveau dossier
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ([
                    ['Dossiers reçus', $stats['recus'], route('dossiers.index')],
                    ['En attente de cotation', $stats['a_coter'], route('dossiers.index', ['statut' => 'recu'])],
                    ['En cours de traitement', $stats['en_cours'], route('dossiers.index')],
                    ['En retard', $stats['en_retard'], route('dossiers.index')],
                    ['À valider', $stats['a_valider'], route('dossiers.index', ['statut' => 'soumis'])],
                    ['Clôturés', $stats['clotures'], route('dossiers.index', ['statut' => 'cloture'])],
                ] as [$label, $valeur, $lien])
                    <a href="{{ $lien }}" class="md-card-elevated p-5 hover:border-primary-200 transition-colors duration-200 group">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-bold text-slate-800 group-hover:text-primary-700 transition-colors">{{ $valeur }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>