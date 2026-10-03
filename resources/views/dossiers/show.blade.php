<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800 tracking-tight">
                Dossier {{ $dossier->reference }}
            </h2>
            <a href="{{ route('dossiers.index') }}" class="text-sm text-primary-600 hover:underline">← Retour à la liste</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('succes'))
                <div class="md-alert md-alert-success">{{ session('succes') }}</div>
            @endif

            {{-- Informations générales --}}
            <div class="md-card-elevated p-5">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <x-statut-badge :statut="$dossier->statut" />
                    <x-priorite-badge :priorite="$dossier->priorite" />
                </div>
                <h3 class="text-lg font-semibold text-slate-800">{{ $dossier->objet }}</h3>
                @if ($dossier->resume)
                    <p class="mt-2 text-slate-600 whitespace-pre-line">{{ $dossier->resume }}</p>
                @endif

                <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Origine</dt>
                        <dd class="font-medium">
                            {{ $dossier->origine_type === 'direction' ? 'Direction du ministère' : 'Entreprise tierce' }}
                            : {{ $dossier->origine_nom }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Date de réception</dt>
                        <dd class="font-medium">{{ $dossier->date_reception->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Échéance</dt>
                        <dd class="font-medium">{{ $dossier->echeance?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Enregistré par</dt>
                        <dd class="font-medium">{{ $dossier->enregistrePar->name }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Fichiers --}}
            <div class="md-card-elevated p-5">
                <h3 class="font-semibold text-slate-800 mb-3">Scans et pièces jointes</h3>
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($dossier->fichiers as $fichier)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium">{{ $fichier->nom_original }}</p>
                                <p class="text-slate-500">
                                    {{ $fichier->categorie === 'scan_initial' ? 'Scan initial' : 'Pièce de traitement' }}
                                    · {{ number_format($fichier->taille / 1024, 0, ',', ' ') }} Ko
                                    · par {{ $fichier->televersePar->name }}
                                </p>
                            </div>
                            <div class="flex gap-4">
                                <a href="{{ route('fichiers.afficher', $fichier) }}" target="_blank"
                                   class="text-primary-600 hover:underline">Voir</a>
                                <a href="{{ route('fichiers.telecharger', $fichier) }}"
                                   class="text-primary-600 hover:underline">Télécharger</a>
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-slate-500">Aucun fichier.</li>
                    @endforelse
                </ul>
            </div>

         {{-- Cotations --}}
@php
    $libellesPivot = ['a_traiter' => 'à traiter', 'en_cours' => 'en cours', 'soumis' => 'soumis', 'transfere' => 'transféré'];
    $idsActifs = $participantsActifs->pluck('id')->all();
@endphp

<div class="md-card-elevated p-5">
    <h3 class="font-semibold text-slate-800 mb-3">Cotations</h3>

    @forelse ($dossier->cotations as $cotation)
        <div class="border border-slate-200 rounded-lg p-4 mb-3 text-sm">
            <p class="text-slate-500">
                {{ $cotation->type === 'transfert' ? 'Transfert' : 'Cotation' }}
                par {{ $cotation->coteePar->name }}
                le {{ $cotation->created_at->format('d/m/Y') }}
                @if ($cotation->echeance) · échéance {{ $cotation->echeance->format('d/m/Y') }} @endif
            </p>

            @if ($cotation->instruction)
                <p class="mt-1">{{ $cotation->instruction }}</p>
            @endif
            @if ($cotation->motif_transfert)
                <p class="mt-1 italic">Motif : {{ $cotation->motif_transfert }}</p>
            @endif

            <ul class="mt-3 space-y-1">
                @foreach ($cotation->users as $u)
                    <li class="flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ $u->name }}</span>
                        <span class="md-badge-neutral">
                            {{ $u->pivot->role === 'pilote' ? 'pilote' : 'associé' }}
                        </span>
                        <span class="md-badge-primary">
                            {{ $libellesPivot[$u->pivot->statut] ?? $u->pivot->statut }}
                        </span>
                        <span class="text-xs text-slate-500">
                            {{ $u->pivot->lu_le
                                ? 'lu le ' . \Illuminate\Support\Carbon::parse($u->pivot->lu_le)->format('d/m/Y H:i')
                                : 'pas encore lu' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="text-sm text-slate-500">Ce dossier n'a pas encore été coté.</p>
    @endforelse
</div>

        {{-- Formulaire de cotation (directeur) --}}
        @can('coter', $dossier)
            <form method="POST" action="{{ route('cotations.store', $dossier) }}"
                class="md-card-elevated p-5 space-y-4">
                @csrf
                <h3 class="font-semibold text-slate-800">Coter ce dossier</h3>

                <div>
                    <p class="text-sm text-slate-600 mb-2">
                        Cochez les collaborations concernés et désignez un pilote (responsable principal).
                    </p>
                    <div class="border border-slate-200 rounded-lg divide-y divide-slate-200 text-sm">
                        <div class="grid grid-cols-[1fr_auto_auto] gap-4 px-3 py-2 bg-slate-50 text-xs uppercase text-slate-500">
                            <span>Collaborateur</span><span>Coter</span><span>Pilote</span>
                        </div>
                        @foreach ($collaborateurs as $c)
                            @php $dejaActif = in_array($c->id, $idsActifs); @endphp
                            <div class="grid grid-cols-[1fr_auto_auto] gap-4 px-3 py-2 items-center {{ $dejaActif ? 'opacity-50' : '' }}">
                                <span>
                                    {{ $c->name }}
                                    @if ($c->fonction) <span class="text-slate-400">· {{ $c->fonction }}</span> @endif
                                    @if ($dejaActif) <span class="text-xs text-slate-500">(déjà coté)</span> @endif
                                </span>
                                <input type="checkbox" name="collaborateurs[]" value="{{ $c->id }}"
                                    @checked(in_array($c->id, old('collaborateurs', []))) @disabled($dejaActif)>
                                <input type="radio" name="pilote" value="{{ $c->id }}"
                                    @checked((int) old('pilote') === $c->id) @disabled($dejaActif)>
                            </div>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('collaborateurs')" class="mt-2" />
                    <x-input-error :messages="$errors->get('pilote')" class="mt-2" />
                </div>

                <div class="md-form-group">
                    <x-input-label for="instruction" value="Instruction (facultatif)" />
                    <textarea id="instruction" name="instruction" rows="3" class="md-input">{{ old('instruction') }}</textarea>
                    <x-input-error :messages="$errors->get('instruction')" class="mt-2" />
                </div>

                <div class="md-form-group max-w-xs">
                    <x-input-label for="echeance_cotation" value="Échéance de traitement (facultatif)" />
                    <input id="echeance_cotation" name="echeance" type="date" value="{{ old('echeance') }}" class="md-input">
                    <x-input-error :messages="$errors->get('echeance')" class="mt-2" />
                </div>

                <x-primary-button>Coter le dossier</x-primary-button>
            </form>
        @endcan

        {{-- Formulaire de transfert (collaborateur) --}}
        @can('transferer', $dossier)
            <form method="POST" action="{{ route('dossiers.transfert', $dossier) }}"
                class="md-card-elevated p-5 space-y-4"
                onsubmit="return confirm('Transférer ce dossier ? Vous ne pourrez plus le traiter.');">
                @csrf
                <h3 class="font-semibold text-slate-800">Transférer ce dossier à un autre collaborateur</h3>

                <div class="md-form-group max-w-sm">
                    <x-input-label for="destinataire" value="Destinataire" />
                    <select id="destinataire" name="destinataire" class="md-select" required>
                        <option value="">Choisir…</option>
                        @foreach ($collaborateurs->whereNotIn('id', $idsActifs) as $c)
                            <option value="{{ $c->id }}" @selected((int) old('destinataire') === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('destinataire')" class="mt-2" />
                </div>

                <div class="md-form-group">
                    <x-input-label for="motif" value="Motif du transfert (obligatoire)" />
                    <textarea id="motif" name="motif" rows="3" class="md-input" required>{{ old('motif') }}</textarea>
                    <x-input-error :messages="$errors->get('motif')" class="mt-2" />
                </div>

                <x-primary-button>Transférer</x-primary-button>
            </form>
        @endcan
                        {{-- Mon traitement (collaborateur coté) --}}
        @can('traiter', $dossier)
            @php
                $monTraitement = $dossier->traitements->firstWhere('collaborateur_id', auth()->id());
                $derniereReprise = $dossier->historiques->firstWhere('action', 'reprise');
                $refSuggeree = $dossier->reference . '-T' . ($dossier->traitements->count() + 1);
            @endphp

            <form method="POST" action="{{ route('traitements.store', $dossier) }}" enctype="multipart/form-data"
                class="md-card-elevated p-5 space-y-4">
                @csrf
                <h3 class="font-semibold text-slate-800">Mon traitement</h3>

                @if ($dossier->statut === 'a_reprendre' && $derniereReprise)
                    <div class="p-3 bg-red-50 text-red-800 text-sm rounded-lg border border-red-100">
                        <p class="font-medium">Reprise demandée par le directeur</p>
                        <p>{{ $derniereReprise->commentaire }}</p>
                    </div>
                @endif

                <div class="md-form-group max-w-sm">
                    <x-input-label for="reference_traitement" value="Référence de traitement" />
                    <input id="reference_traitement" name="reference_traitement" type="text"
                        value="{{ old('reference_traitement', $monTraitement?->reference_traitement ?? $refSuggeree) }}"
                        class="md-input">
                    <x-input-error :messages="$errors->get('reference_traitement')" class="mt-2" />
                </div>

                <div class="md-form-group">
                    <x-input-label for="contenu" value="Traitement / réponse" />
                    <textarea id="contenu" name="contenu" rows="8" class="md-input">{{ old('contenu', $monTraitement?->contenu) }}</textarea>
                    <x-input-error :messages="$errors->get('contenu')" class="mt-2" />
                </div>

                <div class="md-form-group">
                    <x-input-label for="pieces" value="Pièces jointes (5 max, 10 Mo chacune : PDF, image, Word, Excel)" />
                    <input id="pieces" name="pieces[]" type="file" multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                        class="block mt-1 w-full text-sm text-slate-700">
                    <x-input-error :messages="$errors->get('pieces')" class="mt-2" />
                    <x-input-error :messages="$errors->get('pieces.0')" class="mt-2" />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" name="action" value="brouillon"
                            class="md-btn md-btn-outlined md-btn-sm">
                        Enregistrer le brouillon
                    </button>
                    <button type="submit" name="action" value="soumettre"
                            onclick="return confirm('Soumettre ce traitement au directeur ? Vous ne pourrez plus le modifier sauf reprise.');"
                            class="md-btn md-btn-primary md-btn-sm">
                        Soumettre au directeur
                    </button>
                </div>
            </form>
        @endcan

        {{-- Traitements enregistrés --}}
        @php
            $estDirecteur = auth()->user()->hasAnyRole(['directeur', 'admin']);
            $traitementsVisibles = $estDirecteur
                ? $dossier->traitements->where('statut', 'soumis')
                : $dossier->traitements->where('collaborateur_id', auth()->id());
        @endphp

        @if ($traitementsVisibles->isNotEmpty())
            <div class="md-card-elevated p-5">
                <h3 class="font-semibold text-slate-800 mb-3">
                    {{ $estDirecteur ? 'Retours des collaborateurs' : 'Mon traitement enregistré' }}
                </h3>
                @foreach ($traitementsVisibles as $t)
                    <div class="border border-slate-200 rounded-lg p-4 mb-3 text-sm">
                        <div class="flex flex-wrap items-center gap-2 text-slate-500">
                            <span class="font-medium text-slate-800">{{ $t->collaborateur->name }}</span>
                            <span>· réf. {{ $t->reference_traitement }}</span>
                            <span class="md-badge {{ $t->statut === 'soumis' ? 'md-badge-success' : 'md-badge-warning' }}">
                                {{ $t->statut === 'soumis' ? 'soumis' : 'brouillon' }}
                            </span>
                            @if ($t->soumis_le)
                                <span>le {{ $t->soumis_le->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <p class="mt-2 text-slate-700 whitespace-pre-line">{{ $t->contenu }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Décision du directeur : valider ou demander une reprise --}}
        @can('decider', $dossier)
            <div class="md-card-elevated p-5 space-y-6">
                <h3 class="font-semibold text-slate-800">Décision du directeur</h3>

                <form method="POST" action="{{ route('dossiers.valider', $dossier) }}" class="space-y-3">
                    @csrf
                    <div class="md-form-group">
                        <x-input-label for="commentaire_validation" value="Commentaire de validation (facultatif)" />
                        <textarea id="commentaire_validation" name="commentaire" rows="2" class="md-input"></textarea>
                        <x-input-error :messages="$errors->get('commentaire')" class="mt-2" />
                    </div>
                    <x-primary-button>Valider le traitement</x-primary-button>
                </form>

                <hr class="border-slate-200">

                <form method="POST" action="{{ route('dossiers.reprendre', $dossier) }}" class="space-y-3">
                    @csrf
                    <p class="text-sm text-slate-600">Ou demander une reprise aux collaborateurs concernés :</p>
                    <div class="space-y-2 text-sm">
                        @foreach ($dossier->traitements->where('statut', 'soumis') as $t)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="collaborateurs[]" value="{{ $t->collaborateur_id }}"
                                    @checked(in_array($t->collaborateur_id, old('collaborateurs', [])))>
                                {{ $t->collaborateur->name }} <span class="text-slate-400">(réf. {{ $t->reference_traitement }})</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('collaborateurs')" class="mt-1" />

                    <div class="md-form-group">
                        <x-input-label for="motif_reprise" value="Motif de la reprise (obligatoire)" />
                        <textarea id="motif_reprise" name="motif" rows="3" class="md-input">{{ old('motif') }}</textarea>
                        <x-input-error :messages="$errors->get('motif')" class="mt-1" />
                    </div>

                    <button type="submit"
                            class="md-btn md-btn-danger md-btn-sm">
                        Demander une reprise
                    </button>
                </form>
            </div>
        @endcan

    {{-- Clôture --}}
    @can('cloturer', $dossier)
        <form method="POST" action="{{ route('dossiers.cloturer', $dossier) }}"
            class="md-card-elevated p-5 space-y-3"
            onsubmit="return confirm('Clôturer ce dossier ?');">
            @csrf
            <h3 class="font-semibold text-slate-800">
                {{ $dossier->statut === 'recu' ? 'Traiter moi-même et clôturer' : 'Clôturer le dossier' }}
            </h3>
            <div class="md-form-group">
                <x-input-label for="commentaire_cloture"
                    :value="$dossier->statut === 'recu' ? 'Décision / traitement réalisé (obligatoire)' : 'Commentaire (facultatif)'" />
                <textarea id="commentaire_cloture" name="commentaire" rows="3" class="md-input">{{ old('commentaire') }}</textarea>
                <x-input-error :messages="$errors->get('commentaire')" class="mt-1" />
            </div>
            <x-primary-button>Clôturer</x-primary-button>
        </form>
    @endcan

                {{-- Historique --}}
                <div class="md-card-elevated p-5">
                    <h3 class="font-semibold text-slate-800 mb-3">Historique</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach ($dossier->historiques as $h)
                            <li class="border-l-2 border-slate-200 pl-4">
                                <p class="text-slate-500">{{ $h->created_at->format('d/m/Y H:i') }} · {{ $h->user->name }}</p>
                                <p class="font-medium capitalize">{{ $h->action }}</p>
                                @if ($h->commentaire)
                                    <p class="text-slate-600">{{ $h->commentaire }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
</x-app-layout>