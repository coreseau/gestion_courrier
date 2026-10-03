<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800 tracking-tight">Enregistrer un dossier</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('dossiers.store') }}" enctype="multipart/form-data"
                  class="md-card-elevated p-5 space-y-5">
                @csrf

                <div class="md-form-group">
                    <x-input-label for="objet" value="Objet du dossier" />
                    <input id="objet" name="objet" type="text" value="{{ old('objet') }}" class="md-input" required>
                    <x-input-error :messages="$errors->get('objet')" class="mt-2" />
                </div>

                <div class="md-form-group">
                    <x-input-label for="resume" value="Résumé (facultatif)" />
                    <textarea id="resume" name="resume" rows="4" class="md-input">{{ old('resume') }}</textarea>
                    <x-input-error :messages="$errors->get('resume')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="md-form-group">
                        <x-input-label for="origine_type" value="Type d'origine" />
                        <select id="origine_type" name="origine_type" class="md-select" required>
                            <option value="direction" @selected(old('origine_type') === 'direction')>Direction du ministère</option>
                            <option value="entreprise" @selected(old('origine_type') === 'entreprise')>Entreprise tierce</option>
                        </select>
                        <x-input-error :messages="$errors->get('origine_type')" class="mt-2" />
                    </div>
                    <div class="md-form-group">
                        <x-input-label for="origine_nom" value="Nom de la direction / entreprise" />
                        <input id="origine_nom" name="origine_nom" type="text" value="{{ old('origine_nom') }}" class="md-input" required>
                        <x-input-error :messages="$errors->get('origine_nom')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="md-form-group">
                        <x-input-label for="date_reception" value="Date de réception" />
                        <input id="date_reception" name="date_reception" type="date"
                               value="{{ old('date_reception', now()->toDateString()) }}" class="md-input" required>
                        <x-input-error :messages="$errors->get('date_reception')" class="mt-2" />
                    </div>
                    <div class="md-form-group">
                        <x-input-label for="priorite" value="Priorité" />
                        <select id="priorite" name="priorite" class="md-select" required>
                            <option value="normale" @selected(old('priorite', 'normale') === 'normale')>Normale</option>
                            <option value="urgente" @selected(old('priorite') === 'urgente')>Urgente</option>
                            <option value="tres_urgente" @selected(old('priorite') === 'tres_urgente')>Très urgente</option>
                        </select>
                        <x-input-error :messages="$errors->get('priorite')" class="mt-2" />
                    </div>
                    <div class="md-form-group">
                        <x-input-label for="echeance" value="Échéance (facultatif)" />
                        <input id="echeance" name="echeance" type="date" value="{{ old('echeance') }}" class="md-input">
                        <x-input-error :messages="$errors->get('echeance')" class="mt-2" />
                    </div>
                </div>

                <div class="md-form-group">
                    <x-input-label for="scan" value="Scan du dossier (PDF, JPG ou PNG, 10 Mo max)" />
                    <input id="scan" name="scan" type="file" accept=".pdf,.jpg,.jpeg,.png"
                           class="block mt-1 w-full text-sm text-slate-700" required>
                    <x-input-error :messages="$errors->get('scan')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a href="{{ route('dossiers.index') }}" class="text-sm text-primary-600 hover:underline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>